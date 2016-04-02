<?php

namespace Surume\Runtime\Command\Processes;

use Surume\Runtime\Command\Command;
use Surume\Command\CommandInterface;
use Surume\Throwable\Exception\Runtime\Execution\RejectionException;

class ProcessesGetCommand extends Command implements CommandInterface
{
    /**
     * @param mixed[] $params
     * @return mixed
     * @throws RejectionException
     */
    protected function command($params = [])
    {
        return $this->runtime->manager()->getProcesses();
    }
}
