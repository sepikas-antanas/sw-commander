<?php

namespace Sepikas\SwCommander;

class SshCommander implements CommanderInterface
{
    /**
     * @var string
     */
    private $sessionLog;

    /**
     * @var array
     */
    private $commands = [];

    /**
     * @var array
     */
    private $prompts = [];

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var int
     */
    private $pause = 250000;

    /**
     * @var int
     */
    private $length = 1024;

    /**
     * @var resource
     */
    private $socket;

    /**
     * @var resource
     */
    private $stream;

    /**
     * @var int
     */
    private $timeout;

    /**
     * @var bool
     */
    private $debug;

    public function __construct($length = null, $pause = null, $timeout = null, $debug = false)
    {
        $this->length = $length ? $length : $this->length;
        $this->pause = $pause ? $pause : $this->pause;
        $this->timeout = $timeout;
        $this->debug = $debug;
    }

    public function connect($server, $port = 22)
    {
        if ($this->timeout) {
            ini_set("default_socket_timeout", $this->timeout);
        }

        $this->socket = ssh2_connect($server, $port);

        if (false === $this->socket) {
            throw new \Exception("Connection to $server failed");
        }

        $this->sessionLog = null;

        return $this;
    }

    public function setAuthentication($username, $password)
    {
        $this->username = $username;
        $this->password = $password;

        return $this;
    }

    public function setPrompts(array $prompts)
    {
        $this->prompts = $prompts;

        return $this;
    }

    public function setCommands(array $commands)
    {
        $this->commands = $commands;

        return $this;
    }

    public function execute()
    {
        if (true !== @ssh2_auth_none($this->socket, $this->username)) {
            throw new \Exception('Invalid authentication, authentication method not allowed');
        }

        $this->stream = @ssh2_shell($this->socket);
        stream_set_blocking($this->stream, true);

        while ($data = $this->readFromSocket()) {
            $this->sessionLog .= $data;
            
            switch ($data) {
                case !!preg_match($this->prompts['username'], $data):
                    $this->writeToSocket($this->username);
                    break;
                case !!preg_match($this->prompts['password'], $data):
                    $this->writeToSocket($this->password);
                    break;
                case !!preg_match($this->prompts['shell'], $data):
                    $command = array_shift($this->commands);
                    if (NULL !== $command) {
                        $this->writeToSocket($command);
                    }
                    break;
                default:
                    break;
            }
            usleep($this->pause);
        }

        return $this;
    }

    public function getSessionLog()
    {
        $this->sessionLog = implode(PHP_EOL, array_slice(
            explode(PHP_EOL, $this->sessionLog), 2
        ));

        return $this->sessionLog;
    }

    private function readFromSocket()
    {
        $buffer = @fread($this->stream, $this->length);

        if (false === $buffer) {
            throw new \Exception("Could not read data from stream fread()");
        }

        if (true === $this->debug) {
            echo($buffer);
        }

        return $buffer;
    }

    private function writeToSocket($command, $persist = true)
    {
        $command = $persist ? trim($command).PHP_EOL : trim($command);

        $bytes = @fwrite($this->stream, $command, strlen($command));

        if (false === $bytes) {
            throw new \Exception("Could not write data to stream fwrite()");
        }
    }
    public function disconnect()
    {
        if ($this->stream) {
            @fclose($this->stream);
        }

        $this->stream = null;
        $this->socket = null;

        return $this;
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}
