<?php

namespace Surume\Runtime\Container\Process;

use Surume\Loop\Flow\FlowController;

class ProcessController extends FlowController
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->isRunning = true;
    }
}
