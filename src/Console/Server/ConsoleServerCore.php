<?php

namespace Surume\Console\Server;

use Surume\Core\Core;
use Surume\Core\CoreInterface;
use Surume\Runtime\Runtime;

class ConsoleServerCore extends Core implements CoreInterface
{
    /**
     * @var string
     */
    const RUNTIME_UNIT = Runtime::UNIT_PROCESS;

    /**
     * @return string[]
     */
    protected function defaultProviders()
    {
        return [
            'Surume\Core\Provider\Channel\ChannelProvider',
            'Surume\Core\Provider\Command\CommandProvider',
            'Surume\Core\Provider\Config\ConfigProvider',
            'Surume\Core\Provider\Container\ContainerProvider',
            'Surume\Core\Provider\Core\CoreProvider',
            'Surume\Core\Provider\Core\EnvironmentProvider',
            'Surume\Core\Provider\Supervisor\SupervisorProvider',
            'Surume\Core\Provider\Event\EventProvider',
            'Surume\Core\Provider\Filesystem\FilesystemProvider',
            'Surume\Core\Provider\Log\LogProvider',
            'Surume\Core\Provider\Loop\LoopProvider',
            'Surume\Console\Server\Provider\Channel\ChannelProvider',
            'Surume\Console\Server\Provider\Command\CommandProvider',
            'Surume\Runtime\Provider\Command\CommandProvider',
            'Surume\Runtime\Provider\Supervisor\SupervisorProvider',
            'Surume\Runtime\Provider\Runtime\RuntimeManagerProvider'
        ];
    }

    /**
     * @return string[]
     */
    protected function defaultAliases()
    {
        return [
            'Channel'           => 'Surume\Runtime\Channel\ChannelInterface',
            'Channel.Internal'  => 'Surume\Runtime\Channel\ChannelInterface',
            'CommandManager'    => 'Surume\Command\CommandManagerInterface',
            'Config'            => 'Surume\Config\ConfigInterface',
            'Container'         => 'Surume\Container\ContainerInterface',
            'Core'              => 'Surume\Core\CoreInterface',
            'Emitter'           => 'Surume\Event\EventEmitterInterface',
            'Environment'       => 'Surume\Core\EnvironmentInterface',
            'Filesystem'        => 'Surume\Filesystem\FilesystemInterface',
            'Filesystem.Disk'   => 'Surume\Filesystem\FilesystemInterface',
            'Filesystem.Cloud'  => 'Surume\Filesystem\FilesystemManagerInterface',
            'Logger'            => 'Surume\Log\LoggerInterface',
            'Loop'              => 'Surume\Loop\LoopInterface',
            'Supervisor'        => 'Surume\Runtime\Supervisor\SupervisorBaseInterface',
            'Supervisor.Base'   => 'Surume\Runtime\Supervisor\SupervisorBaseInterface',
            'Supervisor.Remote' => 'Surume\Runtime\Supervisor\SupervisorRemoteInterface'
        ];
    }
}
