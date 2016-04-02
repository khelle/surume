<?php

namespace Surume\Transfer\Websocket\Driver\Version\HyBi10;

use Surume\Transfer\Http\HttpRequestInterface;
use Surume\Transfer\Websocket\Driver\Version\RFC6455\Version as VersionRFC6455;
use Surume\Transfer\Websocket\Driver\Version\VersionInterface;

class Version extends VersionRFC6455 implements VersionInterface
{
    /**
     * @override
     */
    public function isRequestSupported(HttpRequestInterface $request)
    {
        $version = (int)(string)$request->getHeaderLine('Sec-WebSocket-Version');

        return ($version >= 6 && $version < 13);
    }

    /**
     * @override
     */
    public function getVersionNumber()
    {
        return 6;
    }
}
