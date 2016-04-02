<?php

namespace Surume\Runtime\Command\Cmd;

use Surume\Runtime\Command\Command;
use Surume\Command\CommandInterface;
use Surume\Supervisor\SupervisorInterface;
use Surume\Throwable\Exception\Runtime\Execution\RejectionException;
use Error;
use Exception;

class CmdErrorCommand extends Command implements CommandInterface
{
    /**
     * @var SupervisorInterface
     */
    protected $manager;

    /**
     *
     */
    protected function construct()
    {
        $this->manager = $this->runtime->core()->make('Surume\Runtime\Supervisor\SupervisorRemoteInterface');
    }

    /**
     *
     */
    protected function destruct()
    {
        unset($this->manager);
    }

    /**
     * @param mixed[] $params
     * @return mixed
     * @throws RejectionException
     */
    protected function command($params = [])
    {
        if (!isset($params['exception']) || !isset($params['message']) || !isset($params['origin']))
        {
            throw new RejectionException('Invalid params.');
        }

        $class = $params['exception'];
        $message = $params['message'];
        $origin = $params['origin'];

        try
        {
            throw new $class($message);
        }
        catch (Error $ex)
        {
            $this->handleException($origin, $ex);
        }
        catch (Exception $ex)
        {
            $this->handleException($origin, $ex);
        }
    }

    /**
     * @param string $origin
     * @param Error|Exception $ex
     */
    protected function handleException($origin, $ex)
    {
        try
        {
            $this->manager->handle($ex, [ 'origin' => $origin ]);
        }
        catch (Error $ex)
        {
            $this->manager->handle($ex, [ 'origin' => $origin ]);
        }
        catch (Exception $ex)
        {
            $this->manager->handle($ex, [ 'origin' => $origin ]);
        }
    }
}
