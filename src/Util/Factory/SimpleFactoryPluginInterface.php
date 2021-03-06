<?php

namespace Surume\Util\Factory;

use Surume\Throwable\Exception\Runtime\ExecutionException;

interface SimpleFactoryPluginInterface
{
    /**
     * @param SimpleFactoryInterface $factory
     * @throws ExecutionException
     */
    public function registerPlugin(SimpleFactoryInterface $factory);

    /**
     * @param SimpleFactoryInterface $factory
     */
    public function unregisterPlugin(SimpleFactoryInterface $factory);
}
