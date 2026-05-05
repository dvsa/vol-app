<?php

namespace Olcs\Logging;

use Laminas\EventManager\EventInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Monolog\ErrorHandler;
use Olcs\Logging\Log\Formatter\Standard;
use Olcs\Logging\Log\Formatter\StandardFactory;
use Olcs\Logging\Log\Logger as StaticLogger;
use Olcs\Logging\Log\LoggerFactory;
use Psr\Log\LogLevel;

class Module
{
    public function getConfig(): array
    {
        $logfile = sys_get_temp_dir() . '/olcs-' . PHP_SAPI . '-application.log';

        $processors = [
            ['name' => Log\Processor\Microtime::class],
            ['name' => Log\Processor\UserId::class],
            ['name' => Log\Processor\SessionId::class],
            ['name' => Log\Processor\RemoteIp::class],
            ['name' => Log\Processor\RequestId::class],
            ['name' => Log\Processor\CorrelationId::class],
        ];

        return [
            'listeners' => [
                Listener\LogRequest::class,
                Listener\LogError::class,
            ],
            'service_manager' => [
                'factories' => [
                    'Logger' => LoggerFactory::class,
                    'ExceptionLogger' => LoggerFactory::class,
                    Listener\LogRequest::class => Listener\LogRequest::class,
                    Listener\LogError::class => Listener\LogError::class,
                    Helper\LogException::class => Helper\LogException::class,
                    Helper\LogError::class => Helper\LogError::class,
                    Standard::class => StandardFactory::class,
                    Log\Processor\Microtime::class => InvokableFactory::class,
                    Log\Processor\UserId::class => InvokableFactory::class,
                    Log\Processor\SessionId::class => InvokableFactory::class,
                    Log\Processor\RemoteIp::class => InvokableFactory::class,
                    Log\Processor\RequestId::class => InvokableFactory::class,
                    Log\Processor\HidePassword::class => InvokableFactory::class,
                    Log\Processor\CorrelationId::class => Log\Processor\CorrelationIdFactory::class,
                ],
            ],
            'log' => [
                'Logger' => [
                    'processors' => $processors,
                    'writers' => [
                        'full' => [
                            'name' => 'stream',
                            'options' => [
                                'stream' => $logfile,
                                'formatter' => Standard::class,
                                'filters' => [
                                    'priority' => [
                                        'name' => 'priority',
                                        'options' => [
                                            'priority' => LogLevel::DEBUG,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function onBootstrap(EventInterface $event): void
    {
        /** @var MvcEvent $event */
        $serviceManager = $event->getApplication()->getServiceManager();
        $logger = $serviceManager->get('Logger');
        $config = $serviceManager->get('Config');

        if (empty($config['log']['allowPasswordLogging'])) {
            $hidePassword = $serviceManager->get(Log\Processor\HidePassword::class);
            $logger->pushProcessor($hidePassword);
        }

        ErrorHandler::register($logger);

        StaticLogger::setLogger($logger);
    }
}
