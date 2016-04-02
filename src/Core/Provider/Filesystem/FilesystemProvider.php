<?php

namespace Surume\Core\Provider\Filesystem;

use Surume\Core\CoreInterface;
use Surume\Core\Service\ServiceProvider;
use Surume\Core\Service\ServiceProviderInterface;
use Surume\Filesystem\Filesystem;
use Surume\Filesystem\FilesystemAdapterFactory;
use Surume\Filesystem\FilesystemManager;

class FilesystemProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Surume\Config\ConfigInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Surume\Filesystem\FilesystemInterface',
        'Surume\Filesystem\FilesystemManagerInterface'
    ];

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $config = $core->make('Surume\Config\ConfigInterface');

        $factory = new FilesystemAdapterFactory();
        $fsCloud = new FilesystemManager();
        $fsDisk  = new Filesystem(
            $factory->create('Local', [ [ 'path' => $core->basePath() ] ])
        );

        $disks = $config->get('filesystem.cloud');

        foreach ($disks as $disk=>$config)
        {
            $fsCloud->mountFilesystem($disk, new Filesystem(
                $factory->create(
                    $config['factory'],
                    [ $config['config'] ]
                )
            ));
        }

        $core->instance(
            'Surume\Filesystem\FilesystemInterface',
            $fsDisk
        );

        $core->instance(
            'Surume\Filesystem\FilesystemManagerInterface',
            $fsCloud
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Surume\Filesystem\FilesystemInterface'
        );

        $core->remove(
            'Surume\Filesystem\FilesystemManagerInterface'
        );
    }
}
