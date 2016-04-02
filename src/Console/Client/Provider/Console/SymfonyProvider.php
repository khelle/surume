<?php

namespace Surume\Console\Client\Provider\Console;

use Symfony\Component\Console\Application;
use Surume\Core\CoreInterface;
use Surume\Core\Service\ServiceProvider;
use Surume\Core\Service\ServiceProviderInterface;

class SymfonyProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var Application
     */
    protected $symfony;

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $symfony = new Application();
        $symfony->setAutoExit(false);

        $this->symfony = $symfony;
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        unset($this->symfony);
    }

    /**
     * @param CoreInterface $core
     */
    protected function boot(CoreInterface $core)
    {
        $config  = $core->make('Surume\Config\ConfigInterface');
        $factory = $core->make('Surume\Console\Client\Command\CommandFactoryInterface');
        $handler = $core->make('Surume\Console\Client\Command\CommandHandlerInterface');
        $console = $core->make('Surume\Console\Client\ConsoleClientInterface');

        $cmds = (array) $factory->getDefinitions();
        $commands = [];
        foreach ($cmds as $command=>$definition)
        {
            $commands[] = $factory->create($command, [ $handler ]);
        }

        $this->symfony->addCommands($commands);

        $version = $core->version();
        $console->onCommand(function() use($version) {
            echo "SurumePHP-v$version\n";
            $this->symfony->run();
        });
    }
}
