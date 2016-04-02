<?php

namespace Surume\Runtime\Provider\Runtime;

use Surume\Core\CoreInterface;
use Surume\Core\Service\ServiceProvider;
use Surume\Core\Service\ServiceProviderInterface;

class RuntimeAutowireProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Surume\Loop\LoopExtendedInterface',
        'Surume\Runtime\RuntimeInterface',
        'Surume\Runtime\Supervisor\SupervisorBaseInterface',
        'Surume\Runtime\RuntimeManagerInterface'
    ];

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $loop    = $core->make('Surume\Loop\LoopExtendedInterface');
        $runtime = $core->make('Surume\Runtime\RuntimeInterface');
        $error   = $core->make('Surume\Runtime\Supervisor\SupervisorBaseInterface');
        $manager = $core->make('Surume\Runtime\RuntimeManagerInterface');

        $model = $runtime->model();
        $model->setLoop($loop);
        $model->setSupervisor($error);
        $model->setRuntimeManager($manager);
    }
}
