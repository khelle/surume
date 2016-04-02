<?php

namespace Surume\Console\Client\Command;

use Surume\Promise\PromiseInterface;

interface CommandHandlerInterface
{
    /**
     * @param string|null $commandParent
     * @param string $commandName
     * @param string[] $commandParams
     * @return PromiseInterface
     */
    public function handle($commandParent, $commandName, $commandParams = []);
}
