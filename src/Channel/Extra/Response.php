<?php

namespace Surume\Channel\Extra;

use Surume\Promise\Promise;
use Surume\Promise\PromiseInterface;
use Surume\Channel\Channel;
use Surume\Channel\ChannelBaseInterface;
use Surume\Channel\ChannelProtocolInterface;
use Error;
use Exception;

class Response
{
    /**
     * @var ChannelBaseInterface
     */
    protected $channel;

    /**
     * @var ChannelProtocolInterface
     */
    protected $protocol;

    /**
     * @var string|string[]|Error|Exception
     */
    protected $message;

    /**
     * @var mixed[]
     */
    protected $params;

    /**
     * @param ChannelBaseInterface $channel
     * @param ChannelProtocolInterface $protocol
     * @param string|string[]|Error|Exception $message
     * @param mixed[] $params
     */
    public function __construct($channel, $protocol, $message, $params = [])
    {
        $this->channel = $channel;
        $this->protocol = $protocol;
        $this->message = $message;
        $this->params = [];
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->channel);
        unset($this->protocol);
        unset($this->message);
        unset($this->params);
    }

    /**
     * @return PromiseInterface
     */
    public function __invoke()
    {
        return $this->send(new Promise());
    }

    /**
     * @return PromiseInterface
     */
    public function call()
    {
        return $this->send(new Promise());
    }

    /**
     * @param PromiseInterface $promise
     * @return PromiseInterface
     */
    protected function send(PromiseInterface $promise)
    {
        $pid     = $this->protocol->getPid();
        $origin  = $this->protocol->getOrigin();
        $message = $this->message;
        $channel = $this->channel;

        if ($message instanceof Exception)
        {
            $answer = $channel->createProtocol($message->getMessage())
                ->setPid($pid, true)
                ->setException(get_class($message), true)
            ;
        }
        else
        {
            $answer = $channel->createProtocol($message)
                ->setPid($pid, true)
            ;
        }

        $this->channel->send(
            $origin,
            $answer,
            Channel::MODE_BUFFER_ONLINE
        );

        return $promise->resolve();
    }
}
