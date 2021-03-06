<?php

namespace Surume\Console\Client\Command\Process;

use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Surume\Console\Client\Command\Command;

class ProcessExistsCommand extends Command
{
    /**
     *
     */
    protected function config()
    {
        $this
            ->setName('process:exists')
            ->setDescription('Checks if process with given alias exists in parent scope.')
        ;

        $this->addArgument(
            'parent',
            InputArgument::REQUIRED,
            'Alias of parent runtime.'
        );

        $this->addArgument(
            'alias',
            InputArgument::REQUIRED,
            'Alias of process to check.'
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

        $cmd  = 'process:exists';
        $opts = [
            'alias' => $alias
        ];

        return [ $parent, $cmd, $opts ];
    }

    /**
     * @param mixed $value
     */
    protected function onSuccess($value)
    {
        $value = (bool) $value;

        if ($value)
        {
            echo $this->successMessage("Process exists.");
        }
        else
        {
            echo $this->failureMessage("Process does not exist.");
        }
    }
}
