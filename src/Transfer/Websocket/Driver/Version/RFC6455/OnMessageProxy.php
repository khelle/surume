<?php

namespace Surume\Transfer\Websocket\Driver\Version\RFC6455;

use Surume\Transfer\IoMessage;
use Ratchet\ConnectionInterface;
use Ratchet\MessageInterface;

class OnMessageProxy implements MessageInterface
{
    protected $target;

    /**
     * @param callable $target
     */
    public function __construct(callable $target)
    {
        $this->target = $target;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->target);
    }

    /**
     * @override
     */
    function onMessage(ConnectionInterface $from, $msg)
    {
        $callable = $this->target;
        $callable($from, new IoMessage($msg));
    }
}
