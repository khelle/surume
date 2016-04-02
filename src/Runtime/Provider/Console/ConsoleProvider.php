<?php

namespace Surume\Runtime\Provider\Console;

use Surume\Channel\ChannelBaseInterface;
use Surume\Channel\ChannelCompositeInterface;
use Surume\Channel\Router\RuleHandler;
use Surume\Core\CoreInterface;
use Surume\Core\Service\ServiceProvider;
use Surume\Core\Service\ServiceProviderInterface;
use Surume\Runtime\Runtime;

class ConsoleProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Surume\Config\ConfigInterface',
        'Surume\Channel\ChannelFactoryInterface',
        'Surume\Runtime\RuntimeInterface',
        'Surume\Command\CommandManagerInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Surume\Runtime\Channel\ConsoleInterface'
    ];

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $config  = $core->make('Surume\Config\ConfigInterface');
        $factory = $core->make('Surume\Channel\ChannelFactoryInterface');
        $runtime = $core->make('Surume\Runtime\RuntimeInterface');

        $console = $factory->create('Surume\Channel\ChannelBase', [
            $runtime->parent() === null
                ? $config->get('channel.channels.console.class')
                : 'Surume\Channel\Model\Null\NullModel',
            array_merge(
                $config->get('channel.channels.console.config'),
                [ 'hosts' => Runtime::RESERVED_CONSOLE_CLIENT ]
            )
        ]);

        $core->instance(
            'Surume\Runtime\Channel\ConsoleInterface',
            $console
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Surume\Runtime\Channel\ConsoleInterface'
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

        $this->applyConsoleRouting($channel, $console);

        $runtime->on('create', [ $console, 'start' ]);
        $runtime->on('destroy', [ $console, 'stop' ]);
    }

    /**
     * @param ChannelCompositeInterface $channel
     * @param ChannelBaseInterface $console
     */
    private function applyConsoleRouting(ChannelCompositeInterface $channel, ChannelBaseInterface $console)
    {
        $master = $channel->bus('master');

        $router = $console->input();
        $router->addAnchor(
            new RuleHandler(function($params) use($master) {
                $master->receive(
                    $params['alias'],
                    $params['protocol']
                );
            })
        );

        $router = $console->output();
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
