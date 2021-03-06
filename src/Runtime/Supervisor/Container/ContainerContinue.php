<?php

namespace Surume\Runtime\Supervisor\Container;

use Surume\Runtime\Supervisor\SolverBase;
use Surume\Supervisor\SolverInterface;
use Error;
use Exception;

class ContainerContinue extends SolverBase implements SolverInterface
{
    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return mixed
     */
    protected function handler($ex, $params = [])
    {
        $this->runtime->succeed();
    }
}
