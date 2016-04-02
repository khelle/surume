<?php

namespace Surume\Test\Unit\Stream;

use Surume\Loop\LoopInterface;
use Surume\Stream\AsyncStreamWriter;
use Surume\Test\Unit\TestCase;

class AsyncStreamWriterTest extends TestCase
{
    public function testApiWrite_WritesDataProperly()
    {
        $stream = $this->createAsyncStreamWriterMock();
        $resource = $stream->getResource();

        $expectedData = "foobar\n";

        $stream->on('drain', $this->expectCallableOnce());

        $stream->write($expectedData);
        $stream->rewind();

        $this->assertSame($expectedData, fread($resource, $stream->getBufferSize()));
    }

    /**
     * @param resource|null $resource
     * @param LoopInterface|null $loop
     * @return AsyncStreamWriter
     */
    private function createAsyncStreamWriterMock($resource = null, $loop = null)
    {
        return new AsyncStreamWriter(
            is_null($resource) ? fopen('php://temp', 'r+') : $resource,
            is_null($loop) ? $this->createWritableLoopMock() : $loop
        );
    }
}
