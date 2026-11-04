<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Framework\Support;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\TypeRegistry;
use Laminas\Session\AbstractContainer;
use Olcs\Logging\Log\Logger;
use Olcs\Logging\Log\Processor\UserId;

/**
 * The process-wide state that loading and bootstrapping the api changes, captured so a test can put it back.
 *
 * Booting the real application inside the unit suite is only safe if nothing it does outlives the test:
 * a stray error handler, static logger or Doctrine type would change how every later test behaves, and
 * CI runs the suite in random order. Each item below was found by reading what the boot does:
 *
 *  - error and exception handlers: Monolog's ErrorHandler and olcs-logging's E_USER_ERROR tolerance
 *    (vol-logging-mvc Module::onBootstrap)
 *  - the static Logger (the same bootstrap), and the static user id its UserId processor stamps on
 *    every record (Api Module::onBootstrap)
 *  - Doctrine's global type registry: Roave registers the custom types, and Api Module::onBootstrap
 *    gives encrypted_string its cipher
 *  - the default session manager (Cli Module::onBootstrap) and PHP's own session: the Cli module installs
 *    a save handler, and the SessionId log processor starts a session the first time anything is logged
 *  - autoloaders: the ModuleManager and the Cli module each register one
 *
 * Monolog also registers a shutdown function for fatal errors. That cannot be undone, and only acts
 * if the process dies of a fatal error, so it is left alone.
 */
final class GlobalState
{
    /**
     * @param array<string, Type> $typeInstances
     * @param array<int, string> $typeReverseIndex
     * @param list<callable> $autoloaders
     */
    private function __construct(
        private readonly mixed $errorHandler,
        private readonly mixed $exceptionHandler,
        private readonly mixed $logger,
        private readonly mixed $logUserId,
        private readonly array $typeInstances,
        private readonly array $typeReverseIndex,
        private readonly mixed $sessionManager,
        private readonly int $sessionStatus,
        private readonly string|false $sessionSaveHandler,
        private readonly array $autoloaders,
    ) {
    }

    public static function capture(): self
    {
        return new self(
            self::currentErrorHandler(),
            self::currentExceptionHandler(),
            self::staticProperty(Logger::class, 'logger'),
            self::staticProperty(UserId::class, 'userId'),
            self::typeRegistryProperty('instances'),
            self::typeRegistryProperty('instancesReverseIndex'),
            self::staticProperty(AbstractContainer::class, 'defaultManager'),
            session_status(),
            ini_get('session.save_handler'),
            spl_autoload_functions(),
        );
    }

    public function restore(): void
    {
        // A handler stack is unwound, not replaced, so PHPUnit sees exactly the handlers it installed
        for ($i = 0; self::currentErrorHandler() !== $this->errorHandler; $i++) {
            if ($i === 20) {
                throw new \LogicException('Could not unwind the error handlers the api installed');
            }

            restore_error_handler();
        }

        for ($i = 0; self::currentExceptionHandler() !== $this->exceptionHandler; $i++) {
            if ($i === 20) {
                throw new \LogicException('Could not unwind the exception handlers the api installed');
            }

            restore_exception_handler();
        }

        self::setStaticProperty(Logger::class, 'logger', $this->logger);
        self::setStaticProperty(UserId::class, 'userId', $this->logUserId);
        self::setStaticProperty(AbstractContainer::class, 'defaultManager', $this->sessionManager);
        $this->restoreSession();

        $registry = Type::getTypeRegistry();
        (new \ReflectionProperty(TypeRegistry::class, 'instances'))->setValue($registry, $this->typeInstances);
        (new \ReflectionProperty(TypeRegistry::class, 'instancesReverseIndex'))
            ->setValue($registry, $this->typeReverseIndex);

        foreach (spl_autoload_functions() as $autoloader) {
            if (!in_array($autoloader, $this->autoloaders, true)) {
                spl_autoload_unregister($autoloader);
            }
        }
    }

    private function restoreSession(): void
    {
        if ($this->sessionStatus !== PHP_SESSION_ACTIVE && session_status() === PHP_SESSION_ACTIVE) {
            // Removes the session file the 'files' handler wrote
            session_destroy();
            $_SESSION = [];
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_id('');

            if (is_string($this->sessionSaveHandler) && ini_get('session.save_handler') !== $this->sessionSaveHandler) {
                ini_set('session.save_handler', $this->sessionSaveHandler);
            }
        }
    }

    private static function currentErrorHandler(): mixed
    {
        $current = set_error_handler(static fn(): bool => false);
        restore_error_handler();

        return $current;
    }

    private static function currentExceptionHandler(): mixed
    {
        $current = set_exception_handler(static function (): void {
        });
        restore_exception_handler();

        return $current;
    }

    /**
     * @return array<array-key, mixed>
     */
    private static function typeRegistryProperty(string $name): array
    {
        $value = (new \ReflectionProperty(TypeRegistry::class, $name))->getValue(Type::getTypeRegistry());
        assert(is_array($value));

        return $value;
    }

    private static function staticProperty(string $class, string $name): mixed
    {
        return (new \ReflectionProperty($class, $name))->getValue();
    }

    private static function setStaticProperty(string $class, string $name, mixed $value): void
    {
        (new \ReflectionProperty($class, $name))->setValue(null, $value);
    }
}
