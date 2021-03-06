<?php

namespace Surume\Channel;

class ChannelRouterHandler
{
    /**
     * @var ChannelRouterBaseInterface
     */
    protected $router;

    /**
     * @var callable
     */
    protected $matcher;

    /**
     * @var callable
     */
    protected $handler;

    /**
     * @var bool
     */
    protected $propagate;

    /**
     * @var int
     */
    protected $limit;

    /**
     * @var array|null
     */
    protected $pointer;

    /**
     * @var bool
     */
    protected $cancelled;

    /**
     * @param ChannelRouterBaseInterface $router
     * @param callable $matcher
     * @param callable $handler
     * @param bool $propagate
     * @param int $limit
     */
    public function __construct(ChannelRouterBaseInterface $router, callable $matcher, callable $handler, $propagate = false, $limit = 0)
    {
        $this->router = $router;
        $this->matcher = $matcher;
        $this->handler = $handler;
        $this->propagate = $propagate;
        $this->limit = $limit;
        $this->pointer = null;
        $this->cancelled = false;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->router);
        unset($this->matcher);
        unset($this->handler);
        unset($this->propagate);
        unset($this->pointer);
        unset($this->cancelled);
    }

    /**
     * @return ChannelRouterBaseInterface
     */
    public function router()
    {
        return $this->router;
    }

    /**
     * @param string $sender
     * @param ChannelProtocolInterface $protocol
     * @return bool
     */
    public function match($sender, ChannelProtocolInterface $protocol)
    {
        $callback = $this->matcher;
        return $callback($sender, $protocol);
    }

    /**
     * @param string $sender
     * @param ChannelProtocolInterface $protocol
     * @param int $flags
     * @param callable|null $success
     * @param callable|null $failure
     * @param callable|null $cancel
     * @param float $timeout
     * @return bool
     */
    public function handle($sender, ChannelProtocolInterface $protocol, $flags, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0)
    {
        $callback = $this->handler;
        $callback($sender, $protocol, $flags, $success, $failure, $cancel, $timeout);

        if ($this->limit > 0)
        {
            $this->limit--;
            if ($this->limit === 0)
            {
                $this->cancel();
            }
        }

        return $this->propagate;
    }

    /**
     *
     */
    public function cancel()
    {
        if ($this->pointer !== null && !$this->cancelled)
        {
            $this->router->removeHandler($this->pointer[0], $this->pointer[1]);
            $this->cancelled = true;
        }
    }

    /**
     * @internal
     * @param string $stack
     * @param int $pointer
     */
    public function setPointer($stack, $pointer)
    {
        $this->pointer = [ $stack, $pointer ];
    }
}
