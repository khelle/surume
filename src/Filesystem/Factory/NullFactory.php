<?php

namespace Surume\Filesystem\Factory;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Adapter\NullAdapter;
use Surume\Filesystem\FilesystemAdapterSimpleFactory;
use Surume\Util\Factory\SimpleFactoryInterface;

class NullFactory extends FilesystemAdapterSimpleFactory implements SimpleFactoryInterface
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
        return new NullAdapter();
    }
}
