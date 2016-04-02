<?php

namespace Surume\Ipc\Socket;

use Surume\Event\EventEmitterInterface;
use Surume\Loop\LoopResourceInterface;
use Surume\Stream\StreamBaseInterface;

/**
 * @event connect(object, SocketInterface)
 */
interface SocketListenerInterface extends EventEmitterInterface, LoopResourceInterface, StreamBaseInterface
{
    /**
     * Get server endpoint.
     *
     * This method returns server endpoint with this pattern [$protocol://$address:$port].
     *
     * @return string
     */
    public function getLocalEndpoint();
}
