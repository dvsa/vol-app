<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cpms\Logger;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Class LoggerFactory
 *
 * @package
 */
class LoggerFactory
{
    public function __construct(private readonly string $logPath, private readonly int $zendLogLevel)
    {
    }

    public function createLogger(): Logger
    {
        $logger = new Logger('cpms_client_logger');
        $logger->pushHandler(new StreamHandler($this->logPath, $this->mapZendLogLevelToMonolog()));

        return $logger;
    }

    /**
     * transformation between logger types
     * Zend logger returns integer between 0-7 so map to array
     * @return int
     */
    private function mapZendLogLevelToMonolog(): int
    {
        $levels = [
            Logger::EMERGENCY,
            Logger::ALERT,
            Logger::CRITICAL,
            Logger::ERROR,
            Logger::WARNING,
            Logger::NOTICE,
            Logger::INFO,
            Logger::DEBUG
        ];

        return $levels[$this->zendLogLevel];
    }
}
