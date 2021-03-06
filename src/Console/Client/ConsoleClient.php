<?php

namespace Surume\Console\Client;

use Surume\Console\Client\Provider\Console\ConsoleProvider;
use Surume\Core\CoreAwareTrait;
use Surume\Core\CoreInterface;
use Surume\Event\BaseEventEmitter;
use Surume\Event\EventHandler;
use Surume\Loop\LoopExtendedAwareTrait;
use Surume\Runtime\RuntimeInterface;

class ConsoleClient extends BaseEventEmitter implements ConsoleClientInterface
{
    use CoreAwareTrait;
    use LoopExtendedAwareTrait;

    /**
     *
     */
    public function __destruct()
    {
        $this->setCore(null);
    }

    /**
     * @return string
     */
    public function type()
    {
        return $this->core()->unit();
    }

    /**
     * @return string|null
     */
    public function parent()
    {
        return null;
    }

    /**
     * @return string
     */
    public function alias()
    {
        return 'ConsoleClient';
    }

    /**
     * @return string
     */
    public function name()
    {
        return 'ConsoleClient';
    }

    /**
     * @param callable $callback
     * @return EventHandler
     */
    public function onStart(callable $callback)
    {
        return $this->on('start', $callback);
    }

    /**
     * @param callable $callback
     * @return EventHandler
     */
    public function onStop(callable $callback)
    {
        return $this->on('stop', $callback);
    }

    /**
     * @param callable $callback
     * @return EventHandler
     */
    public function onCommand(callable $callback)
    {
        return $this->on('command', $callback);
    }

    /**
     * @return int
     */
    public function start()
    {
        $this->loop()->afterTick(function() {
            $this->emit('start');
            $this->emit('command');
        });

        $this->loop()->start();
    }

    /**
     *
     */
    public function stop()
    {
        $this->emit('stop');
        $this->loop()->stop();
    }

    /**
     * @param CoreInterface $core
     * @return array
     */
    public function config(CoreInterface $core)
    {
        return [];
    }

    /**
     * @param CoreInterface $core
     * @return RuntimeInterface
     */
    public function boot(CoreInterface $core)
    {
        return $this;
    }

    /**
     * @param CoreInterface $core
     * @return RuntimeInterface
     */
    public function construct(CoreInterface $core)
    {
        return $this;
    }

    /**
     * @param CoreInterface $core
     * @return array
     */
    public function internalConfig(CoreInterface $core)
    {
        return $this->config($core);
    }

    /**
     * @param CoreInterface $core
     * @return RuntimeInterface
     */
    public function internalBoot(CoreInterface $core)
    {
        $core->registerProvider(new ConsoleProvider($this));

        return $this->boot($core);
    }

    /**
     * @param CoreInterface $core
     * @return RuntimeInterface
     */
    public function internalConstruct(CoreInterface $core)
    {
        return $this->construct($core);
    }
}
