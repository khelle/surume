<?php

namespace Surume\Console\Client\Command\Project;

use Surume\Runtime\Runtime;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Surume\Console\Client\Command\Command;

class ProjectCreateCommand extends Command
{
    /**
     *
     */
    protected function config()
    {
        $this
            ->setName('project:create')
            ->setDescription('Creates project using core.project configuration.')
        ;

        $this->addOption(
            'flags',
            null,
            InputOption::VALUE_OPTIONAL,
            'Force level.',
            Runtime::CREATE_DEFAULT
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed[]
     */
    protected function command(InputInterface $input, OutputInterface $output)
    {
        $flags  = $input->getOption('flags');

        $cmd  = 'project:create';
        $opts = [
            'flags' => $flags
        ];

        return [ null, $cmd, $opts ];
    }
}
