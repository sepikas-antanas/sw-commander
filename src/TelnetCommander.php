<?php

namespace Sepikas\SwCommander;

class TelnetCommander implements CommanderInterface
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
     * @var bool
     */
    private $debug;

    public function __construct($length = null, $pause = null, $debug = false)
    {
        $this->length = $length ? $length : $this->length;
        $this->pause = $pause ? $pause : $this->pause;
        $this->debug = $debug;
    }

    public function connect($server, $timeout = null, $port = 23)
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        if ($timeout) {
            socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => $timeout, 'usec' => 0));
            socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, array('sec' => $timeout, 'usec' => 0));
        }

        if (false === $this->socket) {
            throw new \Exception("Could not execute socket_create(): " . socket_strerror(socket_last_error()));
        }

        if (false ===  socket_connect($this->socket, $server, $port)) {
            throw new \Exception("Could not execute socket_connect(): " . socket_strerror(socket_last_error()));
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
        while (!empty($this->commands) && ($data = $this->readFromSocket())) {
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
                        if (empty($this->commands)) {
                            $this->writeToSocket($command, false);
                            $this->sessionLog .= $this->readFromSocket();
                            $this->sessionLog .= $this->readFromSocket();
                            $this->writeToSocket(PHP_EOL);
                        } else {
                            $this->writeToSocket($command);
                            $this->sessionLog .= $this->readFromSocket();
                        }
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
        $buffer = @socket_read($this->socket, $this->length);

        if (false === $buffer) {
            throw new \Exception("Could not read data from socket socket_read(): " . socket_strerror(socket_last_error()));
        }

        if (true === $this->debug) {
            echo($buffer);
        }

        return $buffer;
    }

    private function writeToSocket($command, $persist = true)
    {
        $command = $persist ? trim($command).PHP_EOL : trim($command);

        $bytes = @socket_write($this->socket, $command, strlen($command));

        if (false === $bytes) {
            throw new \Exception("Could not send data to socket socket_send(): " . socket_strerror(socket_last_error()));
        }
    }

    public function disconnect()
    {
        if (null !== $this->socket && @socket_read($this->socket, $this->length)) {
            if (false === @socket_shutdown($this->socket)) {
                throw new \Exception("Could not shutdown socket socket_shutdown(): " . socket_strerror(socket_last_error()));
            }
        }

        if (null !== $this->socket) {
            @socket_close($this->socket);
        }

        $this->socket = null;

        return $this;
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}