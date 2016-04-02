<?php

namespace Surume\Core\Provider\Core;

use Surume\Core\CoreInterface;
use Surume\Core\Environment;
use Surume\Core\EnvironmentInterface;
use Surume\Core\Service\ServiceProvider;
use Surume\Core\Service\ServiceProviderInterface;

class EnvironmentProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Surume\Core\CoreInputContextInterface',
        'Surume\Config\ConfigInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Surume\Core\EnvironmentInterface'
    ];

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $context = $core->make('Surume\Core\CoreInputContextInterface');
        $config  = $core->make('Surume\Config\ConfigInterface');

        $env = new Environment($context, $config);

        $env->setOption('error_reporting', E_ALL);
        $env->setOption('log_errors', '1');
        $env->setOption('display_errors', '0');

        $inis = (array) $config->get('core.ini');
        foreach ($inis as $option=>$value)
        {
            $env->setOption($option, $value);
        }

        $this->setProcessProperties($env);

        $env->registerErrorHandler([ 'Surume\Throwable\ErrorEnvHandler', 'handleError' ]);
        $env->registerShutdownHandler([ 'Surume\Throwable\ErrorEnvHandler', 'handleShutdown' ]);
        $env->registerExceptionHandler([ 'Surume\Throwable\ExceptionEnvHandler', 'handleException' ]);

        $core->instance(
            'Surume\Core\EnvironmentInterface',
            $env
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Surume\Core\EnvironmentInterface'
        );
    }

    /**
     * @param EnvironmentInterface $env
     */
    private function setProcessProperties(EnvironmentInterface $env)
    {
        $props = $env->getEnv('cli');
        if ($props['title'] !== 'php' && function_exists('cli_set_process_title'))
        {
            cli_set_process_title($props['title']);
        }
    }
}
