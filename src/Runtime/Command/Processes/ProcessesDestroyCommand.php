<?php

namespace Surume\Runtime\Command\Processes;

use Surume\Runtime\Command\Command;
use Surume\Command\CommandInterface;
use Surume\Throwable\Exception\Runtime\Execution\RejectionException;

class ProcessesDestroyCommand extends Command implements CommandInterface
{
    /**
     * @param mixed[] $params
     * @return mixed
     * @throws RejectionException
     */
    protected function command($params = [])
    {
        if (!isset($params['aliases']) || !isset($params['flags']))
        {
            throw new RejectionException('Invalid params.');
        }

        return $this->runtime->manager()->destroyProcesses($params['aliases'], (int)$params['flags']);
    }
}
