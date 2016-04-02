<?php

namespace Surume\Core\Provider\Supervisor;

use Exception;
use Surume\Core\CoreInterface;
use Surume\Core\Service\ServiceProvider;
use Surume\Core\Service\ServiceProviderInterface;
use Surume\Runtime\Supervisor\SolverFactory;
use Surume\Supervisor\SolverFactoryInterface;
use Surume\Supervisor\Supervisor;
use Surume\Throwable\Exception\Logic\Resource\ResourceUndefinedException;
use Surume\Throwable\Exception\Logic\InvalidArgumentException;
use Surume\Util\Factory\FactoryPluginInterface;

class SupervisorProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Surume\Runtime\RuntimeInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Surume\Supervisor\SolverFactoryInterface',
        'Surume\Supervisor\SupervisorInterface'
    ];

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $runtime = $core->make('Surume\Runtime\RuntimeInterface');

        $factory = new SolverFactory($runtime);
        $config = [];

        $core->instance(
            'Surume\Supervisor\SolverFactoryInterface',
            $factory
        );

        $core->factory(
            'Surume\Supervisor\SupervisorInterface',
            function (SolverFactoryInterface $passedFactory = null, $passedConfig = [], $passedRules = []) use($factory, $config) {
                return new Supervisor(
                    $passedFactory !== null ? $passedFactory : $factory,
                    array_merge($config, $passedConfig),
                    $passedRules
                );
            }
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Surume\Supervisor\SolverFactoryInterface'
        );

        $core->remove(
            'Surume\Supervisor\SupervisorInterface'
        );
    }

    /**
     * @param CoreInterface $core
     * @throws Exception
     */
    protected function boot(CoreInterface $core)
    {
        $config  = $core->make('Surume\Config\ConfigInterface');
        $factory = $core->make('Surume\Supervisor\SolverFactoryInterface');

        $handlers = (array) $config->get('error.handlers');
        foreach ($handlers as $handlerClass)
        {
            if (!class_exists($handlerClass))
            {
                throw new ResourceUndefinedException("Solver [$handlerClass] does not exist.");
            }

            $factory
                ->define($handlerClass, function($runtime, $context = []) use($handlerClass) {
                    return new $handlerClass($runtime, $context);
                });
        }

        $plugins = (array) $config->get('error.plugins');
        foreach ($plugins as $pluginClass)
        {
            if (!class_exists($pluginClass))
            {
                throw new ResourceUndefinedException("SupervisorPlugin [$pluginClass] does not exist.");
            }

            $plugin = new $pluginClass();

            if (!($plugin instanceof FactoryPluginInterface))
            {
                throw new InvalidArgumentException("SupervisorPlugin [$pluginClass] does not implement SupervisorPluginInterface.");
            }

            $plugin->registerPlugin($factory);
        }
    }
}
