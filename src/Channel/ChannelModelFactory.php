<?php

namespace Surume\Channel;

use Surume\Channel\Model\Null\NullModel;
use Surume\Channel\Model\Zmq\ZmqDealer;
use Surume\Loop\LoopInterface;
use Surume\Util\Factory\Factory;

class ChannelModelFactory extends Factory implements ChannelModelFactoryInterface
{
    /**
     * @param string $name
     * @param LoopInterface $loop
     */
    public function __construct($name, LoopInterface $loop)
    {
        parent::__construct();

        $factory = $this;
        $factory
            ->bindParam('name', $name)
            ->bindParam('loop', $loop)
            ->define('Surume\Channel\Model\Null\NullModel', function($config) {
                return new NullModel();
            })
            ->define('Surume\Channel\Model\Zmq\ZmqDealer', function($config) use($factory) {
                return new ZmqDealer(
                    isset($config['loop']) ? $config['loop'] : $factory->getParam('loop'),
                    array_merge(
                        [
                            'id'    => isset($config['name']) ? $config['name'] : $factory->getParam('name'),
                            'type'  => ZmqDealer::BINDER,
                            'hosts' => isset($config['name']) ? $config['name'] : $factory->getParam('name')
                        ],
                        $config
                    )
                );
            })
        ;
    }
}
