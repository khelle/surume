<?php

namespace Surume\Runtime\Provider\Runtime;

use Surume\Core\CoreInterface;
use Surume\Core\Service\ServiceProvider;
use Surume\Core\Service\ServiceProviderInterface;
use Surume\Runtime\RuntimeInterface;

class RuntimeProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $provides = [
        'Surume\Core\CoreInputContextInterface',
        'Surume\Runtime\RuntimeInterface'
    ];

    /**
     * @var RuntimeInterface
     */
    protected $runtime;

    /**
     * @param RuntimeInterface $runtime
     */
    public function __construct(RuntimeInterface $runtime)
    {
        $this->runtime = $runtime;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->runtime);
    }

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $core->instance(
            'Surume\Core\CoreInputContextInterface',
            $this->runtime
        );

        $core->instance(
            'Surume\Runtime\RuntimeInterface',
            $this->runtime
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
            'Surume\Runtime\RuntimeInterface'
        );
    }
}
