<?php

namespace Surume\Command;

use Surume\Promise\PromiseInterface;

interface CommandInterface
{
    /**
     * @param mixed[] $params
     * @return PromiseInterface
     */
    public function __invoke($params = []);

    /**
     * @param mixed[] $params
     * @return PromiseInterface
     */
    public function execute($params = []);
}
