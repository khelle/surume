<?php

namespace Surume\Transfer;

use Surume\Loop\LoopResourceInterface;

interface IoServerInterface extends LoopResourceInterface
{
    /**
     * Close the underlying SocketServerListner.
     */
    public function close();
}
