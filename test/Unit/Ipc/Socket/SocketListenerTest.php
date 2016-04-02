<?php

namespace Surume\Test\Unit\Ipc\Socket;

use Surume\Throwable\Exception\Logic\InstantiationException;
use Surume\Ipc\Socket\SocketListener;
use Surume\Loop\LoopInterface;
use Surume\Test\Unit\TestCase;

class SocketListenerTest extends TestCase
{
    public function testConstructor()
    {
        $socket = $this->createSocketListenerMock();
    }

    public function testConstructor_ThrowsException_OnInvalidResource()
    {
        $this->setExpectedException(InstantiationException::class);
        $socket = $this->createSocketListenerMock('invalid');
    }

    public function testConstructor_ThrowsException_OnOccupiedEndpoint()
    {
        $server = stream_socket_server($this->tempSocketRemoteAddress());
        $this->setExpectedException(InstantiationException::class);
        $socket = $this->createSocketListenerMock();
    }

    public function testApiGetLocalEndpoint_ReturnsValidEndpoint()
    {
        $socket = $this->createSocketListenerMock($this->tempSocketRemoteAddress());
        $this->assertRegExp('#^tcp://(([0-9]*?)\.){3}([0-9]*?):([0-9]*?)$#si', $socket->getLocalEndpoint());
    }

    /**
     * @param resource|null $resource
     * @param LoopInterface $loop
     * @return SocketListener
     */
    protected function createSocketListenerMock($resource = null, LoopInterface $loop = null)
    {
        return $this->createSocketListenerInjection(
            is_null($resource) ? $this->tempSocketRemoteAddress() : $resource,
            is_null($loop) ? $this->createLoopMock() : $loop
        );
    }

    /**
     * @param string|resource $endpointOrResource
     * @param LoopInterface $loop
     * @return SocketListener
     */
    protected function createSocketListenerInjection($endpointOrResource, LoopInterface $loop)
    {
        return new SocketListener($endpointOrResource, $loop);
    }

    /**
     * @return string
     */
    private function tempSocketRemoteAddress()
    {
        return 'tcp://127.0.0.1:2080';
    }
}
