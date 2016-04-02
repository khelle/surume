<?php

namespace Surume\Test\Unit\Stream;

use Surume\Loop\LoopInterface;
use Surume\Stream\AsyncStreamReader;
use Surume\Test\Unit\TestCase;

class AsyncStreamReaderTest extends TestCase
{
    public function testApiRead_ReadsDataProperly()
    {
        $stream = $this->createAsyncStreamReaderMock();
        $resource = $stream->getResource();

        $expectedData = "foobar\n";
        $capturedData = null;
        $capturedOrigin = null;

        $stream->on('data', function($origin, $data) use(&$capturedOrigin, &$capturedData) {
            $capturedOrigin = $origin;
            $capturedData = $data;
        });

        fwrite($resource, $expectedData);
        rewind($resource);
        $stream->handleData($stream->getResource());

        $this->assertSame($expectedData, $capturedData);
        $this->assertSame($stream, $capturedOrigin);
    }

    /**
     * @param resource|null $resource
     * @param LoopInterface|null $loop
     * @return AsyncStreamReader
     */
    private function createAsyncStreamReaderMock($resource = null, $loop = null)
    {
        return new AsyncStreamReader(
            is_null($resource) ? fopen('php://temp', 'r+') : $resource,
            is_null($loop) ? $this->createLoopMock() : $loop
        );
    }
}
