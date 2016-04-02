<?php

namespace Surume\Filesystem;

use Surume\Util\Factory\Factory;
use Surume\Util\Factory\SimpleFactoryInterface;

class FilesystemAdapterFactory extends Factory implements FilesystemAdapterFactoryInterface
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $adapters = [
            'Local'     => 'Surume\Filesystem\Factory\LocalFactory',
            'Ftp'       => 'Surume\Filesystem\Factory\FtpFactory',
            'Ftpd'      => 'Surume\Filesystem\Factory\FtpdFactory',
            'Null'      => 'Surume\Filesystem\Factory\NullFactory',
            'AwsS3v2'   => 'Surume\Filesystem\Factory\Aws3v2Factory',
            'AwsS3v3'   => 'Surume\Filesystem\Factory\Aws3v3Factory',
            'Rackspace' => 'Surume\Filesystem\Factory\RackspaceFactory',
            'Dropbox'   => 'Surume\Filesystem\Factory\DropboxFactory',
            'Copy'      => 'Surume\Filesystem\Factory\CopyFactory',
            'Sftp'      => 'Surume\Filesystem\Factory\SftpFactory',
            'Zip'       => 'Surume\Filesystem\Factory\ZipFactory',
            'WebDAV'    => 'Surume\Filesystem\Factory\WebDavFactory',
            'Redis'     => 'Surume\Filesystem\Factory\RedisFactory',
            'Memory'    => 'Surume\Filesystem\Factory\MemoryFactory'
        ];

        foreach ($adapters as $name=>$adapter)
        {
            $this->registerAdapter($name, $adapter);
        }
    }

    /**
     * @param string $name
     * @param string|SimpleFactoryInterface $classOrFactory
     */
    protected function registerAdapter($name, $classOrFactory)
    {
        $this
            ->define($name, function($config) use($classOrFactory) {
                if (is_object($classOrFactory))
                {
                    return $classOrFactory->create([ $config ]);
                }
                else
                {
                    return (new $classOrFactory())->create([ $config ]);
                }
            })
        ;
    }
}
