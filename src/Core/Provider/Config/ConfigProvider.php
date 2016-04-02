<?php

namespace Surume\Core\Provider\Config;

use Surume\Config\Config;
use Surume\Config\ConfigFactory;
use Surume\Config\ConfigInterface;
use Surume\Core\CoreInterface;
use Surume\Core\CoreInputContextInterface;
use Surume\Core\Service\ServiceProvider;
use Surume\Core\Service\ServiceProviderInterface;
use Surume\Filesystem\Filesystem;
use Surume\Filesystem\FilesystemAdapterFactory;
use Surume\Support\ArraySupport;
use Surume\Support\StringSupport;

class ConfigProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Surume\Core\CoreInputContextInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Surume\Config\ConfigInterface'
    ];

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $context = $core->make('Surume\Core\CoreInputContextInterface');

        $global = $core->dataPath() . '/config-global/' . $this->getDir($core->unit());
        $local  = $core->dataPath() . '/config/' . $context->name();

        $config = new Config();
        $this->addConfigByPath($config, $global);
        $this->addConfigByPath($config, $local);
        $this->addConfig($config, new Config($core->config()));

        $vars = array_merge(
            $config->exists('vars') ? $config->get('vars') : [],
            $this->getDefaultVariables($core, $context)
        );

        $records = ArraySupport::flatten($config->all());
        foreach ($records as $key=>$value)
        {
            $new = StringSupport::parametrize($value, $vars);
            if (is_string($value) && $new != $value)
            {
                $config->set($key, $new);
            }
        }

        $core->instance(
            'Surume\Config\ConfigInterface',
            $config
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Surume\Config\ConfigInterface'
        );
    }

    /**
     * @param string $path
     * @return ConfigInterface
     */
    private function createConfig($path)
    {
        if (!is_dir($path))
        {
            return new Config();
        }

        $factory = new FilesystemAdapterFactory();

        return (new ConfigFactory(
            new Filesystem(
                $factory->create('Local', [ [ 'path' => $path ] ])
            )
        ))->create();
    }

    /**
     * @param string $runtimeUnit
     * @return string
     */
    private function getDir($runtimeUnit)
    {
        return $runtimeUnit;
    }

    /**
     * @param ConfigInterface $config
     * @param string $option
     * @return callable
     */
    private function getOverwriteHandler(ConfigInterface $config, $option)
    {
        switch ($option)
        {
            case 'isolate':     return [ $config, 'getOverwriteHandlerIsolater' ];
            case 'replace':     return [ $config, 'getOverwriteHandlerReplacer' ];
            case 'merge':       return [ $config, 'getOverwriteHandlerMerger' ];
            default:            return [ $config, 'getOverwriteHandlerMerger' ];
        }
    }

    /**
     * @param ConfigInterface $config
     * @param string $path
     */
    private function addConfigByPath(ConfigInterface $config, $path)
    {
        $this->addConfig($config, $this->createConfig($path));
    }

    /**
     * @param ConfigInterface $config
     * @param ConfigInterface $current
     */
    private function addConfig(ConfigInterface $config, ConfigInterface $current)
    {
        $dirs = (array) $current->get('config.dirs');
        foreach ($dirs as $dir)
        {
            $this->addConfigByPath($current, $dir);
        }

        if ($current->exists('config.mode'))
        {
            $config->setOverwriteHandler(
                $this->getOverwriteHandler($config, $current->get('config.mode'))
            );
        }

        $config->merge($current->all());
    }

    /**
     * @param CoreInterface $core
     * @param CoreInputContextInterface $context
     * @return string[]
     */
    private function getDefaultVariables(CoreInterface $core, CoreInputContextInterface $context)
    {
        return [
            'runtime'   => $context->type(),
            'parent'    => $context->parent(),
            'alias'     => $context->alias(),
            'name'      => $context->name(),
            'basepath'  => $core->basePath(),
            'datapath'  => $core->dataPath(),
            'host.main' => '127.0.0.1'
        ];
    }
}
