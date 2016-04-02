<?php

namespace Surume\Runtime\Command\Container;

use Surume\Runtime\Command\Command;
use Surume\Command\CommandInterface;

class ContainerStatusCommand extends Command implements CommandInterface
{
    /**
     * @param mixed[] $params
     * @return mixed
     */
    protected function command($params = [])
    {
        $runtime = $this->runtime;

        return [
            'parent' => $runtime->parent(),
            'alias'  => $runtime->alias(),
            'name'   => $runtime->name(),
            'state'  => $runtime->state()
        ];
    }
}
