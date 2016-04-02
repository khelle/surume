<?php

namespace Surume\Event;

use Surume\Loop\LoopAwareTrait;

trait AsyncEventEmitterTrait
{
    use LoopAwareTrait;
    use BaseEventEmitterTrait;

    /**
     * @param int $pointer
     * @param string $event
     * @param callable $listener
     * @return callable
     */
    protected function attachOnListener($pointer, $event, callable $listener)
    {
        return function() use($listener) {
            $args = func_get_args();
            $this->getLoop()->afterTick(function() use($listener, $args) {
                call_user_func_array($listener, $args);
            });
        };
    }

    /**
     * @param int $pointer
     * @param string $event
     * @param callable $listener
     * @return callable
     */
    protected function attachOnceListener($pointer, $event, callable $listener)
    {
        return function() use($listener, $event, $pointer) {
            unset($this->emitterEventHandlers[$event][$pointer]);

            $args = func_get_args();
            $this->getLoop()->afterTick(function() use($listener, $args) {
                call_user_func_array($listener, $args);
            });
        };
    }
}
