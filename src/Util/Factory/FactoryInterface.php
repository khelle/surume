<?php

namespace Surume\Util\Factory;

use Surume\Throwable\Exception\Logic\IllegalCallException;
use Surume\Throwable\Exception\Logic\IllegalFieldException;

interface FactoryInterface
{
    /**
     * @param string $name
     * @param mixed $value
     * @return FactoryInterface
     */
    public function bindParam($name, $value);

    /**
     * @param string $name
     * @param mixed $value
     * @return FactoryInterface
     */
    public function unbindParam($name, $value);

    /**
     * @param string $name
     * @return mixed
     * @throws IllegalFieldException
     */
    public function getParam($name);

    /**
     * @param string $param
     * @return bool
     */
    public function hasParam($param);

    /**
     * @return mixed[]
     */
    public function getParams();

    /**
     * @param string $name
     * @param callable $factoryMethod
     * @return FactoryInterface
     */
    public function define($name, callable $factoryMethod);

    /**
     * @param string $name
     * @return FactoryInterface
     */
    public function remove($name);

    /**
     * @param string $name
     * @param mixed[] $args
     * @return mixed
     * @throws IllegalCallException
     */
    public function create($name, $args = []);

    /**
     * @param callable[] $factoryMethods
     */
    public function addDefinitions($factoryMethods);

    /**
     * @return mixed[]
     */
    public function getDefinitions();
}
