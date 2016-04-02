<?php

namespace Surume\Supervisor;

use Surume\Promise\Promise;
use Surume\Promise\PromiseInterface;
use Surume\Throwable\Exception\Runtime\ExecutionException;
use Surume\Throwable\Exception\Logic\IllegalCallException;
use Surume\Throwable\Exception\LogicException;
use Error;
use Exception;

class Supervisor implements SupervisorInterface
{
    /**
     * @var SolverFactoryInterface
     */
    protected $factory;

    /**
     * @var mixed[]
     */
    protected $params;

    /**
     * @var SolverInterface[]
     */
    protected $rules;

    /**
     * @param SolverFactoryInterface $factory
     * @param mixed[] $params
     * @param SolverInterface[] $rules
     */
    public function __construct(SolverFactoryInterface $factory, $params = [], $rules = [])
    {
        $this->factory = $factory;
        $this->params = [];
        $this->rules = [];

        foreach ($params as $paramKey=>$paramValue)
        {
            $this->setParam($paramKey, $paramValue);
        }

        foreach ($rules as $ruleException=>$ruleHandler)
        {
            $this->setHandler($ruleException, $ruleHandler);
        }
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->factory);
        unset($this->params);
        unset($this->rules);
    }

    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return PromiseInterface
     * @throws ExecutionException
     */
    public function __invoke($ex, $params = [])
    {
        return $this->handle($ex, $params);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function existsParam($key)
    {
        return isset($this->params[$key]);
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function setParam($key, $value)
    {
        $this->params[$key] = $value;
    }

    /**
     * @param string $key
     * @return mixed|null $value
     */
    public function getParam($key)
    {
        return $this->existsParam($key) ? $this->params[$key] : null;
    }

    /**
     * @param string $exception
     * @return bool
     */
    public function existsHandler($exception)
    {
        return isset($this->rules[$exception]);
    }

    /**
     * @param string $exception
     * @param SolverInterface|string|string[] $handler
     * @throws LogicException
     */
    public function setHandler($exception, $handler)
    {
        if (is_string($handler))
        {
            $handler = $this->resolveHandler($handler);
        }
        else if (is_array($handler))
        {
            $names = $handler;
            $handlers = [];
            foreach ($names as $name)
            {
                $handlers[] = $this->resolveHandler($name);
            }

            $handler = new SolverComposite($handlers);
        }

        $this->rules[$exception] = $handler;
    }

    /**
     * @param string $exception
     * @return SolverInterface|null
     */
    public function getHandler($exception)
    {
        return $this->existsHandler($exception) ? $this->rules[$exception] : null;
    }

    /**
     * @param string $exception
     */
    public function removeHandler($exception)
    {
        unset($this->rules[$exception]);
    }

    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @param int $try
     * @return PromiseInterface
     */
    public function handle($ex, $params = [], &$try = 0)
    {
        $classBaseEx = get_class($ex);
        $classes = array_merge([ $classBaseEx ], class_parents($ex));

        $indexMin = -1;
        $chosen = null;
        foreach ($classes as $class)
        {
            $indexCurrent = array_search($class, array_keys($this->rules), true);
            if ($indexCurrent !== false && ($indexMin === -1 || $indexCurrent < $indexMin))
            {
                $indexMin = $indexCurrent;
                $chosen = $class;
            }
        }

        if ($chosen === null)
        {
            return Promise::doReject(
                new ExecutionException("SolverInterface [$classBaseEx] is not registered.")
            );
        }

        $try++;
        $params = array_merge($this->params, $params);
        $valueOrPromise = $this->getHandler($chosen)->handle($ex, $params);

        return Promise::doResolve($valueOrPromise);
    }

    /**
     * @param string $name
     * @return SolverInterface
     * @throws LogicException
     */
    protected function resolveHandler($name)
    {
        try
        {
            $handler = $this->factory->create($name);
        }
        catch (Error $ex)
        {
            throw new IllegalCallException("Tried to invoke [$name] which is undefined.");
        }
        catch (Exception $ex)
        {
            throw new IllegalCallException("Tried to invoke [$name] which is undefined.");
        }

        return $handler;
    }
}
