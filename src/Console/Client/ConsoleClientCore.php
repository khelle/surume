<?php

namespace Surume\Console\Client;

use Surume\Core\Core;
use Surume\Core\CoreInterface;

class ConsoleClientCore extends Core implements CoreInterface
{
    /**
     * @var string
     */
    const RUNTIME_UNIT = 'Console';

    /**
     * @return string[]
     */
    protected function defaultProviders()
    {
        return [
            'Surume\Core\Provider\Channel\ChannelProvider',
            'Surume\Core\Provider\Config\ConfigProvider',
            'Surume\Core\Provider\Container\ContainerProvider',
            'Surume\Core\Provider\Core\CoreProvider',
            'Surume\Core\Provider\Core\EnvironmentProvider',
            'Surume\Core\Provider\Event\EventProvider',
            'Surume\Core\Provider\Log\LogProvider',
            'Surume\Core\Provider\Loop\LoopProvider',
            'Surume\Console\Client\Provider\Channel\ChannelProvider',
            'Surume\Console\Client\Provider\Command\CommandProvider',
            'Surume\Console\Client\Provider\Console\SymfonyProvider',
            'Surume\Console\Client\Provider\Console\CommandProvider'
        ];
    }

    /**
     * @return string[]
     */
    protected function defaultAliases()
    {
        return [];
    }
}
