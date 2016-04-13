<?php

use Sepikas\SwCommander\SshCommander;

class SshCommanderTest extends PHPUnit_Framework_TestCase {

    protected $host = '10.10.10.20';
    protected $username = 'test';
    protected $password = 'test';
    protected $usernamePrompt = '/User Name:.*/i';
    protected $passwordPrompt = '/Password:.*/i';
    protected $shellPrompt = '/(# ?$)|(\?\[Yes\/press any key for no\].*)/i';
    protected $commander;
    protected $shellPromptTest = '/#/';

    public function testCommander1()
    {
        $commander = new SshCommander();
        $commander->connect($this->host)
            ->setAuthentication($this->username, $this->password)
            ->setPrompts([
                'username' => $this->usernamePrompt,
                'password' => $this->passwordPrompt,
                'shell' => $this->shellPrompt,
            ])
            ->setCommands([
                'show running-config interface gi1/0/3',
                'exit'
            ])
            ->execute()
            ->disconnect();

        $log = $commander->getSessionLog();
        $this->assertRegexp($this->usernamePrompt, $log);
        $this->assertRegexp($this->passwordPrompt, $log);
        $this->assertRegexp($this->shellPromptTest, $log);
    }

    public function testCommander2()
    {
        $commander = new SshCommander();
        $commander->connect($this->host)
            ->setAuthentication($this->username, $this->password)
            ->setPrompts([
                'username' => $this->usernamePrompt,
                'password' => $this->passwordPrompt,
                'shell' => $this->shellPrompt,
            ])
            ->setCommands([
                'configure',
                'ip access-list extended gi1_0_3_testas',
                'deny udp any any any netbios-ns',
                'permit ip 172.16.0.1 0.0.0.0 any',
                'permit ip 172.16.0.252 0.0.0.0 any',
                'permit ip 172.16.0.253 0.0.0.0 any',
                'permit ip 172.16.0.254 0.0.0.0 any',
                'permit ip 172.16.0.2 0.0.0.0 any',
                'deny ip any any',
                'exit',
                '',
                'ipv6 access-list gi1_0_3_testas_IPv6',
                'deny ipv6 any any',
                'exit',
                '',
                'interface range gi1/0/3',
                'spanning-tree portfast',
                'no shutdown',
                'description "testas"',
                'storm-control broadcast enable',
                'storm-control broadcast level kbps 3500',
                'no service-acl input',
                'service-acl input gi1_0_3_testas',
                '',
                '',
                '',
                'negotiation',
                '',
                'negotiation',
                '',
                'switchport protected-port',
                'switchport access vlan 1290',
                '',
                'exit',
                'end',
                'write',
                'y',
                'exit'
            ])
            ->execute()
            ->disconnect();

        $log = $commander->getSessionLog();
        $this->assertRegexp($this->usernamePrompt, $log);
        $this->assertRegexp($this->passwordPrompt, $log);
        $this->assertRegexp($this->shellPromptTest, $log);
    }

    public function testCommander3()
    {
        $commander = new SshCommander();
        $commander->connect($this->host)
            ->setAuthentication($this->username, $this->password)
            ->setPrompts([
                'username' => $this->usernamePrompt,
                'password' => $this->passwordPrompt,
                'shell' => $this->shellPrompt,
            ])
            ->setCommands([
                'configure',
                'interface range gi1/0/3',
                'no shutdown ',
                'end',
                'write',
                'y',
                'exit'
            ])
            ->execute()
            ->disconnect();

        $log = $commander->getSessionLog();
        $this->assertRegexp($this->usernamePrompt, $log);
        $this->assertRegexp($this->passwordPrompt, $log);
        $this->assertRegexp($this->shellPromptTest, $log);
    }

    public function testCommander4()
    {
        $commander = new SshCommander();
        $commander->connect($this->host)
            ->setAuthentication($this->username, $this->password)
            ->setPrompts([
                'username' => $this->usernamePrompt,
                'password' => $this->passwordPrompt,
                'shell' => $this->shellPrompt,
            ])
            ->setCommands([
                'configure',
                'interface range gi1/0/3',
                'shutdown',
                'no description',
                'no storm-control broadcast enable',
                'no storm-control broadcast level',
                'no rate-limit',
                'no traffic-shape',
                'no service-acl input',
                'no spanning-tree portfast',
                'switchport protected-port',
                'switchport mode access',
                'negotiation',
                'switchport access vlan 1',
                'switchport access vlan 1',
                'exit',
                '',
                'no ip access-list extended gi1_0_3_testas',
                'no ipv6 access-list gi1_0_3_testas_IPv6',
                '',
                'end',
                'exit',
            ])
            ->execute()
            ->disconnect();

        $log = $commander->getSessionLog();
        $this->assertRegexp($this->usernamePrompt, $log);
        $this->assertRegexp($this->passwordPrompt, $log);
        $this->assertRegexp($this->shellPromptTest, $log);
    }
}