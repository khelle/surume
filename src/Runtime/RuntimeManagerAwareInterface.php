<?php

namespace Surume\Runtime;

interface RuntimeManagerAwareInterface
{
    /**
     * @param RuntimeManagerInterface $manager
     */
    public function setRuntimeManager(RuntimeManagerInterface $manager);

    /**
     * @return RuntimeManagerInterface
     */
    public function getRuntimeManager();

    /**
     * @return RuntimeManagerInterface
     */
    public function runtimeManager();
}
