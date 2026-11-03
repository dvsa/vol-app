<?php

/**
 * Flash Messenger Helper Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Service\Helper;

use Common\Service\FlashMessenger\FlashMessengerInterface;

/**
 * Flash Messenger Helper Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FlashMessengerHelperService
{
    public const NAMESPACE_PROMINENT_ERROR = 'prominent-error';

    protected $currentMessages = [
        'default' => [],
        'success' => [],
        'error' => [],
        'warning' => [],
        'info' => []
    ];

    /** @var FlashMessengerInterface */
    protected $flashMessenger;


    public function __construct(
        FlashMessengerInterface $flashMessenger
    ) {
        $this->flashMessenger = $flashMessenger;
    }

    /**
     * @psalm-param 'success message 2' $message
     */
    public function addCurrentMessage($namespace, $message): void
    {
        $this->currentMessages[$namespace][] = $message;
    }

    public function getCurrentMessages($namespace)
    {
        return $this->currentMessages[$namespace];
    }

    /**
     * @psalm-param 'success message' $message
     */
    public function addCurrentSuccessMessage($message): void
    {
        $this->addCurrentMessage('success', $message);
    }

    public function addCurrentErrorMessage($message): void
    {
        $this->addCurrentMessage('error', $message);
    }

    /**
     * @psalm-param 'warning message' $message
     */
    public function addCurrentWarningMessage($message): void
    {
        $this->addCurrentMessage('warning', $message);
    }

    /**
     * @psalm-param 'info message 2'|'info message' $message
     */
    public function addCurrentInfoMessage($message): void
    {
        $this->addCurrentMessage('info', $message);
    }

    /**
     * Add a success message
     *
     * @param string $message
     * @return FlashMessengerInterface
     */
    public function addSuccessMessage($message)
    {
        return $this->flashMessenger->addSuccessMessage($message);
    }

    /**
     * Add a error message
     *
     * @param string $message
     * @return FlashMessengerInterface
     */
    public function addErrorMessage($message)
    {
        return $this->flashMessenger->addErrorMessage($message);
    }

    public function addProminentErrorMessage($message): static
    {
        $namespace = $this->flashMessenger->getNamespace();

        $this->flashMessenger->setNamespace(self::NAMESPACE_PROMINENT_ERROR);
        $this->flashMessenger->addMessage($message);

        $this->flashMessenger->setNamespace($namespace);

        return $this;
    }

    /**
     * Add a warning message
     *
     * @param string $message
     * @return FlashMessengerInterface
     */
    public function addWarningMessage($message)
    {
        return $this->flashMessenger->addWarningMessage($message);
    }


    /**
     * Add a info message
     *
     * @param string $message
     * @return FlashMessengerInterface
     */
    public function addInfoMessage($message)
    {
        return $this->flashMessenger->addInfoMessage($message);
    }

    public function addUnknownError()
    {
        return $this->addErrorMessage('unknown-error');
    }

    public function addConflictError()
    {
        return $this->addErrorMessage('conflict-error');
    }

    public function addCurrentUnknownError(): void
    {
        $this->addCurrentErrorMessage('unknown-error');
    }

    public function clearCurrentMessagesFromContainer(): void
    {
        $this->flashMessenger->clearCurrentMessagesFromContainer();
    }

    public function offsetSet(string $key, mixed $value): void
    {
        $this->flashMessenger->getContainer()->offsetSet($key, $value);
    }
}
