<?php

namespace Surume\Filesystem\Factory;

use Danhunsaker\Flysystem\Redis\RedisAdapter;
use Predis\Client;
use League\Flysystem\AdapterInterface;
use Surume\Filesystem\FilesystemAdapterSimpleFactory;
use Surume\Util\Factory\SimpleFactoryInterface;

class RedisFactory extends FilesystemAdapterSimpleFactory implements SimpleFactoryInterface
{
    /**
     * @return mixed[]
     */
    protected function getDefaults()
    {
        return [];
    }

    /**
     * @param mixed[] $config
     * @return AdapterInterface
     */
    protected function onCreate($config = [])
    {
        $redis = new Client(
            $this->params($config)
        );

        return new RedisAdapter($redis);
    }
}
