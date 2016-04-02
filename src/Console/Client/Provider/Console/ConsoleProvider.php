<?php

namespace Surume\Console\Client\Provider\Console;

use Surume\Console\Client\ConsoleClientInterface;
use Surume\Core\CoreInterface;
use Surume\Core\Service\ServiceProvider;
use Surume\Core\Service\ServiceProviderInterface;

class ConsoleProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $provides = [
        'Surume\Core\CoreInputContextInterface',
        'Surume\Console\Client\ConsoleClientInterface'
    ];

    /**
     * @var ConsoleClientInterface
     */
    protected $console;

    /**
     * @param ConsoleClientInterface $console
     */
    public function __construct(ConsoleClientInterface $console)
    {
        $this->console = $console;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->console);
    }

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $core->instance(
            'Surume\Core\CoreInputContextInterface',
            $this->console
        );

        $core->instance(
            'Surume\Console\Client\ConsoleClientInterface',
            $this->console
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Surume\Core\CoreInputContextInterface'
        );

        $core->remove(
            'Surume\Console\Client\ConsoleClientInterface'
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function boot(CoreInterface $core)
    {
        $console = $this->console;
        $loop    = $core->make('Surume\Loop\LoopExtendedInterface');

        $console->setLoop($loop);
    }
}
