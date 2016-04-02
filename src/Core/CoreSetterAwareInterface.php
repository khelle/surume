<?php

namespace Surume\Core;

interface CoreSetterAwareInterface
{
    /**
     * @param CoreInterface $core
     */
    public function setCore(CoreInterface $core);
}
