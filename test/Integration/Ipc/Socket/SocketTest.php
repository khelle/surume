<?php

namespace Surume\Test\Integration\Ipc\Socket;

use Surume\Ipc\Socket\Socket;
use Surume\Ipc\Socket\SocketInterface;
use Surume\Ipc\Socket\SocketListener;
use Surume\Ipc\Socket\SocketListenerInterface;
use Surume\Test\Integration\TestCase;
use Surume\Test\Unit\Stub\EventCollection;

class SocketTest extends TestCase
{
    /**
     * @dataProvider endpointProvider
     */
    public function testSocketWritesAndReadsDataCorrectly($endpoint)
    {
        $this
            ->simulate(function() use($endpoint) {
                $loop = $this->loop();
                $events = $this->createEventCollection();

                $server = new SocketListener($endpoint, $loop);
                $server->on('connect', function(SocketListenerInterface $server, SocketInterface $conn) use($events) {
                    $conn->on('data', function(SocketInterface $conn, $data) use($server, $events) {
                        $events->enqueue($this->createEvent('data', $data));
                        $conn->write('secret answer!');
                        $server->close();
                    });
                });
                $server->on('error', $this->expectCallableNever());
                $server->on('close', function() use($events) {
                    $events->enqueue($this->createEvent('close'));
                });

                $client = new Socket($endpoint, $loop);
                $client->on('data', function(SocketInterface $conn, $data) use($loop, $events) {
                    $events->enqueue($this->createEvent('data', $data));
                    $conn->close();
                    $loop->stop();
                });
                $client->on('error', $this->expectCallableNever());
                $client->on('close', function() use($events) {
                    $events->enqueue($this->createEvent('close'));
                });

                $client->write('secret question!');

                return $events;
            })
            ->done(function(EventCollection $events) {
                $this->assertEvents($events, [
                    $this->createEvent('data', 'secret question!'),
                    $this->createEvent('close'),
                    $this->createEvent('data', 'secret answer!'),
                    $this->createEvent('close')
                ]);
            });
    }

    /**
     * @return string[][]
     */
    public function endpointProvider()
    {
        return [
            [ 'tcp://127.0.0.1:2080' ],
            [ 'tcp://[::1]:2080' ],
            [ 'unix://mysocket.sock' ]
        ];
    }
}
