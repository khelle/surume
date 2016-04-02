<?php

namespace Surume\Core\Service;

use Surume\Throwable\Exception\Runtime\ExecutionException;
use Surume\Throwable\Exception\Logic\InvalidArgumentException;
use Surume\Throwable\Exception\Logic\Resource\ResourceDefinedException;
use Surume\Throwable\Exception\Logic\Resource\ResourceUndefinedException;

interface ServiceRegisterInterface
{
    /**
     * @param ServiceProviderInterface|string $provider
     * @param bool $force
     * @throws ExecutionException
     * @throws InvalidArgumentException
     * @throws ResourceDefinedException
     */
    public function registerProvider($provider, $force = false);

    /**
     * @param ServiceProviderInterface|string $provider
     * @throws InvalidArgumentException
     * @throws ResourceUndefinedException
     */
    public function unregisterProvider($provider);

    /**
     * @param ServiceProviderInterface|string $provider
     * @return ServiceProviderInterface|null
     */
    public function getProvider($provider);

    /**
     * @param string $providerClass
     * @return ServiceProviderInterface|null
     */
    public function resolveProviderClass($providerClass);
}
