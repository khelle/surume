<?php

namespace Surume\Transfer\Websocket;

use Surume\Transfer\Websocket\Driver\WsDriverInterface;
use Surume\Transfer\IoServerComponentInterface;

interface WsServerInterface extends IoServerComponentInterface
{
    /**
     * Return current driver
     *
     * @return WsDriverInterface
     */
    public function getDriver();
}
