<?php

namespace Surume\Loop;

use Surume\Loop\Model\ExtEventLoop;
use Surume\Loop\Model\LibEventLoop;
use Surume\Loop\Model\LibEvLoop;
use Surume\Loop\Model\StreamSelectLoop;
use Surume\Util\Factory\Factory;
use Surume\Util\Factory\FactoryInterface;

class LoopFactory extends Factory implements FactoryInterface
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $factory = $this;
        $factory
            ->define('LibEventLoop', function() {
                return new LibEventLoop();
            })
            ->define('LibEvLoop', function() {
                return new LibEvLoop();
            })
            ->define('ExtEventLoop', function() {
                return new ExtEventLoop();
            })
            ->define('StreamSelectLoop', function() {
                return new StreamSelectLoop();
            })
        ;
    }
}
