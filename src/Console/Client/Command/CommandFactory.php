<?php

namespace Surume\Console\Client\Command;

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
            'ArchStartCommand'          => 'Surume\Console\Client\Command\Arch\ArchStartCommand',
            'ArchStopCommand'           => 'Surume\Console\Client\Command\Arch\ArchStopCommand',
            'ArchStatusCommand'         => 'Surume\Console\Client\Command\Arch\ArchStatusCommand',
            'ProjectCreateCommand'      => 'Surume\Console\Client\Command\Project\ProjectCreateCommand',
            'ProjectDestroyCommand'     => 'Surume\Console\Client\Command\Project\ProjectDestroyCommand',
            'ProjectStartCommand'       => 'Surume\Console\Client\Command\Project\ProjectStartCommand',
            'ProjectStopCommand'        => 'Surume\Console\Client\Command\Project\ProjectStopCommand',
            'ProjectStatusCommand'      => 'Surume\Console\Client\Command\Project\ProjectStatusCommand',
            'ProcessExistsCommand'      => 'Surume\Console\Client\Command\Process\ProcessExistsCommand',
            'ProcessCreateCommand'      => 'Surume\Console\Client\Command\Process\ProcessCreateCommand',
            'ProcessDestroyCommand'     => 'Surume\Console\Client\Command\Process\ProcessDestroyCommand',
            'ProcessStartCommand'       => 'Surume\Console\Client\Command\Process\ProcessStartCommand',
            'ProcessStopCommand'        => 'Surume\Console\Client\Command\Process\ProcessStopCommand',
            'ThreadExistsCommand'       => 'Surume\Console\Client\Command\Thread\ThreadExistsCommand',
            'ThreadCreateCommand'       => 'Surume\Console\Client\Command\Thread\ThreadCreateCommand',
            'ThreadDestroyCommand'      => 'Surume\Console\Client\Command\Thread\ThreadDestroyCommand',
            'ThreadStartCommand'        => 'Surume\Console\Client\Command\Thread\ThreadStartCommand',
            'ThreadStopCommand'         => 'Surume\Console\Client\Command\Thread\ThreadStopCommand',
            'RuntimeExistsCommand'      => 'Surume\Console\Client\Command\Runtime\RuntimeExistsCommand',
            'RuntimeDestroyCommand'     => 'Surume\Console\Client\Command\Runtime\RuntimeDestroyCommand',
            'RuntimeStartCommand'       => 'Surume\Console\Client\Command\Runtime\RuntimeStartCommand',
            'RuntimeStopCommand'        => 'Surume\Console\Client\Command\Runtime\RuntimeStopCommand',
            'ContainerDestroyCommand'   => 'Surume\Console\Client\Command\Container\ContainerDestroyCommand',
            'ContainerStartCommand'     => 'Surume\Console\Client\Command\Container\ContainerStartCommand',
            'ContainerStopCommand'      => 'Surume\Console\Client\Command\Container\ContainerStopCommand',
            'ContainerStatusCommand'    => 'Surume\Console\Client\Command\Container\ContainerStatusCommand'
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
            ->define($alias, function($handler) use($class) {
                return new $class($handler);
            })
            ->define($class, function($handler) use($class) {
                return new $class($handler);
            })
        ;
    }
}
