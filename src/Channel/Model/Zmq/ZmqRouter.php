<?php

namespace Surume\Channel\Model\Zmq;

use Surume\Channel\ChannelModelInterface;

class ZmqRouter extends ZmqModel implements ChannelModelInterface
{
    /**
     * @return int
     */
    protected function getSocketType()
    {
        return \ZMQ::SOCKET_ROUTER;
    }

    /**
     * @param string[] $multipartMessage
     * @return string[]
     */
    protected function parseBinderMessage($multipartMessage)
    {
        $id = $multipartMessage[2];
        $type = $multipartMessage[3];
        $message = array_slice($multipartMessage, 4);

        return [ $id, $type, $message ];
    }

    /**
     * @param string[] $multipartMessage
     * @return string[]
     */
    protected function parseConnectorMessage($multipartMessage)
    {
        $id = $multipartMessage[2];
        $type = $multipartMessage[3];
        $message = array_slice($multipartMessage, 4);

        return [ $id, $type, $message ];
    }

    /**
     * @param string $id
     * @param string $type
     * @return string[]
     */
    protected function prepareBinderMessage($id, $type)
    {
        return [ $id, $id, $this->id, $type ];
    }

    /**
     * @param string $id
     * @param string $type
     * @return string[]
     */
    protected function prepareConnectorMessage($id, $type)
    {
        return [ $id, $id, $this->id, $type ];
    }
}
