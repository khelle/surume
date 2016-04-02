<?php

namespace Surume\Runtime\Provider\Runtime;

use Surume\Channel\ChannelCompositeInterface;
use Surume\Config\ConfigInterface;
use Surume\Core\CoreInterface;
use Surume\Core\Service\ServiceProvider;
use Surume\Core\Service\ServiceProviderInterface;
use Surume\Loop\Timer\TimerCollection;
use Surume\Runtime\Container\ProcessManagerFactory;
use Surume\Runtime\Container\ThreadManagerFactory;
use Surume\Runtime\Runtime;
use Surume\Runtime\RuntimeInterface;
use Surume\Runtime\RuntimeManager;
use Surume\Runtime\RuntimeManagerFactoryInterface;
use Surume\Runtime\RuntimeManagerInterface;
use Surume\System\SystemUnix;
use Surume\Throwable\Exception\System\ChildUnresponsiveException;
use Surume\Throwable\Exception\System\ParentUnresponsiveException;

class RuntimeManagerProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Surume\Config\ConfigInterface',
        'Surume\Filesystem\FilesystemInterface',
        'Surume\Runtime\RuntimeInterface',
        'Surume\Runtime\Channel\ChannelInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Surume\Runtime\Container\ProcessManagerInterface',
        'Surume\Runtime\Container\ThreadManagerInterface',
        'Surume\Runtime\RuntimeManagerInterface'
    ];

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $system  = new SystemUnix();
        $config  = $core->make('Surume\Config\ConfigInterface');
        $env     = $core->make('Surume\Core\EnvironmentInterface');
        $fs      = $core->make('Surume\Filesystem\FilesystemInterface');
        $runtime = $core->make('Surume\Runtime\RuntimeInterface');
        $channel = $core->make('Surume\Runtime\Channel\ChannelInterface');

        $this->registerRuntimeSupervision($runtime, $channel, $config);

        $defaultConfig = [
            'runtime'    => $runtime,
            'channel'    => $channel,
            'env'        => $env,
            'system'     => $system,
            'filesystem' => $fs,
            'receiver'   => $runtime->parent()
        ];

        $factoryProcess = new ProcessManagerFactory();
        $factoryThread = new ThreadManagerFactory();

        if ($core->unit() === Runtime::UNIT_THREAD)
        {
            $factoryProcess->remove('Surume\Runtime\Container\Manager\ProcessManagerBase');
        }

        $managerProcess = $this->createManager(
            $core,
            $factoryProcess,
            $defaultConfig,
            $config->get('runtime.manager.process')
        );

        $managerThread = $this->createManager(
            $core,
            $factoryThread,
            $defaultConfig,
            $config->get('runtime.manager.thread')
        );

        $managerRuntime = new RuntimeManager($managerProcess, $managerThread);

        $core->instance(
            'Surume\Runtime\Container\ProcessManagerInterface',
            $managerProcess
        );

        $core->instance(
            'Surume\Runtime\Container\ThreadManagerInterface',
            $managerThread
        );

        $core->instance(
            'Surume\Runtime\RuntimeManagerInterface',
            $managerRuntime
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Surume\Runtime\Container\ProcessManagerInterface'
        );

        $core->remove(
            'Surume\Runtime\Container\ThreadManagerInterface'
        );

        $core->remove(
            'Surume\Runtime\RuntimeManagerInterface'
        );
    }

    /**
     * @param RuntimeInterface $runtime
     * @param ChannelCompositeInterface $composite
     * @param ConfigInterface $config
     */
    private function registerRuntimeSupervision(RuntimeInterface $runtime, ChannelCompositeInterface $composite, ConfigInterface $config)
    {
        $timerCollection = new TimerCollection();

        $channel = $composite->bus('slave');
        $keepalive = $config->get('core.tolerance.child.keepalive');
        $channel->on('disconnect', function($alias) use($runtime, $keepalive, $timerCollection) {
            if ($keepalive <= 0)
            {
                return;
            }

            $timer = $runtime->loop()->addTimer($keepalive, function() use($alias, $runtime, $timerCollection) {
                $timerCollection->removeTimer($alias);
                $runtime->fail(
                    new ChildUnresponsiveException("Child runtime [$alias] is unresponsive."),
                    [ 'origin' => $alias ]
                );
            });

            $timerCollection->addTimer($alias, $timer);
        });
        $channel->on('connect', function($alias) use($timerCollection) {
            if (($timer = $timerCollection->getTimer($alias)) !== null)
            {
                $timer->cancel();
                $timerCollection->removeTimer($alias);
            }
        });

        $channel = $composite->bus('master');
        $keepalive = $config->get('core.tolerance.parent.keepalive');
        $channel->on('disconnect', function($alias) use($runtime, $keepalive, $timerCollection) {
            if ($keepalive <= 0)
            {
                return;
            }

            $timer = $runtime->loop()->addTimer($keepalive, function() use($alias, $runtime, $timerCollection) {
                $timerCollection->removeTimer($alias);
                $runtime->fail(
                    new ParentUnresponsiveException("Parent runtime [$alias] is unresponsive."),
                    [ 'origin' => $alias ]
                );
            });

            $timerCollection->addTimer($alias, $timer);
        });
        $channel->on('connect', function($alias) use($timerCollection) {
            if (($timer = $timerCollection->getTimer($alias)) !== null)
            {
                $timer->cancel();
                $timerCollection->removeTimer($alias);
            }
        });
    }

    /**
     * @param CoreInterface $core
     * @param RuntimeManagerFactoryInterface $managerFactory
     * @param mixed[] $default
     * @param mixed[] $config
     * @return RuntimeManagerInterface
     */
    private function createManager(CoreInterface $core, RuntimeManagerFactoryInterface $managerFactory, $default, $config)
    {
        $managerClass = $config['class'];
        $managerConfig = array_merge($default, $config['config']);

        foreach ($managerConfig as $key=>$value)
        {
            if (is_string($value) && class_exists($value))
            {
                $managerConfig[$key] = $core->make($value);
            }
        }

        return $managerFactory->create($managerClass, [ $managerConfig ]);
    }
}
