<?php

namespace Surume\Runtime\Command\Runtimes;

use Surume\Runtime\Command\Command;
use Surume\Command\CommandInterface;
use Surume\Throwable\Exception\Runtime\Execution\RejectionException;

class RuntimesStartCommand extends Command implements CommandInterface
{
    /**
     * @param mixed[] $params
     * @return mixed
     * @throws RejectionException
     */
    protected function command($params = [])
    {
        if (!isset($params['aliases']))
        {
            throw new RejectionException('Invalid params.');
        }

        return $this->runtime->manager()->startRuntimes($params['aliases']);
    }
}
