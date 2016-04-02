<?php

namespace Surume\Test\Integration\Stream;

use Surume\Stream\Stream;
use Surume\Stream\StreamReader;
use Surume\Stream\StreamWriter;
use Surume\Test\Integration\TestCase;

class StreamTest extends TestCase
{
    public function tearDown()
    {
        $local = $this->basePath();
        unlink("$local/temp");
    }

    public function testStream_WriteAndReadDataScenario()
    {
        $local = $this->basePath();
        $writer = new Stream(fopen("file://$local/temp", 'w+'));
        $reader = new Stream(fopen("file://$local/temp", 'r+'));

        $expectedData = "qwertyuiop\n";
        $capturedData = null;
        $readData = null;

        $reader->on('data', function($origin, $data) use(&$capturedData) {
            $capturedData = $data;
        });
        $reader->on('error', $this->expectCallableNever());
        $reader->on('close', $this->expectCallableOnce());

        $writer->on('drain', $this->expectCallableOnce());
        $writer->on('error', $this->expectCallableNever());
        $writer->on('close', $this->expectCallableOnce());

        $writer->write($expectedData);
        $readData = $reader->read();

        $writer->close();
        $reader->close();

        $this->assertEquals($expectedData, $readData);
        $this->assertEquals($expectedData, $capturedData);
    }

    public function testStreamReader_StreamWriter_WriteAndReadDataScenario()
    {
        $local = $this->basePath();
        $writer = new StreamWriter(fopen("file://$local/temp", 'w+'));
        $reader = new StreamReader(fopen("file://$local/temp", 'r+'));

        $expectedData = "qwertyuiop\n";
        $capturedData = null;
        $readData = null;

        $reader->on('data', function($origin, $data) use(&$capturedData) {
            $capturedData = $data;
        });
        $reader->on('drain', $this->expectCallableNever());
        $reader->on('error', $this->expectCallableNever());
        $reader->on('close', $this->expectCallableOnce());

        $writer->on('data',  $this->expectCallableNever());
        $writer->on('drain', $this->expectCallableOnce());
        $writer->on('error', $this->expectCallableNever());
        $writer->on('close', $this->expectCallableOnce());

        $writer->write($expectedData);
        $readData = $reader->read();

        $writer->close();
        $reader->close();

        $this->assertEquals($expectedData, $readData);
        $this->assertEquals($expectedData, $capturedData);
    }
}
