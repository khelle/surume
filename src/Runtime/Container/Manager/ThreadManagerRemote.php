<?php

namespace Surume\Runtime\Container\Manager;

use Surume\Throwable\Exception\Runtime\Execution\RejectionException;
use Surume\Promise\Promise;
use Surume\Promise\PromiseInterface;
use Surume\Channel\ChannelBaseInterface;
use Surume\Channel\Extra\Request;
use Surume\Runtime\Runtime;
use Surume\Runtime\RuntimeCommand;
use Surume\Runtime\RuntimeInterface;
use Surume\Runtime\Container\ThreadManagerInterface;

class ThreadManagerRemote implements ThreadManagerInterface
{
    /**
     * @var RuntimeInterface
     */
    protected $runtime;

    /**
     * @var ChannelBaseInterface
     */
    protected $channel;

    /**
     * @var string
     */
    protected $receiver;

    /**
     * @param RuntimeInterface $runtime
     * @param ChannelBaseInterface $channel
     */
    public function __construct(RuntimeInterface $runtime, ChannelBaseInterface $channel, $receiver = null)
    {
        $this->runtime = $runtime;
        $this->channel = $channel;
        $this->receiver = ($receiver !== null) ? $receiver : $runtime->parent();
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->runtime);
        unset($this->channel);
        unset($this->receiver);
    }

    /**
     * @param string $alias
     * @return bool
     */
    public function existsThread($alias)
    {
        return false;
    }

    /**
     * @param string $alias
     * @param string|null $name
     * @param int $flags
     * @return PromiseInterface
     */
    public function createThread($alias, $name, $flags = Runtime::CREATE_DEFAULT)
    {
        $req = new Request(
            $this->channel,
            $this->receiver,
            new RuntimeCommand('thread:create', [ 'alias' => $alias, 'name' => $name, 'flags' => $flags ])
        );

        return $req->call();
    }

    /**
     * @param string $alias
     * @param int $flags
     * @return PromiseInterface
     */
    public function destroyThread($alias, $flags = Runtime::DESTROY_FORCE_SOFT)
    {
        $req = new Request(
            $this->channel,
            $this->receiver,
            new RuntimeCommand('thread:destroy', [ 'alias' => $alias, 'flags' => $flags ])
        );

        return $req->call();
    }

    /**
     * @param string $alias
     * @return PromiseInterface
     */
    public function startThread($alias)
    {
        $req = new Request(
            $this->channel,
            $this->receiver,
            new RuntimeCommand('thread:start', [ 'alias' => $alias ])
        );

        return $req->call();
    }

    /**
     * @param string $alias
     * @return PromiseInterface
     */
    public function stopThread($alias)
    {
        $req = new Request(
            $this->channel,
            $this->receiver,
            new RuntimeCommand('thread:stop', [ 'alias' => $alias ])
        );

        return $req->call();
    }

    /**
     * @param string[][] $definitions
     * @param int $flags
     * @return PromiseInterface
     */
    public function createThreads($definitions, $flags = Runtime::CREATE_DEFAULT)
    {
        $req = new Request(
            $this->channel,
            $this->receiver,
            new RuntimeCommand('threads:create', [ 'definitions' => $definitions, 'flags' => $flags ])
        );

        return $req->call();
    }

    /**
     * @param string[] $aliases
     * @param int $flags
     * @return PromiseInterface
     */
    public function destroyThreads($aliases, $flags = Runtime::DESTROY_FORCE_SOFT)
    {
        $req = new Request(
            $this->channel,
            $this->receiver,
            new RuntimeCommand('threads:destroy', [ 'aliases' => $aliases, 'flags' => $flags ])
        );

        return $req->call();
    }

    /**
     * @param $aliases
     * @return PromiseInterface
     */
    public function startThreads($aliases)
    {
        $req = new Request(
            $this->channel,
            $this->receiver,
            new RuntimeCommand('threads:start', [ 'aliases' => $aliases ])
        );

        return $req->call();
    }

    /**
     * @param $aliases
     * @return PromiseInterface
     */
    public function stopThreads($aliases)
    {
        $req = new Request(
            $this->channel,
            $this->receiver,
            new RuntimeCommand('threads:stop', [ 'aliases' => $aliases ])
        );

        return $req->call();
    }

    /**
     * @return PromiseInterface
     */
    public function getThreads()
    {
        $req = new Request(
            $this->channel,
            $this->receiver,
            new RuntimeCommand('threads:get')
        );

        return $req->call();
    }

    /**
     * @param int $flags
     * @return PromiseInterface
     */
    public function flushThreads($flags = Runtime::DESTROY_KEEP)
    {
        return Promise::doReject(new RejectionException('Threads storage cannot be flushed.'));
    }
}
