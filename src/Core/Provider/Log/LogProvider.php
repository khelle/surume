<?php

namespace Surume\Core\Provider\Log;

use Surume\Config\ConfigInterface;
use Surume\Core\CoreInterface;
use Surume\Core\Service\ServiceProvider;
use Surume\Core\Service\ServiceProviderInterface;
use Surume\Log\Handler\HandlerInterface;
use Surume\Log\Logger;
use Surume\Log\LoggerFactory;
use Surume\Support\StringSupport;

class LogProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Surume\Config\ConfigInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Surume\Log\LoggerInterface',
        'Surume\Log\LoggerFactory'
    ];

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $config  = $core->make('Surume\Config\ConfigInterface');

        $factory = new LoggerFactory();
        $logger  = new Logger(
            'surume',
            [
                $this->createHandler($core, $config, 'debug', Logger::DEBUG),
                $this->createHandler($core, $config, 'info', Logger::INFO),
                $this->createHandler($core, $config, 'notice', Logger::NOTICE),
                $this->createHandler($core, $config, 'warning', Logger::WARNING),
                $this->createHandler($core, $config, 'error', Logger::EMERGENCY)
            ]
        );

        $core->instance(
            'Surume\Log\LoggerFactory',
            $factory
        );

        $core->instance(
            'Surume\Log\LoggerInterface',
            $logger
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Surume\Log\LoggerInterface'
        );

        $core->remove(
            'Surume\Log\LoggerFactory'
        );
    }

    /**
     * @param CoreInterface $core
     * @param ConfigInterface $config
     * @param string $level
     * @param int $loggerLevel
     * @return HandlerInterface
     */
    private function createHandler(CoreInterface $core, ConfigInterface $config, $level, $loggerLevel)
    {
        $factory = new LoggerFactory();

        $formatter = $factory->createFormatter(
            'LineFormatter', [ $config->get('log.messagePattern'), $config->get('log.datePattern'), true ]
        );

        $filePermission = $config->get('log.filePermission');
        $fileLocking = (bool) $config->get('log.fileLocking');
        $filePath = $config->get('log.filePattern');

        $loggerHandler = $factory->createHandler(
            'StreamHandler',
            [
                $this->filePath($filePath, $level),
                $loggerLevel,
                false,
                $filePermission,
                $fileLocking
            ]
        );
        $loggerHandler
            ->setFormatter($formatter);

        return $loggerHandler;
    }

    /**
     * @param string $path
     * @param string $level
     * @return string
     */
    private function filePath($path, $level)
    {
        return StringSupport::parametrize($path, [
            'level' => $level,
            'date'  => date('Y-m-d'),
            'time'  => date('H:i:s')
        ]);
    }
}
