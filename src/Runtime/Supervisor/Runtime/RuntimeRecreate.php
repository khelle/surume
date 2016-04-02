<?php

namespace Surume\Runtime\Supervisor\Runtime;

use Surume\Promise\Promise;
use Surume\Runtime\Supervisor\SolverBase;
use Surume\Supervisor\SolverInterface;
use Surume\Throwable\Exception\Runtime\Execution\RejectionException;
use Surume\Runtime\Runtime;
use Error;
use Exception;

class RuntimeRecreate extends SolverBase implements SolverInterface
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
        $manager = $this->runtime->manager();
        $alias = $params['origin'];

        if ($manager->existsThread($alias))
        {
            return $manager->createThread($alias, null, Runtime::CREATE_FORCE);
        }
        else if ($manager->existsProcess($alias))
        {
            return $manager->createProcess($alias, null, Runtime::CREATE_FORCE);
        }

        return Promise::doReject(new RejectionException("Runtime [$alias] does not exists."));
    }
}
