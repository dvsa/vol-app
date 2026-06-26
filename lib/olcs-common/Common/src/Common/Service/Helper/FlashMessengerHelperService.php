<?php

/**
 * Flash Messenger Helper Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Service\Helper;

use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;

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

    /** @var FlashMessenger */
    protected $flashMessenger;

    /**
     * Create service instance
     *
     *
     * @return FlashMessengerHelperService
     */
    public function __construct(
        FlashMessenger $flashMessenger
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
     * @return \Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger
     */
    public function addSuccessMessage($message)
    {
        return $this->getFlashMessenger()->addSuccessMessage($message);
    }

    /**
     * Add a error message
     *
     * @param string $message
     * @return \Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger
     */
    public function addErrorMessage($message)
    {
        return $this->getFlashMessenger()->addErrorMessage($message);
    }

    public function addProminentErrorMessage($message): static
    {
        $namespace = $this->getFlashMessenger()->getNamespace();

        $this->getFlashMessenger()->setNamespace(self::NAMESPACE_PROMINENT_ERROR);
        $this->getFlashMessenger()->addMessage($message);

        $this->getFlashMessenger()->setNamespace($namespace);

        return $this;
    }

    /**
     * Add a warning message
     *
     * @param string $message
     * @return \Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger
     */
    public function addWarningMessage($message)
    {
        return $this->getFlashMessenger()->addWarningMessage($message);
    }


    /**
     * Add a info message
     *
     * @param string $message
     * @return \Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger
     */
    public function addInfoMessage($message)
    {
        return $this->getFlashMessenger()->addInfoMessage($message);
    }

    /**
     * Get the flash messenger
     *
     * @return \Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger
     */
    protected function getFlashMessenger()
    {
        return $this->flashMessenger;
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
}
