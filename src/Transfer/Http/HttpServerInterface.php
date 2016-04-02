<?php

namespace Surume\Transfer\Http;

use Surume\Transfer\Http\Driver\HttpDriverInterface;
use Surume\Transfer\IoServerComponentInterface;

interface HttpServerInterface extends IoServerComponentInterface
{
    /**
     * Return current driver
     *
     * @return HttpDriverInterface
     */
    public function getDriver();
}
