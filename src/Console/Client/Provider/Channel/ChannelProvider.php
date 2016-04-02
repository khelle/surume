<?php

namespace Surume\Console\Client\Provider\Channel;

use Surume\Channel\ChannelBaseInterface;
use Surume\Channel\Router\RuleHandler;
use Surume\Core\CoreInterface;
use Surume\Core\Service\ServiceProvider;
use Surume\Core\Service\ServiceProviderInterface;
use Surume\Runtime\Runtime;

class ChannelProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Surume\Config\ConfigInterface',
        'Surume\Console\Client\ConsoleClientInterface',
        'Surume\Channel\ChannelFactoryInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Surume\Console\Client\Channel\ConsoleInterface'
    ];

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $factory = $core->make('Surume\Channel\ChannelFactoryInterface');
        $config  = $core->make('Surume\Config\ConfigInterface');
        $console = $core->make('Surume\Console\Client\ConsoleClientInterface');

        $channel = $factory->create('Surume\Channel\ChannelBase', [
            $config->get('channel.channels.console.class'),
            array_merge(
                $config->get('channel.channels.console.config'),
                [ 'hosts' => Runtime::RESERVED_CONSOLE_SERVER ]
            )
        ]);

        $this->applyConsoleController($channel);

        $console->onStart(function() use($channel) {
            $channel->start();
        });
        $console->onStop(function() use($channel) {
            $channel->stop();
        });

        $core->instance(
            'Surume\Console\Client\Channel\ConsoleInterface',
            $channel
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Surume\Console\Client\Channel\ConsoleInterface'
        );
    }

    /**
     * @param ChannelBaseInterface $channel
     */
    protected function applyConsoleController(ChannelBaseInterface $channel)
    {
        $router = $channel->input();
        $router->addAnchor(
            new RuleHandler(function($params) {})
        );

        $router = $channel->output();
        $router->addAnchor(
            new RuleHandler(function($params) use($channel) {
                $channel->push(
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
    }
}
