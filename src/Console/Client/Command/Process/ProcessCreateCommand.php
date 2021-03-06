<?php

namespace Surume\Console\Client\Command\Process;

use Surume\Runtime\Runtime;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Surume\Console\Client\Command\Command;

class ProcessCreateCommand extends Command
{
    /**
     *
     */
    protected function config()
    {
        $this
            ->setName('process:create')
            ->setDescription('Creates process name with given alias in parent scope.')
        ;

        $this->addArgument(
            'parent',
            InputArgument::REQUIRED,
            'Alias of parent runtime.'
        );

        $this->addArgument(
            'alias',
            InputArgument::REQUIRED,
            'Alias of new process.'
        );

        $this->addArgument(
            'name',
            InputArgument::REQUIRED,
            'Name of new process.'
        );

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
        $parent = $input->getArgument('parent');
        $alias  = $input->getArgument('alias');
        $name   = $input->getArgument('name');
        $flags  = $input->getOption('flags');

        $cmd  = 'process:create';
        $opts = [
            'alias' => $alias,
            'name'  => $name,
            'flags' => $flags
        ];

        return [ $parent, $cmd, $opts ];
    }
}
