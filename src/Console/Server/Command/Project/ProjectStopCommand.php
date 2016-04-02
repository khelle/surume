<?php

namespace Surume\Console\Server\Command\Project;

use Surume\Runtime\Command\Command;
use Surume\Command\CommandInterface;
use Surume\Config\Config;
use Surume\Config\ConfigInterface;
use Surume\Throwable\Exception\Runtime\Execution\RejectionException;

class ProjectStopCommand extends Command implements CommandInterface
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     *
     */
    protected function construct()
    {
        $config = $this->runtime->core()->make('Surume\Config\ConfigInterface');
        $this->config = new Config($config->get('core.project'));
    }

    /**
     *
     */
    protected function destruct()
    {
        unset($this->config);
    }

    /**
     * @param mixed[] $params
     * @return mixed
     * @throws RejectionException
     */
    protected function command($params = [])
    {
        return $this->runtime->manager()
            ->stopProcess(
                $this->config->get('main.alias')
            )
            ->then(
                function() {
                    return 'Project has been stopped.';
                }
            )
        ;
    }
}
