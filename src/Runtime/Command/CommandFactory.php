<?php

namespace Surume\Runtime\Command;

use Surume\Command\CommandFactoryInterface;
use Surume\Util\Factory\Factory;

class CommandFactory extends Factory implements CommandFactoryInterface
{
    /**
     * @param string[] $params
     */
    public function __construct($params = [])
    {
        parent::__construct();

        foreach ($params as $paramName=>$paramValue)
        {
            $this->bindParam($paramName, $paramValue);
        }

        $commands = [
            'ArchStartCommand'          => 'Surume\Runtime\Command\Arch\ArchStartCommand',
            'ArchStopCommand'           => 'Surume\Runtime\Command\Arch\ArchStopCommand',
            'ArchStatusCommand'         => 'Surume\Runtime\Command\Arch\ArchStatusCommand',
            'ProcessExistsCommand'      => 'Surume\Runtime\Command\Process\ProcessExistsCommand',
            'ProcessCreateCommand'      => 'Surume\Runtime\Command\Process\ProcessCreateCommand',
            'ProcessDestroyCommand'     => 'Surume\Runtime\Command\Process\ProcessDestroyCommand',
            'ProcessStartCommand'       => 'Surume\Runtime\Command\Process\ProcessStartCommand',
            'ProcessStopCommand'        => 'Surume\Runtime\Command\Process\ProcessStopCommand',
            'ProcessesGetCommand'       => 'Surume\Runtime\Command\Processes\ProcessesGetCommand',
            'ProcessesCreateCommand'    => 'Surume\Runtime\Command\Processes\ProcessesCreateCommand',
            'ProcessesDestroyCommand'   => 'Surume\Runtime\Command\Processes\ProcessesDestroyCommand',
            'ProcessesStartCommand'     => 'Surume\Runtime\Command\Processes\ProcessesStartCommand',
            'ProcessesStopCommand'      => 'Surume\Runtime\Command\Processes\ProcessesStopCommand',
            'ThreadExistsCommand'       => 'Surume\Runtime\Command\Thread\ThreadExistsCommand',
            'ThreadCreateCommand'       => 'Surume\Runtime\Command\Thread\ThreadCreateCommand',
            'ThreadDestroyCommand'      => 'Surume\Runtime\Command\Thread\ThreadDestroyCommand',
            'ThreadStartCommand'        => 'Surume\Runtime\Command\Thread\ThreadStartCommand',
            'ThreadStopCommand'         => 'Surume\Runtime\Command\Thread\ThreadStopCommand',
            'ThreadsGetCommand'         => 'Surume\Runtime\Command\Threads\ThreadsGetCommand',
            'ThreadsCreateCommand'      => 'Surume\Runtime\Command\Threads\ThreadsCreateCommand',
            'ThreadsDestroyCommand'     => 'Surume\Runtime\Command\Threads\ThreadsDestroyCommand',
            'ThreadsStartCommand'       => 'Surume\Runtime\Command\Threads\ThreadsStartCommand',
            'ThreadsStopCommand'        => 'Surume\Runtime\Command\Threads\ThreadsStopCommand',
            'RuntimeExistsCommand'      => 'Surume\Runtime\Command\Runtime\RuntimeExistsCommand',
            'RuntimeDestroyCommand'     => 'Surume\Runtime\Command\Runtime\RuntimeDestroyCommand',
            'RuntimeStartCommand'       => 'Surume\Runtime\Command\Runtime\RuntimeStartCommand',
            'RuntimeStopCommand'        => 'Surume\Runtime\Command\Runtime\RuntimeStopCommand',
            'RuntimesGetCommand'        => 'Surume\Runtime\Command\Runtimes\RuntimesGetCommand',
            'RuntimesDestroyCommand'    => 'Surume\Runtime\Command\Runtimes\RuntimesDestroyCommand',
            'RuntimesStartCommand'      => 'Surume\Runtime\Command\Runtimes\RuntimesStartCommand',
            'RuntimesStopCommand'       => 'Surume\Runtime\Command\Runtimes\RuntimesStopCommand',
            'ContainerContinueCommand'  => 'Surume\Runtime\Command\Container\ContainerContinueCommand',
            'ContainerDestroyCommand'   => 'Surume\Runtime\Command\Container\ContainerDestroyCommand',
            'ContainerStartCommand'     => 'Surume\Runtime\Command\Container\ContainerStartCommand',
            'ContainerStopCommand'      => 'Surume\Runtime\Command\Container\ContainerStopCommand',
            'ContainerStatusCommand'    => 'Surume\Runtime\Command\Container\ContainerStatusCommand',
            'CmdErrorCommand'           => 'Surume\Runtime\Command\Cmd\CmdErrorCommand',
            'CmdPingCommand'            => 'Surume\Runtime\Command\Cmd\CmdPingCommand',
        ];

        foreach ($commands as $alias=>$class)
        {
            $this->registerCommand($alias, $class);
        }
    }

    /**
     * @param string $alias
     * @param string $class
     */
    protected function registerCommand($alias, $class)
    {
        $this
            ->define($alias, function($runtime, $context = []) use($class) {
                return new $class($runtime, $context);
            })
            ->define($class, function($runtime, $context = []) use($class) {
                return new $class($runtime, $context);
            })
        ;
    }
}
