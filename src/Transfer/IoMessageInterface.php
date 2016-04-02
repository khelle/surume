<?php

namespace Surume\Transfer;

interface IoMessageInterface
{
    /**
     * Return original message as string.
     *
     * @return string
     */
    public function read();
}
