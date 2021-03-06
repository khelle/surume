<?php

namespace Surume\Console\Server\Provider\Channel;

use Surume\Channel\Channel;
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
            $config->get('channel.channels.master.class'),
            $config->get('channel.channels.master.config')
        ]);

        $slave = $factory->create('Surume\Channel\ChannelBase', [
            $config->get('channel.channels.slave.class'),
            array_merge(
                $config->get('channel.channels.slave.config'),
                [ 'name' => Runtime::RESERVED_CONSOLE_CLIENT ]
            )
        ]);

        $composite = $factory->create('Surume\Channel\ChannelComposite')
            ->setBus('master', $master)
            ->setBus('slave', $slave)
        ;

//        $composite->on('connect', function($alias) {
//            echo "Connected [$alias]\n";
//        });
//        $composite->on('disconnect', function($alias) {
//            echo "Disconnected [$alias]\n";
//        });

        $this->applyConsoleRouting($runtime, $composite);

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

        $runtime->on('create', [ $channel, 'start' ]);
        $runtime->on('destroy', [ $channel, 'stop' ]);
    }

    /**
     * @param RuntimeInterface $runtime
     * @param ChannelCompositeInterface $composite
     */
    private function applyConsoleRouting(RuntimeInterface $runtime, ChannelCompositeInterface $composite)
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
            new RuleHandler(function($params) use($slave, $master) {
                $ch = ($params['alias'] === Runtime::RESERVED_CONSOLE_CLIENT) ? $master : $slave;
                $ch->push(
                    $params['alias'],
                    $params['protocol'],
                    $params['flags'],
                    $params['success'],
                    $params['failure'],
                    $params['cancel'],
                    $params['timeout']
                );
            })
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
//        $router->addRule(
//            new RuleMatchDestination($slave->name()),
//            new RuleHandler(function($params) use($composite) {
//                $this->executeProtocol($composite, $params['protocol']);
//            })
//        );
        $router->addAnchor(
            new RuleHandler(function($params) use($runtime, $slave, $master) {
                $master->push(Runtime::RESERVED_CONSOLE_CLIENT, $params['protocol'], $params['flags']);
            })
        );

        $router = $master->output();
        $router->addAnchor(
            new RuleHandler(function($params) use($master) {
                $protocol = $params['protocol'];
                $master->push(
                    $protocol->getDestination(),
                    $protocol,
                    $params['flags'],
                    $params['success'],
                    $params['failure'],
                    $params['cancel'],
                    $params['timeout']
                );
            })
        );

        $router = $slave->output();
        $router->addAnchor(
            new RuleHandler(function($params) use($slave) {
                $protocol = $params['protocol'];
                $slave->push(
                    $protocol->getDestination(),
                    $protocol,
                    $params['flags'],
                    $params['success'],
                    $params['failure'],
                    $params['cancel'],
                    $params['timeout']
                );
            })
        );
    }

    /**
     * @param ChannelCompositeInterface $composite
     * @param ChannelProtocolInterface $protocol
     */
    private function executeProtocol(ChannelCompositeInterface $composite, ChannelProtocolInterface $protocol)
    {
        $params = json_decode($protocol->getMessage(), true);
        $command = array_shift($params);
        $params['origin'] = $protocol->getOrigin();
        $promise = $this->executeCommand($command, $params);

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
