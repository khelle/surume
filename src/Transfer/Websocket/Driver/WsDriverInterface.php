<?php

namespace Surume\Transfer\Websocket\Driver;

use Surume\Transfer\Websocket\Driver\Version\VersionManagerInterface;

interface WsDriverInterface extends VersionManagerInterface
{
    /**
     * Toggle whether to check encoding of incoming messages.
     *
     * @param bool
     */
    public function setEncodingChecks($opt);
}
