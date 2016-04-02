<?php

namespace Surume\Console\Client\Provider\Console;

use Surume\Console\Client\Command\CommandHandler;
use Surume\Core\CoreInterface;
use Surume\Core\Service\ServiceProvider;
use Surume\Core\Service\ServiceProviderInterface;

class CommandProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Surume\Console\Client\Channel\ConsoleInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Surume\Console\Client\Command\CommandHandlerInterface'
    ];

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $channel = $core->make('Surume\Console\Client\Channel\ConsoleInterface');

        $manager = new CommandHandler($channel, 'ConsoleServer');

        $core->instance(
            'Surume\Console\Client\Command\CommandHandlerInterface',
            $manager
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Surume\Console\Client\Command\CommandHandlerInterface'
        );
    }
}
