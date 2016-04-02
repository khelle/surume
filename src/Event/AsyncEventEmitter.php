<?php

namespace Surume\Event;

use Surume\Loop\LoopAwareInterface;
use Surume\Loop\LoopInterface;

class AsyncEventEmitter implements EventEmitterInterface, LoopAwareInterface
{
    use AsyncEventEmitterTrait;

    /**
     * @param LoopInterface $loop
     */
    public function __construct(LoopInterface $loop)
    {
        $this->setLoop($loop);
    }
}
