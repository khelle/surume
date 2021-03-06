<?php

namespace Surume\Channel;

trait ChannelCompositeAwareTrait
{
    /**
     * @var ChannelCompositeInterface|null
     */
    protected $channel = null;

    /**
     * @param ChannelCompositeInterface|null $channel
     * @return ChannelCompositeInterface
     */
    public function setChannel(ChannelCompositeInterface $channel = null)
    {
        $this->channel = $channel;
    }

    /**
     * @return ChannelCompositeInterface
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @return ChannelCompositeInterface
     */
    public function channel()
    {
        return $this->channel;
    }
}
