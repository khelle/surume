<?php

namespace Surume\Loop\Bridge\React;

use Surume\Loop\Timer\TimerInterface;

interface ReactTimerInterface extends \React\EventLoop\Timer\TimerInterface
{
    /**
     * Return the actual TimerInterface which is adapted by current ReactTimerInterface.
     *
     * @return TimerInterface;
     */
    public function getActualTimer();
}
