<?php

namespace Surume\Runtime\Command\Thread;

use Surume\Runtime\Command\Command;
use Surume\Command\CommandInterface;
use Surume\Throwable\Exception\Runtime\Execution\RejectionException;

class ThreadCreateCommand extends Command implements CommandInterface
{
    /**
     * @param mixed[] $params
     * @return mixed
     * @throws RejectionException
     */
    protected function command($params = [])
    {
        if (!isset($params['alias']) || !isset($params['name']) || !isset($params['flags']))
        {
            throw new RejectionException('Invalid params.');
        }

        return $this->runtime->manager()->createThread($params['alias'], $params['name'], (int)$params['flags']);
    }
}
