<?php

namespace Surume\Test\Unit\Stream;

use Surume\Loop\LoopInterface;
use Surume\Stream\AsyncStream;
use Surume\Test\Unit\TestCase;

class AsyncStreamTest extends TestCase
{
    public function testApiPauseAndResumeAndIsPaused()
    {
        $stream = $this->createStreamMock();

        $this->assertFalse($stream->isPaused());
        $stream->pause();
        $this->assertTrue($stream->isPaused());
        $stream->resume();
        $this->assertFalse($stream->isPaused());
    }

    public function testApiWriteAndRead()
    {
        $stream = $this->createStreamMock(
            null,
            $this->createWritableLoopMock()
        );

        $expectedData = "foobar\n";
        $capturedData = null;
        $capturedOrigin = null;

        $stream->on('data', function($origin, $data) use(&$capturedOrigin, &$capturedData) {
            $capturedOrigin = $origin;
            $capturedData = $data;
        });
        $stream->on('drain', $this->expectCallableOnce());

        $stream->write($expectedData);
        $stream->rewind();
        $stream->handleData($stream->getResource());

        $this->assertSame($expectedData, $capturedData);
        $this->assertSame($stream, $capturedOrigin);
    }

    /**
     * @param resource|null $resource
     * @param LoopInterface|null $loop
     * @return AsyncStream
     */
    private function createStreamMock($resource = null, $loop = null)
    {
        return new AsyncStream(
            is_null($resource) ? fopen('php://temp', 'r+') : $resource,
            is_null($loop) ? $this->createLoopMock() : $loop
        );
    }
}
