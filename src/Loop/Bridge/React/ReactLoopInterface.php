<?php

namespace Surume\Loop\Bridge\React;

use Surume\Loop\LoopInterface;

interface ReactLoopInterface extends \React\EventLoop\LoopInterface
{
    /**
     * Return the actual LoopInterface which is adapted by current ReactLoopInterface.
     *
     * @return LoopInterface
     */
    public function getActualLoop();
}
