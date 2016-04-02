<?php

namespace Surume\Console\Server\Command\Project;

use Surume\Runtime\Command\Command;
use Surume\Command\CommandInterface;
use Surume\Config\Config;
use Surume\Config\ConfigInterface;
use Surume\Throwable\Exception\Runtime\Execution\RejectionException;

class ProjectCreateCommand extends Command implements CommandInterface
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
        if (!isset($params['flags']))
        {
            throw new RejectionException('Invalid params.');
        }

        return $this->runtime->manager()
            ->createProcess(
                $this->config->get('main.alias'),
                $this->config->get('main.name'),
                $params['flags']
            )
            ->then(
                function() {
                    return 'Project has been created.';
                }
            )
        ;
    }
}
