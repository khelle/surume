<?php

namespace Surume\Runtime\Command\Runtime;

use Surume\Runtime\Command\Command;
use Surume\Command\CommandInterface;
use Surume\Throwable\Exception\Runtime\Execution\RejectionException;

class RuntimeStartCommand extends Command implements CommandInterface
{
    /**
     * @param mixed[] $params
     * @return mixed
     * @throws RejectionException
     */
    protected function command($params = [])
    {
        if (!isset($params['alias']))
        {
            throw new RejectionException('Invalid params.');
        }

        return $this->runtime->manager()->startRuntime($params['alias']);
    }
}
