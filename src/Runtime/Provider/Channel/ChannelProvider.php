<?php

namespace Surume\Runtime\Provider\Channel;

use Surume\Channel\Channel;
use Surume\Channel\ChannelBaseInterface;
use Surume\Channel\ChannelProtocolInterface;
use Surume\Channel\ChannelCompositeInterface;
use Surume\Channel\Extra\Response;
use Surume\Channel\Router\RuleHandler;
use Surume\Channel\Router\RuleMatchDestination;
use Surume\Command\CommandManagerInterface;
use Surume\Core\CoreInterface;
use Surume\Core\Service\ServiceProvider;
use Surume\Core\Service\ServiceProviderInterface;
use Surume\Promise\Promise;
use Surume\Promise\PromiseInterface;
use Surume\Runtime\Runtime;
use Surume\Runtime\RuntimeInterface;
use Error;
use Exception;

class ChannelProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Surume\Command\CommandManagerInterface',
        'Surume\Config\ConfigInterface',
        'Surume\Channel\ChannelFactoryInterface',
        'Surume\Runtime\RuntimeInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Surume\Runtime\Channel\ChannelInterface'
    ];

    /**
     * @var CommandManagerInterface
     */
    protected $commander;

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $this->commander = $core->make('Surume\Command\CommandManagerInterface');

        $config  = $core->make('Surume\Config\ConfigInterface');
        $runtime = $core->make('Surume\Runtime\RuntimeInterface');
        $factory = $core->make('Surume\Channel\ChannelFactoryInterface');

        $master = $factory->create('Surume\Channel\ChannelBase', [
            $runtime->parent() !== null
                ? $config->get('channel.channels.master.class')
                : 'Surume\Channel\Model\Null\NullModel',
            array_merge(
                $config->get('channel.channels.master.config'),
                [
                    'hosts' => $runtime->parent() !== null ? $runtime->parent() : $runtime->alias()
                ]
            )
        ]);

        $slave = $factory->create('Surume\Channel\ChannelBase', [
            $config->get('channel.channels.slave.class'),
            $config->get('channel.channels.slave.config')
        ]);

        $composite = $factory->create('Surume\Channel\ChannelComposite')
            ->setBus('master', $master)
            ->setBus('slave', $slave)
        ;

        $core->instance(
            'Surume\Runtime\Channel\ChannelInterface',
            $composite
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        unset($this->commander);

        $core->remove(
            'Surume\Runtime\Channel\ChannelInterface'
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function boot(CoreInterface $core)
    {
        $runtime = $core->make('Surume\Runtime\RuntimeInterface');
        $channel = $core->make('Surume\Runtime\Channel\ChannelInterface');
        $console = $core->make('Surume\Runtime\Channel\ConsoleInterface');

        if ($runtime->parent() === null)
        {
            $this->applyRootRouting($runtime, $channel, $console);
        }
        else
        {
            $this->applySimpleRouting($runtime, $channel);
        }

        $runtime->on('create', [ $channel, 'start' ]);
        $runtime->on('destroy', [ $channel, 'stop' ]);
    }

    /**
     * @param RuntimeInterface $runtime
     * @param ChannelCompositeInterface $composite
     */
    private function applySimpleRouting(RuntimeInterface $runtime, ChannelCompositeInterface $composite)
    {
        $master = $composite->bus('master');
        $slave  = $composite->bus('slave');

        $router = $composite->input();
        $router->addAnchor(
            new RuleHandler(function($params) {
                return true;
            })
        );

        $router = $composite->output();
        $router->addAnchor(
            function($receiver, ChannelProtocolInterface $protocol, $flags, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0) use($runtime, $slave, $master) {
                if ($runtime->manager()->existsRuntime($receiver) || $slave->isConnected($receiver))
                {
                    $slave->push($receiver, $protocol, $flags, $success, $failure, $cancel, $timeout);
                }
                else
                {
                    $master->push($runtime->parent(), $protocol, $flags, $success, $failure, $cancel, $timeout);
                }
            }
        );

        $router = $master->input();
        $router->addRule(
            new RuleMatchDestination($master->name()),
            new RuleHandler(function($params) use($composite) {
                $this->executeProtocol($composite, $params['protocol']);
            })
        );
        $router->addAnchor(
            new RuleHandler(function($params) use($slave) {
                $slave->push($slave->getConnected(), $params['protocol'], $params['flags']);
            })
        );

        $router = $slave->input();
        $router->addRule(
            new RuleMatchDestination($slave->name()),
            new RuleHandler(function($params) use($composite) {
                $this->executeProtocol($composite, $params['protocol']);
            })
        );
        $router->addAnchor(
            new RuleHandler(function($params) use($runtime, $slave, $master) {
                $master->push($runtime->parent(), $params['protocol'], $params['flags']);
            })
        );

        $router = $master->output();
        $router->addAnchor(
            function($sender, ChannelProtocolInterface $protocol, $flags, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0) use($master) {
                $master->push($sender, $protocol, $flags, $success, $failure, $cancel, $timeout);
            }
        );

        $router = $slave->output();
        $router->addAnchor(
            function($sender, ChannelProtocolInterface $protocol, $flags, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0) use($slave) {
                $slave->push($sender, $protocol, $flags, $success, $failure, $cancel, $timeout);
            }
        );
    }

    /**
     * @param RuntimeInterface $runtime
     * @param ChannelCompositeInterface $composite
     * @param ChannelBaseInterface $console
     */
    private function applyRootRouting(RuntimeInterface $runtime, ChannelCompositeInterface $composite, ChannelBaseInterface $console)
    {
        $master = $composite->bus('master');
        $slave  = $composite->bus('slave');

        $router = $composite->input();
        $router->addAnchor(
            new RuleHandler(function($params) {
                return true;
            })
        );

        $router = $composite->output();
        $router->addAnchor(
            function($receiver, ChannelProtocolInterface $protocol, $flags, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0) use($runtime, $slave, $console) {
                if ($receiver === Runtime::RESERVED_CONSOLE_CLIENT || $protocol->getDestination() === Runtime::RESERVED_CONSOLE_CLIENT)
                {
                    $console->push(Runtime::RESERVED_CONSOLE_CLIENT, $protocol, $flags, $success, $failure, $cancel, $timeout);
                }
                else if ($runtime->manager()->existsRuntime($receiver) || $slave->isConnected($receiver))
                {
                    $slave->push($receiver, $protocol, $flags, $success, $failure, $cancel, $timeout);
                }
                else
                {
                    $slave->push($slave->getConnected(), $protocol, $flags, $success, $failure, $cancel, $timeout);
                }
            }
        );

        $router = $master->input();
        $router->addRule(
            new RuleMatchDestination($master->name()),
            new RuleHandler(function($params) use($composite) {
                $this->executeProtocol($composite, $params['protocol']);
            })
        );
        $router->addAnchor(
            new RuleHandler(function($params) use($slave) {
                $slave->push($slave->getConnected(), $params['protocol'], $params['flags']);
            })
        );

        $router = $slave->input();
        $router->addRule(
            new RuleMatchDestination($slave->name()),
            new RuleHandler(function($params) use($composite) {
                $this->executeProtocol($composite, $params['protocol']);
            })
        );
        $router->addAnchor(
            new RuleHandler(function($params) use($runtime, $slave, $console) {
                $receiver = $params['alias'];
                $protocol = $params['protocol'];
                if ($receiver === Runtime::RESERVED_CONSOLE_CLIENT || $protocol->getDestination() === Runtime::RESERVED_CONSOLE_CLIENT)
                {
                    $console->push(Runtime::RESERVED_CONSOLE_CLIENT, $protocol, $params['flags']);
                }
                else
                {
                    $slave->push($slave->getConnected(), $params['protocol'], $params['flags']);
                }
            })
        );

        $router = $master->output();
        $router->addAnchor(
            function($sender, ChannelProtocolInterface $protocol, $flags, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0) use($master) {
                $master->push($sender, $protocol, $flags, $success, $failure, $cancel, $timeout);
            }
        );

        $router = $slave->output();
        $router->addAnchor(
            function($sender, ChannelProtocolInterface $protocol, $flags, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0) use($slave) {
                $slave->push($sender, $protocol, $flags, $success, $failure, $cancel, $timeout);
            }
        );
    }

    /**
     * @param ChannelCompositeInterface $composite
     * @param ChannelProtocolInterface $protocol
     */
    private function executeProtocol(ChannelCompositeInterface $composite, ChannelProtocolInterface $protocol)
    {
        /**
         * If the json_decode fails, it means the received message is leftover of request response,
         * hence it should be dropped.
         */
        try
        {
            $params = json_decode($protocol->getMessage(), true);
            $command = array_shift($params);
            $params['origin'] = $protocol->getOrigin();
            $promise = $this->executeCommand($command, $params);
        }
        catch (Error $ex)
        {
            return;
        }
        catch (Exception $ex)
        {
            return;
        }

        if ($protocol->getType() === Channel::TYPE_REQ)
        {
            $promise
                ->then(
                    function($response) use($composite, $protocol, $command) {
                        return (new Response($composite, $protocol, $response))->call();
                    },
                    function($reason) use($composite, $protocol) {
                        return (new Response($composite, $protocol, $reason))->call();
                    },
                    function($reason) use($composite, $protocol) {
                        return (new Response($composite, $protocol, $reason))->call();
                    }
                );
        }
    }

    /**
     * @param string $command
     * @param mixed[] $params
     * @return PromiseInterface
     */
    private function executeCommand($command, $params = [])
    {
        try
        {
            return $this->commander->execute($command, $params);
        }
        catch (Error $ex)
        {
            return Promise::doReject($ex);
        }
        catch (Exception $ex)
        {
            return Promise::doReject($ex);
        }
    }
}
