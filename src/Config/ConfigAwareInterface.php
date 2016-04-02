<?php

namespace Surume\Config;

interface ConfigAwareInterface
{
    /**
     * @param ConfigInterface|null $config
     */
    public function setConfig(ConfigInterface $config = null);

    /**
     * @return ConfigInterface
     */
    public function getConfig();

    /**
     * @return ConfigInterface
     */
    public function config();
}
