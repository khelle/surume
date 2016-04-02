<?php

namespace Surume\Runtime\Supervisor;

use Surume\Supervisor\SolverInterface;
use Surume\Throwable\Exception\Logic\InstantiationException;
use Surume\Runtime\RuntimeInterface;

class SolverBase extends \Surume\Supervisor\SolverBase implements SolverInterface
{
    /**
     * @var RuntimeInterface
     */
    protected $runtime;

    /**
     * @param mixed[] $context
     * @throws InstantiationException
     */
    public function __construct($context = [])
    {
        if (!isset($context['runtime']))
        {
            throw new InstantiationException('[' . __CLASS__ . '] could not been initialized.');
        }

        $this->runtime = $context['runtime'];
        unset($context['runtime']);

        parent::__construct($context);
    }

    /**
     *
     */
    public function __destruct()
    {
        parent::__destruct();

        unset($this->runtime);
    }
}
