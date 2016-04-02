<?php

namespace Surume\Filesystem;

use League\Flysystem\AdapterInterface;
use Surume\Util\Factory\SimpleFactory;
use Surume\Util\Factory\SimpleFactoryInterface;

abstract class FilesystemAdapterSimpleFactory extends SimpleFactory implements SimpleFactoryInterface
{
    /**
     * @var mixed[]
     */
    protected $defaults;

    /**
     * @param mixed[] $defaults
     */
    public function __construct($defaults = [])
    {
        parent::__construct();

        $this->defaults = array_merge(
            $this->getDefaults(),
            $defaults
        );

        $this->define([ $this, 'onCreate' ]);
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->defaults);

        parent::__destruct();
    }

    /**
     * @return mixed[]
     */
    abstract protected function getDefaults();

    /**
     * @param mixed[] $config
     * @return AdapterInterface
     */
    abstract protected function onCreate($config = []);

    /**
     * @param mixed[] $local
     * @param string $name
     * @return mixed|null
     */
    protected function param($local = [], $name)
    {
        return isset($local[$name]) ? $local[$name] : (isset($this->defaults[$name]) ? $this->defaults[$name] : null);
    }

    /**
     * @param mixed[] $local
     * @return mixed[]
     */
    protected function params($local = [])
    {
        return array_merge($this->defaults, $local);
    }
}
