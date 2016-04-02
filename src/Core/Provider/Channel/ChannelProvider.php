<?php

namespace Surume\Core\Provider\Channel;

use Exception;
use Surume\Channel\ChannelFactory;
use Surume\Channel\ChannelModelFactory;
use Surume\Core\CoreInterface;
use Surume\Core\Service\ServiceProvider;
use Surume\Core\Service\ServiceProviderInterface;
use Surume\Throwable\Exception\Logic\Resource\ResourceUndefinedException;
use Surume\Throwable\Exception\Logic\InvalidArgumentException;
use Surume\Util\Factory\FactoryPluginInterface;

class ChannelProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Surume\Core\CoreInputContextInterface',
        'Surume\Loop\LoopInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Surume\Channel\ChannelModelFactoryInterface',
        'Surume\Channel\ChannelModelInterface',
        'Surume\Channel\ChannelFactoryInterface',
        'Surume\Channel\ChannelBaseInterface',
        'Surume\Channel\ChannelCompositeInterface'
    ];

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $loop = $core->make('Surume\Loop\LoopInterface');
        $context = $core->make('Surume\Core\CoreInputContextInterface');

        $modelFactory = new ChannelModelFactory($context->alias(), $loop);
        $factory = new ChannelFactory($context->alias(), $modelFactory, $loop);

        $core->instance(
            'Surume\Channel\ChannelModelFactoryInterface',
            $modelFactory
        );

        $core->factory(
            'Surume\Channel\ChannelModelInterface',
            function() use($modelFactory) {
                return $modelFactory->create('Surume\Channel\Model\Null\NullModel');
            }
        );

        $core->instance(
            'Surume\Channel\ChannelFactoryInterface',
            $factory
        );

        $core->factory(
            'Surume\Channel\ChannelBaseInterface',
            [ $factory, 'create' ]
        );

        $core->factory(
            'Surume\Channel\ChannelCompositeInterface',
            [ $factory, 'create' ]
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Surume\Channel\ChannelModelFactoryInterface'
        );

        $core->remove(
            'Surume\Channel\ChannelModelInterface'
        );

        $core->remove(
            'Surume\Channel\ChannelFactoryInterface'
        );

        $core->remove(
            'Surume\Channel\ChannelBaseInterface'
        );

        $core->remove(
            'Surume\Channel\ChannelCompositeInterface'
        );
    }

    /**
     * @param CoreInterface $core
     * @throws Exception
     */
    protected function boot(CoreInterface $core)
    {
        $config = $core->make('Surume\Config\ConfigInterface');
        $factory = $core->make('Surume\Channel\ChannelModelFactoryInterface');

        $models = (array) $config->get('channel.models');
        foreach ($models as $modelClass)
        {
            if (!class_exists($modelClass))
            {
                throw new ResourceUndefinedException("ChannelModel [$modelClass] does not exist.");
            }

            $factory
                ->define($modelClass, function($config) use($modelClass) {
                    return new $modelClass($config);
                });
        }

        $plugins = (array) $config->get('channel.plugins');
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
