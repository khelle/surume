<?php

namespace Surume\Supervisor;

use Surume\Promise\Promise;
use Surume\Promise\PromiseInterface;
use Surume\Throwable\Exception\Runtime\Execution\RejectionException;
use Error;
use Exception;

class SolverComposite implements SolverInterface
{
    /**
     * @var SolverInterface[]
     */
    protected $handlers;

    /**
     * @param SolverInterface[] $handlers
     */
    public function __construct($handlers = [])
    {
        $this->handlers = $handlers;

        $this->onCreate();
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->onDestroy();

        unset($this->handlers);
    }

    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return PromiseInterface
     */
    public function __invoke($ex, $params = [])
    {
        return $this->handle($ex, $params);
    }

    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return PromiseInterface
     */
    public function handle($ex, $params = [])
    {
        $promise = Promise::doResolve([ $ex, $params ]);

        return $promise
            ->spread(function($ex, $params) {
                return $this->handler($ex, $params);
            });
    }

    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return mixed
     * @throws RejectionException
     */
    protected function handler($ex, $params = [])
    {
        $promise = Promise::doResolve();

        foreach ($this->handlers as $handler)
        {
            $current = $handler;

            $promise = $promise->then(
                function ($value) use($ex, $params, $current) {
                    return Promise::doResolve($current->handle($ex, $params));
                }
            );
        }

        return $promise;
    }

    /**
     *
     */
    protected function onCreate()
    {}

    /**
     *
     */
    protected function onDestroy()
    {}
}
