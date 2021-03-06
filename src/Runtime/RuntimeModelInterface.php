<?php

namespace Surume\Runtime;

use Error;
use Exception;
use Surume\Core\CoreAwareInterface;
use Surume\Core\CoreInputContextInterface;
use Surume\Supervisor\SupervisorAwareInterface;
use Surume\Event\EventEmitterAwareInterface;
use Surume\Loop\LoopExtendedAwareInterface;
use Surume\Promise\PromiseInterface;

interface RuntimeModelInterface extends
    CoreAwareInterface,
    CoreInputContextInterface,
    SupervisorAwareInterface,
    EventEmitterAwareInterface,
    LoopExtendedAwareInterface,
    RuntimeManagerAwareInterface
{
    /**
     * @param int $state
     */
    public function setState($state);

    /**
     * @return int
     */
    public function getState();

    /**
     * @return int
     */
    public function state();

    /**
     * @param int $state
     * @return bool
     */
    public function isState($state);

    /**
     * @return bool
     */
    public function isCreated();

    /**
     * @return bool
     */
    public function isDestroyed();

    /**
     * @return bool
     */
    public function isStarted();

    /**
     * @return bool
     */
    public function isStopped();

    /**
     * @return PromiseInterface
     */
    public function create();

    /**
     * @return PromiseInterface
     */
    public function destroy();

    /**
     * @return PromiseInterface
     */
    public function start();

    /**
     * @return PromiseInterface
     */
    public function stop();

    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @throws Exception
     */
    public function fail($ex, $params = []);

    /**
     *
     */
    public function succeed();
}
