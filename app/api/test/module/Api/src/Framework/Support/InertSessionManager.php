<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Framework\Support;

use Laminas\EventManager\EventManagerInterface;
use Laminas\Session\Config\ConfigInterface;
use Laminas\Session\Config\StandardConfig;
use Laminas\Session\ManagerInterface;
use Laminas\Session\SaveHandler\SaveHandlerInterface;
use Laminas\Session\Storage\ArrayStorage;
use Laminas\Session\Storage\StorageInterface;
use Laminas\Session\ValidatorChain;

/**
 * A session manager that never starts a PHP session, for the logger's SessionId processor.
 *
 * olcs-logging's SessionId processor calls start() on its session manager for every log record, and stamps
 * the record with getId(). Inside PHPUnit no headers have been sent, so the real manager really starts a PHP
 * session — writing a session file and leaving session_status() active for every later test. This one only
 * reports a fixed id, which also keeps log records the same from run to run.
 */
final class InertSessionManager implements ManagerInterface
{
    public const string ID = 'framework-test-session';

    private ConfigInterface $config;
    private StorageInterface $storage;
    private ?SaveHandlerInterface $saveHandler = null;
    private ?EventManagerInterface $validatorChain = null;
    private string $name = 'PHPSESSID';

    public function __construct()
    {
        $this->config = new StandardConfig();
        $this->storage = new ArrayStorage();
    }

    #[\Override]
    public function setConfig(ConfigInterface $config): self
    {
        $this->config = $config;

        return $this;
    }

    #[\Override]
    public function getConfig(): ConfigInterface
    {
        return $this->config;
    }

    #[\Override]
    public function setStorage(StorageInterface $storage): self
    {
        $this->storage = $storage;

        return $this;
    }

    #[\Override]
    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    #[\Override]
    public function setSaveHandler(SaveHandlerInterface $saveHandler): self
    {
        $this->saveHandler = $saveHandler;

        return $this;
    }

    #[\Override]
    public function getSaveHandler(): ?SaveHandlerInterface
    {
        return $this->saveHandler;
    }

    #[\Override]
    public function sessionExists(): bool
    {
        return false;
    }

    #[\Override]
    public function start(): void
    {
    }

    #[\Override]
    public function destroy(): void
    {
    }

    #[\Override]
    public function writeClose(): void
    {
    }

    #[\Override]
    public function setName($name): self
    {
        $this->name = (string) $name;

        return $this;
    }

    #[\Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[\Override]
    public function setId($id): self
    {
        return $this;
    }

    #[\Override]
    public function getId(): string
    {
        return self::ID;
    }

    #[\Override]
    public function regenerateId(): self
    {
        return $this;
    }

    #[\Override]
    public function rememberMe($ttl = null): void
    {
    }

    #[\Override]
    public function forgetMe(): void
    {
    }

    #[\Override]
    public function expireSessionCookie(): void
    {
    }

    #[\Override]
    public function setValidatorChain(EventManagerInterface $chain): self
    {
        $this->validatorChain = $chain;

        return $this;
    }

    #[\Override]
    public function getValidatorChain(): EventManagerInterface
    {
        return $this->validatorChain ??= new ValidatorChain($this->storage);
    }

    #[\Override]
    public function isValid(): bool
    {
        return true;
    }
}
