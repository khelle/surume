<?php

namespace Surume\Console\Server\Command\Project;

use Surume\Channel\ChannelBaseInterface;
use Surume\Channel\Extra\Request;
use Surume\Runtime\Command\Command;
use Surume\Command\CommandInterface;
use Surume\Config\Config;
use Surume\Config\ConfigInterface;
use Surume\Throwable\Exception\Runtime\Execution\RejectionException;
use Surume\Runtime\RuntimeCommand;

class ProjectStatusCommand extends Command implements CommandInterface
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var ChannelBaseInterface
     */
    protected $channel;

    /**
     *
     */
    protected function construct()
    {
        $config = $this->runtime->core()->make('Surume\Config\ConfigInterface');

        $this->channel = $this->runtime->core()->make('Surume\Runtime\Channel\ChannelInterface');
        $this->config = new Config($config->get('core.project'));
    }

    /**
     *
     */
    protected function destruct()
    {
        unset($this->channel);
        unset($this->config);
    }

    /**
     * @param mixed[] $params
     * @return mixed
     * @throws RejectionException
     */
    protected function command($params = [])
    {
        $req = new Request(
            $this->channel,
            $this->config->get('main.alias'),
            new RuntimeCommand('arch:status')
        );

        return $req->call();
    }
}
