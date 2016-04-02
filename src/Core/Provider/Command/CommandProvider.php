<?php

namespace Surume\Core\Provider\Command;

use Surume\Command\CommandManager;
use Surume\Core\CoreInterface;
use Surume\Core\Service\ServiceProvider;
use Surume\Core\Service\ServiceProviderInterface;
use Surume\Runtime\Command\CommandFactory;
use Surume\Throwable\Exception\Logic\Resource\ResourceUndefinedException;
use Surume\Throwable\Exception\Logic\InvalidArgumentException;
use Surume\Util\Factory\FactoryPluginInterface;
use Exception;

class CommandProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $provides = [
        'Surume\Command\CommandFactoryInterface',
        'Surume\Command\CommandManagerInterface'
    ];

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $factory = new CommandFactory();
        $manager = new CommandManager();

        $core->instance(
            'Surume\Command\CommandFactoryInterface',
            $factory
        );

        $core->instance(
            'Surume\Command\CommandManagerInterface',
            $manager
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Surume\Command\CommandFactoryInterface'
        );

        $core->remove(
            'Surume\Command\CommandManagerInterface'
        );
    }

    /**
     * @param CoreInterface $core
     * @throws Exception
     */
    protected function boot(CoreInterface $core)
    {
        $config = $core->make('Surume\Config\ConfigInterface');
        $factory = $core->make('Surume\Command\CommandFactoryInterface');

        $commands = (array) $config->get('command.models');
        foreach ($commands as $commandClass)
        {
            if (!class_exists($commandClass))
            {
                throw new ResourceUndefinedException("Command [$commandClass] does not exist.");
            }

            $factory
                ->define($commandClass, function($runtime, $context = []) use($commandClass) {
                    return new $commandClass($runtime, $context);
                });
        }

        $plugins = (array) $config->get('command.plugins');
        foreach ($plugins as $pluginClass)
        {
            if (!class_exists($pluginClass))
            {
                throw new ResourceUndefinedException("FactoryPlugin [$pluginClass] does not exist.");
            }

            $plugin = new $pluginClass();

            if (!($plugin instanceof FactoryPluginInterface))
            {
                throw new InvalidArgumentException("FactoryPlugin [$pluginClass] does not implement FactoryPluginInterface.");
            }

            $plugin->registerPlugin($factory);
        }
    }
}
