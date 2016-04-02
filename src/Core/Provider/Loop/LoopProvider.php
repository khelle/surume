<?php

namespace Surume\Core\Provider\Loop;

use Surume\Core\CoreInterface;
use Surume\Core\Service\ServiceProvider;
use Surume\Core\Service\ServiceProviderInterface;
use Surume\Loop\Loop;

class LoopProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Surume\Config\ConfigInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Surume\Loop\LoopInterface',
        'Surume\Loop\LoopExtendedInterface'
    ];

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $config = $core->make('Surume\Config\ConfigInterface');

        $model = $config->get('loop.model');
        $loop = new Loop(new $model());

        $core->instance(
            'Surume\Loop\LoopInterface',
            $loop
        );

        $core->instance(
            'Surume\Loop\LoopExtendedInterface',
            $loop
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Surume\Loop\LoopInterface'
        );

        $core->remove(
            'Surume\Loop\LoopExtendedInterface'
        );
    }
}
