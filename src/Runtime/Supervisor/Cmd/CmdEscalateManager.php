<?php

namespace Surume\Runtime\Supervisor\Cmd;

use Surume\Channel\ChannelBaseInterface;
use Surume\Channel\Extra\Request;
use Surume\Runtime\Supervisor\SolverBase;
use Surume\Supervisor\SolverInterface;
use Surume\Runtime\RuntimeCommand;
use Error;
use Exception;

class CmdEscalateManager extends SolverBase implements SolverInterface
{
    /**
     * @var ChannelBaseInterface
     */
    protected $channel;

    /**
     * @var string
     */
    protected $parent;

    /**
     *
     */
    protected function construct()
    {
        $this->channel = $this->runtime->core()->make('Surume\Runtime\Channel\ChannelInterface');
        $this->parent  = $this->runtime->parent();
    }

    /**
     *
     */
    protected function destruct()
    {
        unset($this->channel);
        unset($this->parent);
    }

    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return mixed
     */
    protected function handler($ex, $params = [])
    {
        $req = new Request(
            $this->channel,
            $this->parent,
            new RuntimeCommand('cmd:error', [ 'exception' => get_class($ex), 'message' => $ex->getMessage() ])
        );

        return $req->call();
    }
}
