<?php

namespace Surume\Runtime\Command;

use Surume\Command\CommandInterface;
use Surume\Runtime\RuntimeInterface;
use Surume\Throwable\Exception\Logic\InstantiationException;

class Command extends \Surume\Command\Command implements CommandInterface
{
    /**
     * @var RuntimeInterface
     */
    protected $runtime;

    /**
     * @param mixed[] $context
     * @throws InstantiationException
     */
    public function __construct($context = [])
    {
        if (!isset($context['runtime']))
        {
            throw new InstantiationException('One of the parameters has not been passed to Command.');
        }

        $this->runtime = $context['runtime'];

        parent::__construct($context);
    }

    /**
     *
     */
    public function __destruct()
    {
        parent::__destruct();

        unset($this->runtime);
    }
}
