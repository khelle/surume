<?php

namespace Surume\Promise;

use Error;
use Exception;

interface DeferredInterface
{
    /**
     * @param mixed|null $value
     * @return PromiseInterface
     */
    public function resolve($value = null);

    /**
     * @param Error|Exception|string|null $reason
     * @return PromiseInterface
     */
    public function reject($reason = null);

    /**
     * @param Error|Exception|string|null $reason
     * @return PromiseInterface
     */
    public function cancel($reason = null);

    /**
     * @param mixed|null $update
     * @return PromiseInterface
     */
    public function notify($update = null);
}
