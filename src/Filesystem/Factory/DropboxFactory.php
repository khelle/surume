<?php

namespace Surume\Filesystem\Factory;

use Dropbox\Client;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Dropbox\DropboxAdapter;
use Surume\Filesystem\FilesystemAdapterSimpleFactory;
use Surume\Util\Factory\SimpleFactoryInterface;

class DropboxFactory extends FilesystemAdapterSimpleFactory implements SimpleFactoryInterface
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
        $client = new Client(
            $this->param($config, 'accessToken'),
            $this->param($config, 'appSecret')
        );

        return new DropboxAdapter(
            $client,
            $this->param($config, 'prefix')
        );
    }
}
