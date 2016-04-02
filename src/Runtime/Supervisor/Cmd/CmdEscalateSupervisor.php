<?php

namespace Surume\Runtime\Supervisor\Cmd;

use Surume\Runtime\Supervisor\SolverBase;
use Surume\Supervisor\SolverInterface;
use Surume\Promise\Promise;
use Error;
use Exception;

class CmdEscalateSupervisor extends SolverBase implements SolverInterface
{
    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return mixed
     */
    protected function handler($ex, $params = [])
    {
        $this->runtime->fail($ex, $params);

        return Promise::doResolve('Runtime has handled failure.');
    }
}
