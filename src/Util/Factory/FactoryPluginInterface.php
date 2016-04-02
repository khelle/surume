<?php

namespace Surume\Util\Factory;

use Surume\Throwable\Exception\Runtime\ExecutionException;

interface FactoryPluginInterface
{
    /**
     * @param FactoryInterface $factory
     * @throws ExecutionException
     */
    public function registerPlugin(FactoryInterface $factory);

    /**
     * @param FactoryInterface $factory
     */
    public function unregisterPlugin(FactoryInterface $factory);
}
