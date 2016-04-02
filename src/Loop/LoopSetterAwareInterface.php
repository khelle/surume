<?php

namespace Surume\Loop;

interface LoopSetterAwareInterface
{
    /**
     * @param LoopInterface $loop
     */
    public function setLoop(LoopInterface $loop);
}
