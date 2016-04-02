<?php

namespace Surume\Core\Provider\Event;

use Surume\Core\CoreInterface;
use Surume\Core\Service\ServiceProvider;
use Surume\Core\Service\ServiceProviderInterface;
use Surume\Event\EventEmitter;

class EventProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Surume\Loop\LoopInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Surume\Event\EventEmitterInterface'
    ];

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $emitter = new EventEmitter(
            $core->make('Surume\Loop\LoopInterface')
        );

        $core->instance(
            'Surume\Event\EventEmitterInterface',
            $emitter
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Surume\Event\EventEmitterInterface'
        );
    }
}
