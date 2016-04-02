<?php

namespace Surume\Runtime\Container;

use Surume\Util\Factory\Factory;
use Surume\Runtime\Container\Manager\ThreadManagerNull;
use ReflectionClass;

class ThreadManagerFactory extends Factory implements ThreadManagerFactoryInterface
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $factory = $this;
        $factory
            ->define('Surume\Runtime\Container\Manager\ThreadManagerBase', function($config) {
                $reflection = (new ReflectionClass('Surume\Runtime\Container\Manager\ThreadManagerBase'));
                return $reflection->newInstanceArgs([
                    $config['runtime'],
                    $config['channel']
                ]);
            })
            ->define('Surume\Runtime\Container\Manager\ThreadManagerRemote', function($config) {
                $reflection = (new ReflectionClass('Surume\Runtime\Container\Manager\ThreadManagerRemote'));
                return $reflection->newInstanceArgs([
                    $config['runtime'],
                    $config['channel'],
                    $config['receiver']
                ]);
            })
            ->define('Surume\Runtime\Container\Manager\ThreadManagerNull', function($config) {
                return new ThreadManagerNull();
            })
        ;
    }
}
