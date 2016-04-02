<?php

namespace Surume\Runtime\Provider\Supervisor;

use Exception;
use Surume\Config\ConfigInterface;
use Surume\Core\CoreInterface;
use Surume\Core\Service\ServiceProvider;
use Surume\Core\Service\ServiceProviderInterface;
use Surume\Supervisor\SolverInterface;
use Surume\Supervisor\SupervisorInterface;
use Surume\Supervisor\SupervisorPluginInterface;
use Surume\Throwable\Exception\Logic\Resource\ResourceUndefinedException;
use Surume\Throwable\Exception\Logic\InvalidArgumentException;

class SupervisorProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Surume\Config\ConfigInterface',
        'Surume\Supervisor\SupervisorInterface',
        'Surume\Supervisor\SolverFactoryInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Surume\Runtime\Supervisor\SupervisorBaseInterface',
        'Surume\Runtime\Supervisor\SupervisorRemoteInterface'
    ];

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $config = $core->make('Surume\Config\ConfigInterface');

        $errorManager    = $core->make('Surume\Supervisor\SupervisorInterface', [ null, $config->get('error.manager.params') ]);
        $errorSupervisor = $core->make('Surume\Supervisor\SupervisorInterface', [ null, $config->get('error.supervisor.params') ]);

        $core->instance(
            'Surume\Runtime\Supervisor\SupervisorBaseInterface',
            $errorManager
        );

        $core->instance(
            'Surume\Runtime\Supervisor\SupervisorRemoteInterface',
            $errorSupervisor
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Surume\Runtime\Supervisor\SupervisorBaseInterface'
        );

        $core->remove(
            'Surume\Runtime\Supervisor\SupervisorRemoteInterface'
        );
    }

    /**
     * @param CoreInterface $core
     * @throws Exception
     */
    protected function boot(CoreInterface $core)
    {
        $config = $core->make('Surume\Config\ConfigInterface');

        $baseSupervisor   = $core->make('Surume\Runtime\Supervisor\SupervisorBaseInterface');
        $remoteSupervisor = $core->make('Surume\Runtime\Supervisor\SupervisorRemoteInterface');

        $this->bootBaseSupervisor($baseSupervisor, $config);
        $this->bootRemoteSupervisor($remoteSupervisor, $config);
    }

    /**
     * @param SupervisorInterface $supervisor
     * @param ConfigInterface $config
     * @throws Exception
     */
    private function bootBaseSupervisor(SupervisorInterface $supervisor, ConfigInterface $config)
    {
        $handlers = (array) $config->get('error.manager.handlers');

        $default = [
            $this->systemException('ChildUnresponsiveException')   => [ 'RuntimeRecreate', 'ContainerContinue' ],
            $this->systemException('ParentUnresponsiveException')  => [ 'ContainerDestroy' ],
            $this->systemError('FatalError')                       => [ 'CmdLog', 'ContainerDestroy' ],
            'Error'                                                => [ 'CmdLog', 'ContainerContinue' ],
            'Exception'                                            => [ 'CmdLog', 'ContainerContinue' ]
        ];

        $plugins = (array) $config->get('error.manager.plugins');

        $this->bootBaseOrRemote($supervisor, $default, $handlers, $plugins);
    }

    /**
     * @param SupervisorInterface $supervisor
     * @param ConfigInterface $config
     * @throws Exception
     */
    private function bootRemoteSupervisor(SupervisorInterface $supervisor, ConfigInterface $config)
    {
        $handlers = (array) $config->get('error.supervisor.handlers');

        $default = [
            $this->systemError('FatalError')    => 'ContainerDestroy',
            'Error'                             => 'ContainerContinue',
            'Exception'                         => 'ContainerContinue'
        ];

        $plugins = (array) $config->get('error.supervisor.plugins');

        $this->bootBaseOrRemote($supervisor, $default, $handlers, $plugins);
    }

    /**
     * @param SupervisorInterface $supervisor
     * @param string[] $default
     * @param string[] $handlers
     * @param string[] $plugins
     * @throws Exception
     */
    private function bootBaseOrRemote($supervisor, $default = [], $handlers = [], $plugins = [])
    {
        $this->setHandlers($supervisor, $handlers);
        $this->setHandlers($supervisor, $default);

        foreach ($plugins as $pluginClass)
        {
            if (!class_exists($pluginClass))
            {
                throw new ResourceUndefinedException("SupervisorPlugin [$pluginClass] does not exist.");
            }

            $plugin = new $pluginClass();

            if (!($plugin instanceof SupervisorPluginInterface))
            {
                throw new InvalidArgumentException("SupervisorPlugin [$pluginClass] does not implement SupervisorPluginInterface.");
            }

            $plugin->registerPlugin($supervisor);
        }
    }

    /**
     * @param SupervisorInterface $supervisor
     * @param SolverInterface[]|string[]|string[][] $handlers
     */
    private function setHandlers(SupervisorInterface $supervisor, $handlers)
    {
        foreach ($handlers as $exception=>$handler)
        {
            $supervisor->setHandler($exception, $handler);
        }
    }

    /**
     * @param string $error
     * @return string
     */
    private function systemError($error)
    {
        return 'Surume\Throwable\Error\\' . $error;
    }

    /**
     * @param string $exception
     * @return string
     */
    private function systemException($exception)
    {
        return 'Surume\Throwable\Exception\System\\' . $exception;
    }
}
