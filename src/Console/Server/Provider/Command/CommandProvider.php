<?php

namespace Surume\Console\Server\Provider\Command;

use Surume\Command\CommandInterface;
use Surume\Core\CoreInterface;
use Surume\Core\Service\ServiceProvider;
use Surume\Core\Service\ServiceProviderInterface;
use Surume\Runtime\RuntimeInterface;
use ReflectionClass;

class CommandProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @param CoreInterface $core
     */
    protected function boot(CoreInterface $core)
    {
        $runtime = $core->make('Surume\Runtime\RuntimeInterface');
        $manager = $core->make('Surume\Command\CommandManagerInterface');

        $manager->import(
            $this->commands($runtime)
        );
    }

    /**
     * @param string $class
     * @param mixed[] $params
     * @return CommandInterface
     */
    protected function create($class, $params = [])
    {
        return (new ReflectionClass($class))->newInstanceArgs($params);
    }

    /**
     * @param RuntimeInterface $runtime
     * @return CommandInterface[]
     */
    protected function commands(RuntimeInterface $runtime)
    {
        $cmds = [
            'project:create'    => 'Surume\Console\Server\Command\Project\ProjectCreateCommand',
            'project:destroy'   => 'Surume\Console\Server\Command\Project\ProjectDestroyCommand',
            'project:start'     => 'Surume\Console\Server\Command\Project\ProjectStartCommand',
            'project:stop'      => 'Surume\Console\Server\Command\Project\ProjectStopCommand',
            'project:status'    => 'Surume\Console\Server\Command\Project\ProjectStatusCommand',
        ];

        foreach ($cmds as $key=>$class)
        {
            $cmds[$key] = $this->create($class, [[ 'runtime' => $runtime ]]);
        }

        return $cmds;
    }
}
