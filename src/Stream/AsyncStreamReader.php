<?php

namespace Surume\Stream;

use Surume\Throwable\Exception\Logic\InvalidArgumentException;
use Surume\Loop\LoopAwareTrait;
use Surume\Loop\LoopInterface;
use Error;
use Exception;

class AsyncStreamReader extends StreamReader implements AsyncStreamReaderInterface
{
    use LoopAwareTrait;

    /**
     * @var bool
     */
    protected $listening;

    /**
     * @var bool
     */
    protected $paused;

    /**
     * @param resource $resource
     * @param LoopInterface $loop
     * @param bool $autoClose
     * @throws InvalidArgumentException
     */
    public function __construct($resource, LoopInterface $loop, $autoClose = true)
    {
        parent::__construct($resource, $autoClose);

        $this->loop = $loop;
        $this->listening = false;
        $this->paused = true;

        $this->resume();
    }

    /**
     *
     */
    public function __destruct()
    {
        parent::__destruct();

        unset($this->loop);
        unset($this->listening);
        unset($this->paused);
    }

    /**
     * @override
     */
    public function isPaused()
    {
        return $this->paused;
    }

    /**
     * @override
     */
    public function setBufferSize($bufferSize)
    {
        $this->bufferSize = $bufferSize;
    }

    /**
     * @override
     */
    public function getBufferSize()
    {
        return $this->bufferSize;
    }

    /**
     * @override
     */
    public function pause()
    {
        if (!$this->paused)
        {
            $this->paused = true;
            $this->loop->removeReadStream($this->resource);
        }
    }

    /**
     * @override
     */
    public function resume()
    {
        if ($this->paused)
        {
            $this->paused = false;
            $this->loop->addReadStream($this->resource, [ $this, 'handleData' ]);
        }
    }

    /**
     * Handle the incoming stream.
     *
     * @internal
     */
    public function handleData()
    {
        try
        {
            $this->read();
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}
    }

    /**
     * @override
     */
    public function handleClose()
    {
        $this->pause();

        parent::handleClose();
    }
}
