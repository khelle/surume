<?php

namespace Surume\Core;

interface CoreGetterAwareInterface
{
    /**
     * @return CoreInterface
     */
    public function getCore();

    /**
     * @return CoreInterface
     */
    public function core();
}
