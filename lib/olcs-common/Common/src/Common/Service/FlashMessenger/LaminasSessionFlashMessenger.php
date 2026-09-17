<?php

namespace Common\Service\FlashMessenger;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Laminas\Session\Container;
use Laminas\Stdlib\SplQueue;

/**
 * Flash Messenger implementation using Laminas\Session\Container
 *
 * @template-implements IteratorAggregate<array-key, string>
 * @psalm-type MessageList = SplQueue<array-key, string>
 */
class LaminasSessionFlashMessenger implements FlashMessengerInterface, IteratorAggregate, Countable
{
    public const string NAMESPACE_DEFAULT = 'default';

    public const string NAMESPACE_SUCCESS = 'success';

    public const string NAMESPACE_WARNING = 'warning';

    public const string NAMESPACE_ERROR = 'error';

    public const string NAMESPACE_INFO = 'info';

    protected array $messages = [];

    protected bool $messageAdded = false;

    protected string $namespace = self::NAMESPACE_DEFAULT;

    public function __construct(private Container $container)
    {
    }

    #[\Override]
    public function addMessage(string $message, ?string $namespace = null, int $hops = 1): self
    {
        $container = $this->container;

        if (null === $namespace) {
            $namespace = $this->getNamespace();
        }

        if (! $this->messageAdded) {
            $this->getMessagesFromContainer();
            $container->setExpirationHops($hops);
        }

        if (
            ! isset($container->{$namespace})
            || ! $container->{$namespace} instanceof SplQueue
        ) {
            $container->{$namespace} = new SplQueue();
        }

        $container->{$namespace}->push($message);

        $this->messageAdded = true;
        return $this;
    }

    #[\Override]
    public function addSuccessMessage(string $message): self
    {
        $this->addMessage($message, self::NAMESPACE_SUCCESS);
        return $this;
    }

    #[\Override]
    public function addErrorMessage(string $message): self
    {
        $this->addMessage($message, self::NAMESPACE_ERROR);
        return $this;
    }

    #[\Override]
    public function addWarningMessage(string $message): self
    {
        $this->addMessage($message, self::NAMESPACE_WARNING);
        return $this;
    }

    #[\Override]
    public function addInfoMessage(string $message): self
    {
        $this->addMessage($message, self::NAMESPACE_INFO);
        return $this;
    }

    #[\Override]
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    #[\Override]
    public function setNamespace(string $namespace = self::NAMESPACE_DEFAULT): self
    {
        $this->namespace = $namespace;

        return $this;
    }

    protected function getMessagesFromContainer(): void
    {
        if (!empty($this->messages) || $this->messageAdded) {
            return;
        }

        $container = $this->container;

        $namespaces = [];
        foreach ($container as $namespace => $messages) {
            $this->messages[$namespace] = $messages;
            $namespaces[]               = $namespace;
        }


        foreach ($namespaces as $namespace) {
            unset($container->{$namespace});
        }
    }

    #[\Override]
    public function hasMessages(?string $namespace = null): bool
    {
        if (null === $namespace) {
            $namespace = $this->getNamespace();
        }

        $this->getMessagesFromContainer();

        return isset($this->messages[$namespace]);
    }

    #[\Override]
    public function getMessages(?string $namespace = null): array
    {
        if (null === $namespace) {
            $namespace = $this->getNamespace();
        }

        if ($this->hasMessages($namespace)) {
            return $this->messages[$namespace]->toArray();
        }

        return [];
    }

    /**
     * This method exists to proxy existing calls that existed in the original Laminas Flash Messenger implementation.
     */
    #[\Override]
    public function getMessagesFromNamespace(string $namespace): array
    {
        return $this->getMessages($namespace);
    }

    #[\Override]
    public function getIterator(): ArrayIterator
    {
        if ($this->hasMessages()) {
            return new ArrayIterator($this->getMessages());
        }

        return new ArrayIterator();
    }

    #[\Override]
    public function count(): int
    {
        if ($this->hasMessages()) {
            return count($this->getMessages());
        }

        return 0;
    }
}
