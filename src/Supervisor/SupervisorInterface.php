<?php

namespace Surume\Supervisor;

use Surume\Promise\PromiseInterface;
use Surume\Throwable\Exception\Runtime\ExecutionException;
use Surume\Throwable\Exception\Logic\IllegalCallException;
use Surume\Throwable\Exception\LogicException;
use Error;
use Exception;

interface SupervisorInterface
{
    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return PromiseInterface
     * @throws ExecutionException
     */
    public function __invoke($ex, $params = []);

    /**
     * @param string $key
     * @return bool
     */
    public function existsParam($key);

    /**
     * @param string $key
     * @param mixed $value
     */
    public function setParam($key, $value);

    /**
     * @param string $key
     * @return mixed|null $value
     */
    public function getParam($key);

    /**
     * @param string $exception
     * @return bool
     */
    public function existsHandler($exception);

    /**
     * @param string $exception
     * @param SolverInterface|string|string[] $handler
     * @throws IllegalCallException
     * @throws LogicException
     */
    public function setHandler($exception, $handler);

    /**
     * @param string $exception
     * @return SolverInterface|null
     */
    public function getHandler($exception);

    /**
     * @param string $exception
     */
    public function removeHandler($exception);

    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return PromiseInterface
     * @throws ExecutionException
     */
    public function handle($ex, $params = []);
}
