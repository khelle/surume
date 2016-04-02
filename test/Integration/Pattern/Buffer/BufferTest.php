<?php

namespace Surume\Test\Integration\Pattern\Buffer;

use Surume\Test\Integration\TestCase;
use Surume\Util\Buffer\Buffer;
use Surume\Util\Buffer\BufferInterface;
use Surume\Util\Buffer\BufferIterator;

class BufferTest extends TestCase
{
    /**
     * @var string
     */
    protected $initialString = 'abcdefghijklmnopqrstuvwxyz';

    /**
     * @var string
     */
    protected $appendString = '1234567890';

    /**
     * @var BufferInterface
     */
    protected $buffer;

    /**
     * @var BufferIterator
     */
    protected $iterator;

    public function setUp()
    {
        $this->buffer = new Buffer($this->initialString);
        $this->iterator = $this->buffer->getIterator();
    }

    public function testIteration()
    {
        $result = null;

        for ($i = 0, $this->iterator->rewind(); $this->iterator->valid(); ++$i, $this->iterator->next())
        {
            if ($i !== $this->iterator->key())
            {
                $this->fail('Got invalid key from iterator.');
            }

            $result .= $this->iterator->current();
        }

        $this->assertSame($this->initialString, $result);

        $this->iterator->prev();

        $this->assertTrue($this->iterator->valid());
        $this->assertSame($this->buffer->length()-1, $this->iterator->key());
    }
}
