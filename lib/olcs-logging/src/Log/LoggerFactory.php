<?php

namespace Olcs\Logging\Log;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger as MonologLogger;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use RuntimeException;

/**
 * Builds a Monolog logger from the existing config['log'][$requestedName]
 * shape that olcs-logging consumers already produce. The shape is preserved
 * so vol-app config files do not need to change.
 *
 * Expected shape under config['log'][$serviceName]:
 *
 *     processors: [ ['name' => '<class>'], ... ]
 *     writers: [
 *         '<key>' => [
 *             'name' => 'stream',
 *             'options' => [
 *                 'stream' => '<path or php://...>',
 *                 'formatter' => '<class>',
 *                 'filters' => ['priority' => ['options' => ['priority' => <PSR-3 LogLevel string|int 0-7>]]],
 *             ],
 *         ],
 *     ]
 */
class LoggerFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): MonologLogger
    {
        $config = $container->get('Config');
        $loggerConfig = $config['log'][$requestedName] ?? [];

        $logger = new MonologLogger((string) $requestedName);

        foreach ($loggerConfig['writers'] ?? [] as $writer) {
            $logger->pushHandler($this->buildHandler($container, $writer));
        }

        foreach ($loggerConfig['processors'] ?? [] as $processorSpec) {
            $processor = $this->resolveProcessor($container, $processorSpec);

            if ($processor !== null) {
                $logger->pushProcessor($processor);
            }
        }

        return $logger;
    }

    private function buildHandler(ContainerInterface $container, array $writer): HandlerInterface
    {
        $name = $writer['name'] ?? null;
        $options = $writer['options'] ?? [];
        $level = $this->resolveLevel($options);

        $handler = match ($name) {
            'stream', null => $this->buildStreamHandler($options, $level),
            default => throw new RuntimeException(sprintf('Unsupported log writer "%s"', $name)),
        };

        if (isset($options['formatter'])) {
            $handler->setFormatter($this->resolveFormatter($container, $options['formatter']));
        }

        return $handler;
    }

    private function buildStreamHandler(array $options, Level $level): StreamHandler
    {
        $stream = $options['stream'] ?? 'php://stdout';

        return new StreamHandler($stream, $level);
    }

    /**
     * Resolves a level threshold from config. Accepts:
     *   - a PSR-3 LogLevel string (case-insensitive) — preferred
     *   - an RFC 5424 syslog int 0..7 (back-compat with the laminas-log era)
     *   - a numeric string '0'..'7' — AWS Parameter Store returns strings
     *
     * Throws on anything else, including missing config — silent defaults
     * mask configuration bugs.
     */
    private function resolveLevel(array $options): Level
    {
        $priority = $options['filters']['priority']['options']['priority'] ?? null;

        return match (is_string($priority) ? strtolower($priority) : $priority) {
            0, '0', LogLevel::EMERGENCY => Level::Emergency,
            1, '1', LogLevel::ALERT => Level::Alert,
            2, '2', LogLevel::CRITICAL => Level::Critical,
            3, '3', LogLevel::ERROR => Level::Error,
            4, '4', LogLevel::WARNING => Level::Warning,
            5, '5', LogLevel::NOTICE => Level::Notice,
            6, '6', LogLevel::INFO => Level::Info,
            7, '7', LogLevel::DEBUG => Level::Debug,
            default => throw new RuntimeException(
                sprintf('Unrecognised log priority %s.', var_export($priority, true))
            ),
        };
    }

    private function resolveFormatter(ContainerInterface $container, string $formatter): FormatterInterface
    {
        if ($container->has($formatter)) {
            return $container->get($formatter);
        }

        return new $formatter();
    }

    private function resolveProcessor(ContainerInterface $container, array $spec): ?callable
    {
        $name = $spec['name'] ?? null;

        if ($name === null) {
            return null;
        }

        if ($container->has($name)) {
            $processor = $container->get($name);
        } else {
            $processor = new $name();
        }

        if (!is_callable($processor)) {
            throw new RuntimeException(sprintf('Log processor "%s" is not callable', $name));
        }

        return $processor;
    }
}
