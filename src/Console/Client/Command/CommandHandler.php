<?php

namespace Surume\Console\Client\Command;

use Surume\Channel\ChannelBaseInterface;
use Surume\Channel\Extra\Request;
use Surume\Promise\PromiseInterface;
use Surume\Runtime\RuntimeCommand;

class CommandHandler implements CommandHandlerInterface
{
    /**
     * @var ChannelBaseInterface
     */
    protected $channel;

    /**
     * @var string
     */
    protected $receiver;

    /**
     * @param ChannelBaseInterface $channel
     * @param string $receiver
     */
    public function __construct(ChannelBaseInterface $channel, $receiver)
    {
        $this->channel = $channel;
        $this->receiver = $receiver;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->channel);
        unset($this->receiver);
    }

    /**
     * @param string|null $commandParent
     * @param string $commandName
     * @param string[] $commandParams
     * @return PromiseInterface
     */
    public function handle($commandParent, $commandName, $commandParams = [])
    {
        $protocol = $this->channel->createProtocol(
            new RuntimeCommand($commandName, $commandParams)
        );

        if ($commandParent !== null)
        {
            $protocol
                ->setDestination($commandParent);
        }

        $req = new Request(
            $this->channel,
            $this->receiver,
            $protocol,
            [
                'timeout'         =>  2,
                'retriesLimit'    => 10,
                'retriesInterval' =>  1
            ]
        );

        return $req->call();
    }
}
