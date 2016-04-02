<?php

namespace Surume\Core\Provider\Container;

use Surume\Core\CoreInterface;
use Surume\Core\Service\ServiceProvider;
use Surume\Core\Service\ServiceProviderInterface;

class ContainerProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $provides = [
        'Surume\Container\ContainerInterface'
    ];

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $core->instance(
            'Surume\Container\ContainerInterface',
            $core
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Surume\Container\ContainerInterface'
        );
    }
}
