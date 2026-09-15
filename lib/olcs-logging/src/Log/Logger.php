<?php

namespace Olcs\Logging\Log;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

/**
 * Static logger facade.
 *
 * Public API is preserved for back-compat with the call sites that
 * use it across vol-app and olcs-common. Internally backed by any PSR-3
 * implementation (Monolog in production).
 */
class Logger
{
    private static ?LoggerInterface $logger = null;

    public static function setLogger(LoggerInterface $logger): void
    {
        self::$logger = $logger;
    }

    public static function getLogger(): LoggerInterface
    {
        if (self::$logger === null) {
            self::$logger = new NullLogger();
        }

        return self::$logger;
    }

    public static function emerg(string|\Stringable $message, array $extra = []): void
    {
        self::getLogger()->emergency($message, $extra);
    }

    public static function alert(string|\Stringable $message, array $extra = []): void
    {
        self::getLogger()->alert($message, $extra);
    }

    public static function crit(string|\Stringable $message, array $extra = []): void
    {
        self::getLogger()->critical($message, $extra);
    }

    public static function err(string|\Stringable $message, array $extra = []): void
    {
        self::getLogger()->error($message, $extra);
    }

    public static function warn(string|\Stringable $message, array $extra = []): void
    {
        self::getLogger()->warning($message, $extra);
    }

    public static function notice(string|\Stringable $message, array $extra = []): void
    {
        self::getLogger()->notice($message, $extra);
    }

    public static function info(string|\Stringable $message, array $extra = []): void
    {
        self::getLogger()->info($message, $extra);
    }

    public static function debug(string|\Stringable $message, array $extra = []): void
    {
        self::getLogger()->debug($message, $extra);
    }

    public static function log(string $priority, string|\Stringable $message, array $extra = []): void
    {
        self::getLogger()->log($priority, $message, $extra);
    }

    /**
     * Log data using a response status code to set the priority. Always logs at DEBUG.
     */
    public static function logResponse(int $status, string|\Stringable $message, array $extra = []): void
    {
        self::getLogger()->debug($message, $extra);
    }

    /**
     * Log an exception with its trace.
     *
     * @param string $priority A Psr\Log\LogLevel::* value.
     */
    public static function logException(\Throwable $e, string $priority = LogLevel::DEBUG): void
    {
        $message = sprintf(
            "Code %s : %s\n%s Line %d",
            $e->getCode(),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );

        self::getLogger()->log($priority, $message, ['trace' => $e->getTraceAsString()]);
    }
}
