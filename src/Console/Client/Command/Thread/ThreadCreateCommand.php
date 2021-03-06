<?php

namespace Surume\Console\Client\Command\Thread;

use Surume\Runtime\Runtime;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Surume\Console\Client\Command\Command;

class ThreadCreateCommand extends Command
{
    /**
     *
     */
    protected function config()
    {
        $this
            ->setName('thread:create')
            ->setDescription('Creates thread name with given alias in parent scope.')
        ;

        $this->addArgument(
            'parent',
            InputArgument::REQUIRED,
            'Alias of parent runtime.'
        );

        $this->addArgument(
            'alias',
            InputArgument::REQUIRED,
            'Alias of new thread.'
        );

        $this->addArgument(
            'name',
            InputArgument::REQUIRED,
            'Name of new thread.'
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

        $cmd  = 'thread:create';
        $opts = [
            'alias' => $alias,
            'name'  => $name,
            'flags' => $flags
        ];

        return [ $parent, $cmd, $opts ];
    }
}
