<?php

namespace Surume\Runtime\Supervisor\Cmd;

use Surume\Runtime\Supervisor\SolverBase;
use Surume\Supervisor\SolverInterface;
use Surume\Log\Logger;
use Surume\Log\LoggerInterface;
use Error;
use Exception;

class CmdLog extends SolverBase implements SolverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     *
     */
    protected function construct()
    {
        if (!isset($this->context['level']))
        {
            $this->context['level'] = Logger::EMERGENCY;
        }

        $this->logger = $this->runtime->core()->make('Surume\Log\LoggerInterface');
    }

    /**
     *
     */
    protected function destruct()
    {
        unset($this->logger);
    }

    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return mixed
     */
    protected function handler($ex, $params = [])
    {
        $this->logger->log(
            $this->context['level'], \Surume\Throwable\Exception::toString($ex)
        );
    }
}
