<?php

namespace Surume\Core;

use Surume\Container\Container;
use Surume\Core\Service\ServiceRegisterInterface;
use Surume\Core\Service\ServiceRegister;
use Surume\Core\Service\ServiceProviderInterface;
use Surume\Throwable\Exception\Runtime\ExecutionException;
use Surume\Throwable\Exception\Logic\IllegalCallException;
use Surume\Throwable\Exception\Logic\InstantiationException;
use Surume\Throwable\Exception\Runtime\Io\IoWriteException;
use Surume\Runtime\Runtime;
use Error;
use Exception;

class Core extends Container implements CoreInterface
{
    /**
     * @var string
     */
    const VERSION = '0.2.2';

    /**
     * @var string
     */
    const RUNTIME_UNIT = Runtime::UNIT_UNDEFINED;

    /**
     * @var string
     */
    protected $dataPath;

    /**
     * @var string[][]
     */
    protected $bootConfig;

    /**
     * @var ServiceRegisterInterface
     */
    protected $serviceRegister;

    /**
     * @param string $dataPath
     * @throws InstantiationException
     */
    public function __construct($dataPath = null)
    {
        parent::__construct();

        $this->dataPath = realpath($dataPath);
        $this->bootConfig = [];
        $this->serviceRegister = new ServiceRegister($this);

        try
        {
            $this->registerDefaultProviders();
            $this->registerDefaultAliases();
        }
        catch (Error $ex)
        {
            throw new InstantiationException("Core module could not be initalized.", $ex);
        }
        catch (Exception $ex)
        {
            throw new InstantiationException("Core module could not be initalized.", $ex);
        }
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->dataPath);
        unset($this->bootConfig);
        unset($this->serviceRegister);
    }

    /**
     * @return CoreInterface
     * @throws InstantiationException
     */
    public function boot()
    {
        try
        {
            $this->bootProviders();

            return $this;
        }
        catch (Error $ex)
        {
            throw new InstantiationException("Core module could not be booted.", $ex);
        }
        catch (Exception $ex)
        {
            throw new InstantiationException("Core module could not be booted.", $ex);
        }
    }

    /**
     * @param string[][] $config
     * @return string[][]
     */
    public function config($config = null)
    {
        if ($config !== null)
        {
            $this->bootConfig = array_merge($this->bootConfig, $config);
        }

        return $this->bootConfig;
    }

    /**
     * @return string
     */
    public function version()
    {
        return static::VERSION;
    }

    /**
     * @return string
     */
    public function unit()
    {
        return static::RUNTIME_UNIT;
    }

    /*
     * @return string
     */
    public function basePath()
    {
        return dirname($this->dataPath);
    }

    /**
     * @return string
     */
    public function dataPath()
    {
        return $this->dataPath;
    }

    /**
     * @return string
     */
    public function dataDir()
    {
        return str_replace($this->basePath(), '', $this->dataPath());
    }

    /**
     * @param ServiceProviderInterface[]|string[] $providers
     * @param bool $force
     * @throws ExecutionException
     */
    public function registerProviders($providers, $force = false)
    {
        foreach ($providers as $provider)
        {
            $this->registerProvider($provider, $force);
        }
    }

    /**
     * @param ServiceProviderInterface|string $provider
     * @param bool $force
     * @throws ExecutionException
     */
    public function registerProvider($provider, $force = false)
    {
        try
        {
            $this->serviceRegister->registerProvider($provider, $force);
        }
        catch (Error $ex)
        {
            throw new ExecutionException("Provider could not be registered.", $ex);
        }
        catch (Exception $ex)
        {
            throw new ExecutionException("Provider could not be registered.", $ex);
        }
    }

    /**
     * @param ServiceProviderInterface|string $provider
     * @throws ExecutionException
     */
    public function unregisterProvider($provider)
    {
        try
        {
            $this->serviceRegister->unregisterProvider($provider);
        }
        catch (Error $ex)
        {
            throw new ExecutionException("Provider could not be unregistered.", $ex);
        }
        catch (Exception $ex)
        {
            throw new ExecutionException("Provider could not be unregistered.", $ex);
        }
    }

    /**
     * @param ServiceProviderInterface|string $provider
     * @return ServiceProviderInterface|null
     */
    public function getProvider($provider)
    {
        return $this->serviceRegister->getProvider($provider);
    }

    /**
     * @return string[]
     */
    public function getProviders()
    {
        return $this->serviceRegister->getProviders();
    }

    /**
     * @return string[]
     */
    public function getServices()
    {
        return $this->serviceRegister->getServices();
    }

    /**
     * @throws IllegalCallException
     */
    public function flushProviders()
    {
        $this->serviceRegister->flushProviders();
    }

    /**
     * @param string[] $interfaces
     */
    public function registerAliases($interfaces)
    {
        foreach ($interfaces as $alias=>$interface)
        {
            $this->registerAlias($alias, $interface);
        }
    }

    /**
     * @param string $alias
     * @param string $interface
     */
    public function registerAlias($alias, $interface)
    {
        $this->serviceRegister->registerAlias($alias, $interface);
    }

    /**
     * @param string $alias
     */
    public function unregisterAlias($alias)
    {
        $this->serviceRegister->unregisterAlias($alias);
    }

    /**
     * @param string $alias
     * @return string
     */
    public function getAlias($alias)
    {
        return $this->serviceRegister->getAlias($alias);
    }

    /**
     * @return string[]
     */
    public function getAliases()
    {
        return $this->serviceRegister->getAliases();
    }

    /**
     * @throws IllegalCallException
     */
    public function flushAliases()
    {
        $this->serviceRegister->flushAliases();
    }

    /**
     * @return string[]
     */
    protected function defaultProviders()
    {
        return [];
    }

    /**
     * @return string[]
     */
    protected function defaultAliases()
    {
        return [];
    }

    /**
     * @throws ExecutionException
     */
    protected function registerDefaultProviders()
    {
        $this->registerProviders($this->defaultProviders());
    }

    /**
     * @throws IoWriteException
     */
    protected function registerDefaultAliases()
    {
        $this->registerAliases($this->defaultAliases());
    }

    /**
     * @throws ExecutionException
     */
    protected function bootProviders()
    {
        $this->serviceRegister->boot();
    }
}
