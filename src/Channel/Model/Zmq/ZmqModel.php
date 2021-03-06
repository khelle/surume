<?php

namespace Surume\Channel\Model\Zmq;

use Surume\Channel\Channel;
use Surume\Channel\ChannelModelInterface;
use Surume\Channel\Model\Zmq\Connection\Connection;
use Surume\Channel\Model\Zmq\Connection\ConnectionPool;
use Surume\Channel\Model\Zmq\Buffer\Buffer;
use Surume\Event\BaseEventEmitter;
use Surume\Ipc\Zmq\ZmqContext;
use Surume\Ipc\Zmq\ZmqSocket;
use Surume\Loop\LoopInterface;
use Surume\Loop\Timer\TimerInterface;

abstract class ZmqModel extends BaseEventEmitter implements ChannelModelInterface
{
    /**
     * @var int
     */
    const CONNECTOR = 2;

    /**
     * @var int
     */
    const BINDER = 1;

    /**
     * @var int
     */
    const SOCKET_UNDEFINED = 1;

    /**
     * @var int
     */
    const COMMAND_HEARTBEAT = 1;

    /**
     * @var int
     */
    const COMMAND_MESSAGE = 2;

    /**
     * @var int
     */
    const ERROR_START = 1;

    /**
     * @var int
     */
    const MODE_STANDARD = Channel::MODE_STANDARD;

    /**
     * @var int
     */
    const MODE_BUFFER_ONLINE = Channel::MODE_BUFFER_ONLINE;

    /**
     * @var int
     */
    const MODE_BUFFER_OFFLINE = Channel::MODE_BUFFER_OFFLINE;

    /**
     * @var int
     */
    const MODE_BUFFER = Channel::MODE_BUFFER;

    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @var ZmqContext
     */
    protected $context;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $endpoint;

    /**
     * @var int
     */
    protected $type;

    /**
     * @var string[]
     */
    protected $hosts;

    /**
     * @var string[]
     */
    protected $flags;

    /**
     * @var mixed[]
     */
    protected $options;

    /**
     * @var bool
     */
    protected $isConnected;

    /**
     * @var string
     */
    protected $pendingOperation;

    /**
     * @var callable
     */
    protected $connectCallback;

    /**
     * @var callable
     */
    protected $disconnectCallback;

    /**
     * @var ZmqSocket
     */
    public $socket;

    /**
     * @var ConnectionPool
     */
    protected $connectionPool;

    /**
     * @var TimerInterface
     */
    private $hTimer;

    /**
     * @var TimerInterface
     */
    private $rTimer;

    /**
     * @param LoopInterface $loop
     * @param string[] $params
     */
    public function __construct(LoopInterface $loop, $params)
    {
        $id         = $params['id'];
        $endpoint   = $params['endpoint'];
        $type       = $params['type'];
        $hosts      = $params['hosts'];

        $flags = [
            'enableHeartbeat'       => isset($params['enableHeartbeat']) ? $params['enableHeartbeat'] : true,
            'enableBuffering'       => isset($params['enableBuffering']) ? $params['enableBuffering'] : true,
            'enableTimeRegister'    => isset($params['enableTimeRegister']) ? $params['enableTimeRegister'] : true
        ];

        $options = [
            'bufferSize'            => isset($params['bufferSize']) ? (int)$params['bufferSize'] : 0,
            'bufferTimeout'         => isset($params['bufferTimeout']) ? (int)$params['bufferTimeout'] : 0,
            'heartbeatInterval'     => isset($params['heartbeatInterval']) ? (int)$params['heartbeatInterval'] : 200,
            'heartbeatKeepalive'    => isset($params['heartbeatKeepalive']) ? (int)$params['heartbeatKeepalive'] : 1000,
            'timeRegisterInterval'  => isset($params['timeRegisterInterval']) ? (int)$params['timeRegisterInterval'] : 400
        ];

        $this->loop = $loop;
        $this->context = new ZmqContext($this->loop);
        $this->id = $id;
        $this->endpoint = $endpoint;
        $this->type = $type;
        $this->hosts = is_array($hosts) ? $hosts : [ $hosts ];
        $this->flags = $flags;
        $this->options = $options;
        $this->isConnected = false;
        $this->pendingOperation = '';
        $this->hTimer = null;
        $this->rTimer = null;
        $this->cnt = 0;

        $this->connectCallback = $this->getSocketConnectorType($this->type);
        $this->disconnectCallback = $this->getSocketDisconnectorType($this->type);
        $this->socket = $this->getSocket();
        $this->buffer = $this->getBuffer();
        $this->connectionPool = $this->getConnectionPool();

        $this->setEventHandler('messages', [ $this, 'onMessages' ]);
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->stop();

        $this->removeEventHandler('messages', [ $this, 'onMessages' ]);

        unset($this->loop);
        unset($this->context);
        unset($this->id);
        unset($this->endpoint);
        unset($this->type);
        unset($this->hosts);
        unset($this->flags);
        unset($this->options);
        unset($this->isConnected);
        unset($this->pendingOperation);
        unset($this->hTimer);
        unset($this->rTimer);

        unset($this->connectCallback);
        unset($this->disconnectCallback);
        unset($this->socket);
        unset($this->buffer);
        unset($this->connectionPool);
    }

    /**
     * @return bool
     */
    public function start()
    {
        if ($this->isConnected())
        {
            return false;
        }

        $connect = $this->connectCallback;
        if (!$this->socket->$connect($this->endpoint))
        {
            $this->emit('error', [ self::ERROR_START, 'socket not connected.' ]);
            return false;
        }

        $this->stopHeartbeat();
        $this->stopTimeRegister();

        $this->isConnected = true;

        $this->startHeartbeat();
        $this->startTimeRegister();

        $this->connectionPool->erase();
        $this->buffer->send();

        $this->emit('start');

        return true;
    }

    /**
     * @return bool
     */
    public function stop()
    {
        if (!$this->isConnected())
        {
            return false;
        }

        $this->stopHeartbeat();
        $this->stopTimeRegister();

        $disconnect = $this->disconnectCallback;
        $this->socket->$disconnect($this->endpoint);

        $this->isConnected = false;

        $this->emit('stop');

        return true;
    }

    /**
     * @param string $id
     * @param string[]|string $message
     * @param int $flags
     * @return bool
     */
    public function unicast($id, $message, $flags = self::MODE_STANDARD)
    {
        $status = $this->sendMessage($id, self::COMMAND_MESSAGE, $message, $flags);

        $this->emit('send', [ $id ]);

        return $status;
    }

    /**
     * @param string[]|string $message
     * @param int $flags
     * @return bool[]
     */
    public function broadcast($message, $flags = self::MODE_STANDARD)
    {
        $conns = $this->getConnected();
        $statuses = [];

        foreach ($conns as $conn)
        {
            $statuses[] = $this->sendMessage($conn, self::COMMAND_MESSAGE, $message, $flags);
        }

        foreach ($conns as $conn)
        {
            $this->emit('send', [ $conn ]);
        }

        return $statuses;
    }

    /**
     * @param string|null $id
     * @return bool
     */
    public function isConnected($id = null)
    {
        if ($id === null)
        {
            return $this->isConnected;
        }
        else
        {
            return $this->connectionPool->validateConnection($id);
        }
    }

    /**
     * @return string[]
     */
    public function getConnected()
    {
        return $this->connectionPool->getConnected();
    }

    /**
     * @param string $id
     * @return bool
     */
    public function connect($id)
    {
        return false;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function disconnect($id)
    {
        return false;
    }

    /**
     * @param string $id
     * @param float $until
     */
    public function setConnectionAlive($id, $until)
    {
        $this->connectionPool->setConnectionProperty($id, 'timestampIn', $until);
    }

    /**
     * @param string $id
     */
    public function setConnectionPersistent($id)
    {
        $this->connectionPool->setConnectionProperty($id, 'timestampIn', 0);
    }

    /**
     * @return int
     */
    abstract protected function getSocketType();

    /**
     * @param string[] $multipartMessage
     * @return string[]
     */
    abstract protected function parseBinderMessage($multipartMessage);

    /**
     * @param string[] $multipartMessage
     * @return string[]
     */
    abstract protected function parseConnectorMessage($multipartMessage);

    /**
     * @param string $id
     * @param string $type
     * @return string[]
     */
    abstract protected function prepareBinderMessage($id, $type);

    /**
     * @param string $id
     * @param string $type
     * @return string[]
     */
    abstract protected function prepareConnectorMessage($id, $type);

    /**
     * @return ZmqSocket
     */
    protected function getSocket()
    {
        $socket = $this->context->getSocket($this->getSocketType());

        $socket->setSockOpt(\ZMQ::SOCKOPT_IDENTITY, $this->id);
//        $socket->setSockOpt(\ZMQ::SOCKOPT_SNDHWM, $this->options['bufferSize']);
//        $socket->setSockOpt(\ZMQ::SOCKOPT_RCVHWM, $this->options['bufferSize']);
        $socket->setSockOpt(\ZMQ::SOCKOPT_LINGER, $this->options['bufferTimeout']);

        return $socket;
    }

    /**
     * @return Buffer
     */
    protected function getBuffer()
    {
        return new Buffer($this->socket, $this->options['bufferSize']);
    }

    /**
     * @return ConnectionPool
     */
    protected function getConnectionPool()
    {
        return new ConnectionPool($this->options['heartbeatKeepalive'], $this->options['heartbeatInterval']);
    }

    /**
     * @param string $event
     * @param callable $callback
     */
    protected function setEventHandler($event, callable $callback)
    {
        $this->socket->on($event, $callback);
    }

    /**
     * @param string $event
     * @param callable $callback
     */
    protected function removeEventHandler($event, callable $callback)
    {
        $this->socket->removeListener($event, $callback);
    }

    /**
     * @param string[] $argv
     */
    public function onMessages($argv)
    {
        if ($this->type === self::BINDER)
        {
            list($id, $type, $message) = $this->parseBinderMessage($argv);
        }
        else if ($this->type === self::CONNECTOR)
        {
            list($id, $type, $message) = $this->parseConnectorMessage($argv);
        }
        else
        {
            return;
        }

        $conn = new Connection($id);

        switch ($type)
        {
            case self::COMMAND_HEARTBEAT:
                $this->onRecvHeartbeat($conn);
                break;

            case self::COMMAND_MESSAGE:
                $this->onRecvMessage($conn, $message);
                break;

            default:
                return;
        }
    }

    /**
     * @param int $type
     * @return int string
     */
    private function getSocketConnectorType($type)
    {
        switch ($type)
        {
            case self::CONNECTOR:
                return 'connect';
            case self::BINDER:
                return 'bind';
            default:
                return 'fail';
        }
    }

    /**
     * @param int $type
     * @return int string
     */
    private function getSocketDisconnectorType($type)
    {
        switch ($type)
        {
            case self::CONNECTOR:
                return 'disconnect';
            case self::BINDER:
                return 'unbind';
            default:
                return 'fail';
        }
    }

    /**
     * @param Connection $conn
     * @param string[] $message
     */
    private function onRecvMessage(Connection $conn, $message)
    {
        $this->recvMessage($conn, $message);
        $this->recvHeartbeat($conn);
    }

    /**
     * @param Connection $conn
     */
    private function onRecvHeartbeat(Connection $conn)
    {
        $this->recvHeartbeat($conn);
    }

    /**
     * @param Connection $conn
     * @param $message
     * @return mixed
     */
    private function recvMessage(Connection $conn, $message)
    {
        $this->emit('recv', [ $conn->id, $message ]);
    }

    /**
     * @param Connection $conn
     */
    private function recvHeartbeat(Connection $conn)
    {
        if ($this->flags['enableHeartbeat'] !== true)
        {
            return;
        }

        if ($this->connectionPool->setConnection($conn->id))
        {
            $this->emit('connect', [ $conn->getId() ]);
        }

        if ($this->type === self::BINDER)
        {
            $this->heartbeat($conn->id);
        }
    }

    /**
     *
     */
    private function fail()
    {
        return false;
    }

    /**
     * @param string $id
     * @return bool
     */
    private function heartbeat($id)
    {
        if ($this->connectionPool->isHeartbeatNeeded($id) === true)
        {
            return $this->sendMessage($id, self::COMMAND_HEARTBEAT);
        }

        return false;
    }

    /**
     *
     */
    private function startHeartbeat()
    {
        if ($this->hTimer === null && $this->flags['enableHeartbeat'])
        {
            $proxy = $this;
            $this->hTimer = $this->loop->addPeriodicTimer(($this->options['heartbeatInterval']/1000), function() use($proxy) {

                if ($proxy->type === self::CONNECTOR)
                {
                    foreach ($proxy->hosts as $hostid)
                    {
                        $proxy->heartbeat($hostid);
                    }
                }

                $this->clearConnectionPool();
            });
        }
    }

    /**
     *
     */
    private function clearConnectionPool()
    {
        $deleted = $this->connectionPool->removeInvalid();

        foreach ($deleted as $deletedid)
        {
            $this->emit('disconnect', [ $deletedid ]);
        }
    }

    /**
     *
     */
    private function stopHeartbeat()
    {
        if ($this->hTimer !== null)
        {
            $this->hTimer->cancel();
            $this->hTimer = null;
        }
    }

    /**
     * @param string $id
     * @param string $type
     * @param mixed $message
     * @return null|string[]
     */
    private function getFrame($id, $type, $message)
    {
        if ($this->type === self::BINDER)
        {
            $frame = $this->prepareBinderMessage($id, $type);
        }
        else if ($this->type === self::CONNECTOR)
        {
            $frame = $this->prepareConnectorMessage($id, $type);
        }
        else
        {
            return null;
        }

        if ($message !== null)
        {
            if (is_object($message))
            {
                return null;
            }
            else if (!is_array($message))
            {
                $message = [ $message ];
            }

            $frame = array_merge($frame, $message);
        }

        return $frame;
    }

    /**
     * @param string $id
     * @param string $type
     * @param mixed $message
     * @param int $flags
     * @return bool
     */
    private function sendMessage($id, $type, $message = null, $flags = self::MODE_STANDARD)
    {
        if (($frame = $this->getFrame($id, $type, $message)) === null)
        {
            return false;
        }

        $isConnected = $this->isConnected();

        if (!$isConnected)
        {
            if ($this->flags['enableBuffering'] === true && ($flags & self::MODE_BUFFER_OFFLINE) === self::MODE_BUFFER_OFFLINE)
            {
                return $this->buffer->add($frame);
            }
        }
        else if ($type === self::COMMAND_HEARTBEAT)
        {
            if ($this->socket->closed === false && $this->socket->send($frame))
            {
                $this->connectionPool->registerHeartbeat($id);
                return true;
            }
        }
        else if (($this->flags['enableHeartbeat'] === false) || ($this->flags['enableBuffering'] === true && ($flags & self::MODE_BUFFER_ONLINE) === self::MODE_BUFFER_ONLINE) || ($this->connectionPool->validateConnection($id) === true))
        {
            $this->socket->send($frame);
            $this->connectionPool->registerHeartbeat($id);
            return true;
        }

        return false;
    }

    /**
     *
     */
    private function startTimeRegister()
    {
        if ($this->rTimer === null && $this->flags['enableHeartbeat'] === true && $this->flags['enableTimeRegister'] === true)
        {
            $proxy = $this;
            $this->rTimer = $this->loop->addPeriodicTimer(($this->options['timeRegisterInterval']/1000), function() use($proxy) {
                $now = round(microtime(true)*1000);
                $proxy->connectionPool->setNow(function() use($now) {
                    return $now;
                });
            });
        }
    }

    /**
     *
     */
    private function stopTimeRegister()
    {
        if ($this->rTimer !== null)
        {
            $this->rTimer->cancel();
            $this->rTimer = null;
            $this->connectionPool->resetNow();
        }
    }
}
