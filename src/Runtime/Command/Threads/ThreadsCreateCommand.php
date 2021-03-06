<?php

namespace Surume\Runtime\Command\Threads;

use Surume\Command\CommandInterface;
use Surume\Runtime\Command\Command;
use Surume\Throwable\Exception\Runtime\Execution\RejectionException;

class ThreadsCreateCommand extends Command implements CommandInterface
{
    /**
     * @param mixed[] $params
     * @return mixed
     * @throws RejectionException
     */
    protected function command($params = [])
    {
        if (!isset($params['definitions']) || !isset($params['flags']))
        {
            throw new RejectionException('Invalid params.');
        }

        return $this->runtime->manager()->createThreads($params['definitions'], (int)$params['flags']);
    }
}
