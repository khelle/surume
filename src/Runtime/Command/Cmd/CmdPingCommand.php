<?php

namespace Surume\Runtime\Command\Cmd;

use Surume\Runtime\Command\Command;
use Surume\Command\CommandInterface;

class CmdPingCommand extends Command implements CommandInterface
{
    /**
     * @param mixed[] $params
     * @return mixed
     */
    protected function command($params = [])
    {
        return 'ping';
    }
}
