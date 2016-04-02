<?php

namespace Surume\Runtime\Supervisor;

use Surume\Runtime\RuntimeInterface;
use Surume\Supervisor\SolverFactoryInterface;
use Surume\Util\Factory\Factory;

class SolverFactory extends Factory implements SolverFactoryInterface
{
    /**
     * @var RuntimeInterface
     */
    protected $runtime;

    /**
     * @param RuntimeInterface $runtime
     */
    public function __construct(RuntimeInterface $runtime)
    {
        parent::__construct();

        $this->runtime = $runtime;

        $handlers = [
            'CmdDoNothing'          => 'Surume\Runtime\Supervisor\Cmd\CmdDoNothing',
            'CmdEscalateManager'    => 'Surume\Runtime\Supervisor\Cmd\CmdEscalateManager',
            'CmdEscalateSupervisor' => 'Surume\Runtime\Supervisor\Cmd\CmdEscalateSupervisor',
            'CmdLog'                => 'Surume\Runtime\Supervisor\Cmd\CmdLog',
            'RuntimeContinue'       => 'Surume\Runtime\Supervisor\Runtime\RuntimeContinue',
            'RuntimeDestroy'        => 'Surume\Runtime\Supervisor\Runtime\RuntimeDestroy',
            'RuntimeDestroySoft'    => 'Surume\Runtime\Supervisor\Runtime\RuntimeDestroySoft',
            'RuntimeDestroyHard'    => 'Surume\Runtime\Supervisor\Runtime\RuntimeDestroyHard',
            'RuntimeRecreate'       => 'Surume\Runtime\Supervisor\Runtime\RuntimeRecreate',
            'RuntimeStart'          => 'Surume\Runtime\Supervisor\Runtime\RuntimeStart',
            'RuntimeStop'           => 'Surume\Runtime\Supervisor\Runtime\RuntimeStop',
            'ContainerContinue'     => 'Surume\Runtime\Supervisor\Container\ContainerContinue',
            'ContainerDestroy'      => 'Surume\Runtime\Supervisor\Container\ContainerDestroy',
            'ContainerStart'        => 'Surume\Runtime\Supervisor\Container\ContainerStart',
            'ContainerStop'         => 'Surume\Runtime\Supervisor\Container\ContainerStop'
        ];

        foreach ($handlers as $handlerName=>$handlerClass)
        {
            $this->registerHandler($handlerName, $handlerClass);
        }
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->runtime);
    }

    /**
     * @param string $name
     */
    private function registerHandler($name, $class)
    {
        $runtime = $this->runtime;
        $this
            ->define($name, function($context = []) use($class, $runtime) {
                return new $class(array_merge(
                    [ 'runtime' => $runtime ],
                    $context
                ));
            })
            ->define($class, function($context = []) use($class, $runtime) {
                return new $class(array_merge(
                    [ 'runtime' => $runtime ],
                    $context
                ));
            })
        ;
    }
}
