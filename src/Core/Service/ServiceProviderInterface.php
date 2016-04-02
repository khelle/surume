<?php

namespace Surume\Core\Service;

use Surume\Core\CoreInterface;
use Surume\Throwable\Exception\Runtime\ExecutionException;

interface ServiceProviderInterface
{
    /**
     * @return string[]
     */
    public function requires();

    /**
     * @return string[]
     */
    public function provides();

    /**
     * @return bool
     */
    public function isRegistered();

    /**
     * @param CoreInterface $core
     * @throws ExecutionException
     */
    public function registerProvider(CoreInterface $core);

    /**
     * @param CoreInterface $core
     */
    public function unregisterProvider(CoreInterface $core);

    /**
     * @param CoreInterface $core
     * @throws ExecutionException
     */
    public function bootProvider(CoreInterface $core);
}
