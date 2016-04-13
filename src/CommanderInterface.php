<?php

namespace Sepikas\SwCommander;

/**
 * Interface CommanderInterface
 */
interface CommanderInterface
{
    /**
     * Instantiate connection to remote device
     *
     * @param string $server
     * @param int $port
     * @return CommanderInterface
     */
    public function connect($server, $port);

    /**
     * @param string $username
     * @param string $password
     * @return CommanderInterface
     */
    public function setAuthentication($username, $password);

    /**
     * @param array $prompts
     * @return CommanderInterface
     */
    public function setPrompts(array $prompts);

    /**
     * @param array $commands
     * @return CommanderInterface
     */
    public function setCommands(array $commands);

    /**
     * @return CommanderInterface
     */
    public function execute();

    /**
     * @return string
     */
    public function getSessionLog();

    /**
     *
     */
    public function disconnect();
}