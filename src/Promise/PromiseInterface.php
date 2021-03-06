<?php

namespace Surume\Promise;

use Error;
use Exception;

interface PromiseInterface extends DeferredInterface
{
    /**
     * @param callable|null $onFulfilled
     * @param callable|null $onRejected
     * @param callable|null $onCancel
     * @param callable|null $onProgress
     * @return PromiseInterface
     */
    public function then(callable $onFulfilled = null, callable $onRejected = null, callable $onCancel = null, callable $onProgress = null);

    /**
     * @param callable|null $onFulfilled
     * @param callable|null $onRejected
     * @param callable|null $onCancel
     * @param callable|null $onProgress
     * @throws Error|Exception
     */
    public function done(callable $onFulfilled = null, callable $onRejected = null, callable $onCancel = null, callable $onProgress = null);

    /**
     * @param callable|null $onFulfilled
     * @param callable|null $onRejected
     * @param callable|null $onCancel
     * @param callable|null $onProgress
     * @return PromiseInterface
     */
    public function spread(callable $onFulfilled = null, callable $onRejected = null, callable $onCancel = null, callable $onProgress = null);

    /**
     * @param callable $onSuccess
     * @return PromiseInterface
     */
    public function success(callable $onSuccess);

    /**
     * @param callable $onFailure
     * @return PromiseInterface
     */
    public function failure(callable $onFailure);

    /**
     * @param callable $onCancel
     * @return PromiseInterface
     */
    public function abort(callable $onCancel);

    /**
     * @param callable $onProgress
     * @return PromiseInterface
     */
    public function progress(callable $onProgress);

    /**
     * @param callable $onFulfilledOrRejected
     * @return PromiseInterface
     */
    public function always(callable $onFulfilledOrRejected);

    /**
     * @return bool
     */
    public function isPending();

    /**
     * @return bool
     */
    public function isFulfilled();
    /**
     * @return bool
     */
    public function isRejected();

    /**
     * @return bool
     */
    public function isCancelled();

    /**
     * @return mixed|null
     */
    public function value();

    /**
     * @return Error|Exception|string|null
     */
    public function reason();
}
