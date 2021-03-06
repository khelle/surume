<?php

namespace Surume\Filesystem\Factory;

use Aws\S3\S3Client;
use League\Flysystem\AdapterInterface;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use Surume\Filesystem\FilesystemAdapterSimpleFactory;
use Surume\Util\Factory\SimpleFactoryInterface;

class Aws3v3Factory extends FilesystemAdapterSimpleFactory implements SimpleFactoryInterface
{
    /**
     * @return mixed[]
     */
    protected function getDefaults()
    {
        return [
            'bucket'    => '',
            'prefix'    => '',
            'options'   => []
        ];
    }

    /**
     * @param mixed[] $config
     * @return AdapterInterface
     */
    protected function onCreate($config = [])
    {
        return new AwsS3Adapter(
            S3Client::factory($this->params($config)),
            $this->param($config, 'bucket'),
            $this->param($config, 'prefix'),
            $this->param($config, 'options')
        );
    }
}
