<?php

namespace Surume\Runtime\Container;

use Surume\Util\Factory\Factory;
use Surume\Runtime\Container\Manager\ProcessManagerNull;
use ReflectionClass;

class ProcessManagerFactory extends Factory implements ProcessManagerFactoryInterface
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $factory = $this;
        $factory
            ->define('Surume\Runtime\Container\Manager\ProcessManagerBase', function($config) {
                $reflection = (new ReflectionClass('Surume\Runtime\Container\Manager\ProcessManagerBase'));
                return $reflection->newInstanceArgs([
                    $config['runtime'],
                    $config['channel'],
                    $config['env'],
                    $config['system'],
                    $config['filesystem']
                ]);
            })
            ->define('Surume\Runtime\Container\Manager\ProcessManagerRemote', function($config) {
                $reflection = (new ReflectionClass('Surume\Runtime\Container\Manager\ProcessManagerRemote'));
                return $reflection->newInstanceArgs([
                    $config['runtime'],
                    $config['channel'],
                    $config['receiver']
                ]);
            })
            ->define('Surume\Runtime\Container\Manager\ProcessManagerNull', function($config) {
                return new ProcessManagerNull();
            })
        ;
    }
}
