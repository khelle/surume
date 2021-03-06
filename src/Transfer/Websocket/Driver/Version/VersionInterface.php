<?php

namespace Surume\Transfer\Websocket\Driver\Version;

use Surume\Transfer\Http\HttpRequestInterface;

interface VersionInterface
{
    /**
     * Given an HTTP header, determine if this version should handle the protocol.
     *
     * @param HttpRequestInterface $request
     * @return bool
     */
    public function isRequestSupported(HttpRequestInterface $request);

    /**
     * Return numberic identificator of the protocol.
     *
     * @return int
     */
    public function getVersionNumber();
}
