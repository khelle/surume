<?php

namespace Surume\Console\Client;

use Surume\Core\CoreSetterAwareInterface;
use Surume\Core\CoreInputContextInterface;
use Surume\Event\EventHandler;
use Surume\Loop\LoopExtendedAwareInterface;

/**
 * @event start
 * @event stop
 * @event command
 */
interface ConsoleClientInterface extends CoreInputContextInterface, CoreSetterAwareInterface, LoopExtendedAwareInterface
{
    /**
     * @param callable $callback
     * @return EventHandler
     */
    public function onStart(callable $callback);

    /**
     * @param callable $callback
     * @return EventHandler
     */
    public function onStop(callable $callback);

    /**
     * @param callable $callback
     * @return EventHandler
     */
    public function onCommand(callable $callback);

    /**
     *
     */
    public function start();

    /**
     *
     */
    public function stop();
}
