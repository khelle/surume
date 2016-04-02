<?php

namespace Surume\Runtime\Command\Container;

use Surume\Runtime\Command\Command;
use Surume\Command\CommandInterface;

class ContainerStopCommand extends Command implements CommandInterface
{
    /**
     * @param mixed[] $params
     * @return mixed
     */
    protected function command($params = [])
    {
        return $this->runtime->stop();
    }
}
