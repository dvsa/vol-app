<?php

declare(strict_types=1);

namespace CommonTest\Service\FlashMessenger;

use Common\Service\FlashMessenger\LaminasSessionFlashMessenger;
use Laminas\Session\Container;
use Laminas\Stdlib\SplQueue;
use PHPUnit\Framework\TestCase;

final class LaminasSessionFlashMessengerTest extends TestCase
{
    private Container $container;

    private LaminasSessionFlashMessenger $sut;

    #[\Override]
    protected function setUp(): void
    {
        // Use a unique container name per test to avoid state leaking between
        // tests via the shared default session manager/storage.
        $this->container = new Container(uniqid('flash'));
        $this->sut = new LaminasSessionFlashMessenger($this->container);
    }

    /**
     * addMessage() writes into the session Container so the message survives
     * for the *next* request (standard flash-messenger semantics). It is not
     * expected to be readable via getMessages() during the same request.
     */
    public function testAddMessageStoresMessageInDefaultNamespaceOfContainer(): void
    {
        $this->sut->addMessage('default message');

        $this->assertInstanceOf(
            SplQueue::class,
            $this->container->{LaminasSessionFlashMessenger::NAMESPACE_DEFAULT},
        );
        $this->assertSame(
            ['default message'],
            $this->container->{LaminasSessionFlashMessenger::NAMESPACE_DEFAULT}->toArray(),
        );
    }

    public function testAddMessageStoresMessageInGivenNamespaceOfContainer(): void
    {
        $this->sut->addMessage('error message', LaminasSessionFlashMessenger::NAMESPACE_ERROR);

        $this->assertObjectNotHasProperty(LaminasSessionFlashMessenger::NAMESPACE_DEFAULT, $this->container);
        $this->assertSame(
            ['error message'],
            $this->container->{LaminasSessionFlashMessenger::NAMESPACE_ERROR}->toArray(),
        );
    }

    public function testAddMessageUsesCurrentNamespaceWhenNoneProvided(): void
    {
        $this->sut->setNamespace(LaminasSessionFlashMessenger::NAMESPACE_WARNING);
        $this->sut->addMessage('warning message');

        $this->assertSame(
            ['warning message'],
            $this->container->{LaminasSessionFlashMessenger::NAMESPACE_WARNING}->toArray(),
        );
    }

    public function testAddMessageAppendsMultipleMessagesInOrder(): void
    {
        $this->sut->addMessage('first');
        $this->sut->addMessage('second');
        $this->sut->addMessage('third');

        $this->assertSame(
            ['first', 'second', 'third'],
            $this->container->{LaminasSessionFlashMessenger::NAMESPACE_DEFAULT}->toArray(),
        );
    }

    public function testAddMessageKeepsNamespacesIndependent(): void
    {
        $this->sut->addMessage('default message');
        $this->sut->addMessage('success message', LaminasSessionFlashMessenger::NAMESPACE_SUCCESS);

        $this->assertSame(
            ['default message'],
            $this->container->{LaminasSessionFlashMessenger::NAMESPACE_DEFAULT}->toArray(),
        );
        $this->assertSame(
            ['success message'],
            $this->container->{LaminasSessionFlashMessenger::NAMESPACE_SUCCESS}->toArray(),
        );
    }

    public function testAddMessageReturnsSelfForChaining(): void
    {
        $result = $this->sut->addMessage('default message');

        $this->assertSame($this->sut, $result);
    }

    /**
     * On the first call, addMessage() pulls any messages already present in
     * the container (e.g. flashed from a previous request) into the internal
     * "read" buffer exposed via getMessages(), and clears the container
     * namespace before pushing the newly added message. This mirrors
     * standard flash messenger "current vs next request" semantics.
     */
    public function testAddMessagePullsPreExistingContainerMessagesBeforeAddingNewOne(): void
    {
        $existing = new SplQueue();
        $existing->push('previously flashed message');
        $this->container->{LaminasSessionFlashMessenger::NAMESPACE_DEFAULT} = $existing;

        $this->sut->addMessage('newly added message');

        // Pre-existing message is now readable this request...
        $this->assertSame(['previously flashed message'], $this->sut->getMessages());
        // ...while the newly added message is queued in the container for
        // the next request only.
        $this->assertSame(
            ['newly added message'],
            $this->container->{LaminasSessionFlashMessenger::NAMESPACE_DEFAULT}->toArray(),
        );
    }

    public function testAddSuccessMessageAddsToSuccessNamespaceOfContainer(): void
    {
        $this->sut->addSuccessMessage('success message');

        $this->assertSame(
            ['success message'],
            $this->container->{LaminasSessionFlashMessenger::NAMESPACE_SUCCESS}->toArray(),
        );
    }

    public function testAddErrorMessageAddsToErrorNamespaceOfContainer(): void
    {
        $this->sut->addErrorMessage('error message');

        $this->assertSame(
            ['error message'],
            $this->container->{LaminasSessionFlashMessenger::NAMESPACE_ERROR}->toArray(),
        );
    }

    public function testAddWarningMessageAddsToWarningNamespaceOfContainer(): void
    {
        $this->sut->addWarningMessage('warning message');

        $this->assertSame(
            ['warning message'],
            $this->container->{LaminasSessionFlashMessenger::NAMESPACE_WARNING}->toArray(),
        );
    }

    public function testAddInfoMessageAddsToInfoNamespaceOfContainer(): void
    {
        $this->sut->addInfoMessage('info message');

        $this->assertSame(
            ['info message'],
            $this->container->{LaminasSessionFlashMessenger::NAMESPACE_INFO}->toArray(),
        );
    }
}
