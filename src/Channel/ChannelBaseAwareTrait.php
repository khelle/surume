<?php

namespace Surume\Channel;

trait ChannelBaseAwareTrait
{
    /**
     * @var ChannelBaseInterface|null
     */
    protected $channel = null;

    /**
     * @param ChannelBaseInterface|null $channel
     * @return ChannelBaseInterface
     */
    public function setChannel(ChannelBaseInterface $channel = null)
    {
        $this->channel = $channel;
    }

    /**
     * @return ChannelBaseInterface
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @return ChannelBaseInterface
     */
    public function channel()
    {
        return $this->channel;
    }
}
