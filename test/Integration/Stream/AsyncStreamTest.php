<?php

namespace Surume\Test\Integration\Stream;

use Surume\Stream\AsyncStreamReaderInterface;
use Surume\Stream\AsyncStreamWriterInterface;
use Surume\Test\Integration\TestCase;
use Surume\Test\Unit\Stub\EventCollection;
use ReflectionClass;

class AsyncStreamTest extends TestCase
{
    public function tearDown()
    {
        $local = $this->basePath();
        unlink("$local/temp");
    }

    /**
     * @dataProvider asyncStreamPairProvider
     * @param string $readerClass
     * @param string $writerClass
     */
    public function testAsyncStream_WritesAndReadsDataCorrectly($readerClass, $writerClass)
    {
        $this
            ->simulate(function() use($readerClass, $writerClass) {
                $loop = $this->loop();
                $local = $this->basePath();
                $events = $this->createEventCollection();
                $cnt = 0;

                $reader = (new ReflectionClass($readerClass))->newInstance(
                    fopen("file://$local/temp", 'w+'),
                    $loop
                );
                $reader->on('data', function(AsyncStreamReaderInterface $conn, $data) use($events) {
                    $events->enqueue($this->createEvent('data', $data));
                    $conn->close();
                });
                $reader->on('drain', $this->expectCallableNever());
                $reader->on('error', $this->expectCallableNever());
                $reader->on('close', function() use($events, &$cnt, $loop) {
                    $events->enqueue($this->createEvent('close'));
                    if (++$cnt === 2)
                    {
                        $loop->stop();
                    }
                });

                $writer = (new ReflectionClass($writerClass))->newInstance(
                    fopen("file://$local/temp", 'r+'),
                    $loop
                );
                $writer->on('data', $this->expectCallableNever());
                $writer->on('drain', function(AsyncStreamWriterInterface $writer) use($events) {
                    $events->enqueue($this->createEvent('drain'));
                    $writer->close();
                });
                $writer->on('error', $this->expectCallableNever());
                $writer->on('close', function() use($events, &$cnt, $loop) {
                    $events->enqueue($this->createEvent('close'));
                    if (++$cnt === 2)
                    {
                        $loop->stop();
                    }
                });

                $writer->write('message!');

                return $events;
            })
            ->done(function(EventCollection $events) {
                $this->assertEvents($events, [
                    $this->createEvent('drain'),
                    $this->createEvent('close'),
                    $this->createEvent('data', 'message!'),
                    $this->createEvent('close')
                ]);
            });
    }

    /**
     * Provider classes of AsyncStream.
     *
     * @return string[][]
     */
    public function asyncStreamPairProvider()
    {
        return [
            [
                'Surume\Stream\AsyncStream',
                'Surume\Stream\AsyncStream'
            ],
            [
                'Surume\Stream\AsyncStreamReader',
                'Surume\Stream\AsyncStreamWriter'
            ]
        ];
    }
}
