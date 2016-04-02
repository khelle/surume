<?php

namespace Surume\Runtime\Supervisor\Runtime;

use Surume\Runtime\Supervisor\SolverBase;
use Surume\Supervisor\SolverInterface;
use Error;
use Exception;

class RuntimeStop extends SolverBase implements SolverInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'origin'
    ];

    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return mixed
     */
    protected function handler($ex, $params = [])
    {
        return $this->runtime->manager()->stopRuntime($params['origin']);
    }
}
