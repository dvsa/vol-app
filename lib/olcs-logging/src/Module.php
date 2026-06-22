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

        $this->registerUserErrorTolerance();
    }

    /**
     * Tolerate E_USER_ERROR the way laminas-log did pre-monolog.
     *
     * laminas-view / guzzle __toString fallbacks call trigger_error(E_USER_ERROR)
     * then return ''. laminas-log's handler returned true, so execution continued
     * and pages still rendered. Monolog treats E_USER_ERROR as fatal and halts the
     * request. We wrap Monolog's handler: log E_USER_ERROR with a triage tag and
     * return true so execution continues; defer every other level to Monolog so its
     * logging is unchanged. Only explicit trigger_error(E_USER_ERROR) is affected -
     * real fatals are not catchable here and remain fatal.
     */
    private function registerUserErrorTolerance(): void
    {
        // Capture the currently-active handler (Monolog's) without disturbing it.
        $previous = set_error_handler(static fn (): bool => false);
        restore_error_handler();

        set_error_handler($this->makeToleranceHandler($previous));
    }

    public function makeToleranceHandler(?callable $previous): callable
    {
        return static function (int $errno, string $message, string $file = '', int $line = 0) use ($previous): bool {
            if ($errno === E_USER_ERROR) {
                StaticLogger::err(
                    'TOLERATED_USER_ERROR: ' . $message,
                    [
                        'tag' => 'tolerated-user-error',
                        'errno' => $errno,
                        'file' => $file,
                        'line' => $line,
                        'url' => $_SERVER['REQUEST_URI'] ?? null,
                    ]
                );

                return true;
            }

            return is_callable($previous) ? (bool) $previous($errno, $message, $file, $line) : false;
        };
    }
}
