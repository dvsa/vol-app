<?php

declare(strict_types=1);

namespace CommonTest\Controller\Util;

use Common\Util\FlashMessengerTrait;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger as FlashMessengerPlugin;
use Mockery as m;

final class FlashMessengerTraitTest extends m\Adapter\Phpunit\MockeryTestCase
{
    /**
     * Host the trait in an anonymous class whose getFlashMessenger() returns the
     * supplied flash messenger, so the trait's add*Message() methods can be exercised.
     */
    private function sutWithFlashMessenger(object $flashMessenger): object
    {
        return new class ($flashMessenger) {
            use FlashMessengerTrait;

            public function __construct(private readonly object $flashMessenger)
            {
            }

            public function getFlashMessenger()
            {
                return $this->flashMessenger;
            }
        };
    }

    #[\PHPUnit\Framework\Attributes\Group('util')]
    #[\PHPUnit\Framework\Attributes\Group('flash_messenger_trait')]
    public function testGetFlashMessenger(): void
    {
        $pluginManager = m::mock(FlashMessengerPlugin::class);

        $sut = new class ($pluginManager) {
            use FlashMessengerTrait;

            public int $pluginCalls = 0;

            public function __construct(private readonly object $pluginManager)
            {
            }

            public function plugin($name = null, ?array $options = null)
            {
                $this->pluginCalls++;
                return $this->pluginManager;
            }
        };

        $this->assertSame($pluginManager, $sut->getFlashMessenger());
        $this->assertSame(1, $sut->pluginCalls);
    }

    #[\PHPUnit\Framework\Attributes\Group('util')]
    #[\PHPUnit\Framework\Attributes\Group('flash_messenger_trait')]
    public function testAddInfoMessage(): void
    {
        $message = 'foo';

        $chainMock = m::mock(FlashMessengerPlugin::class);
        $chainMock->expects('addInfoMessage')->with($message);

        $this->sutWithFlashMessenger($chainMock)->addInfoMessage($message);
    }

    #[\PHPUnit\Framework\Attributes\Group('util')]
    #[\PHPUnit\Framework\Attributes\Group('flash_messenger_trait')]
    public function testAddErrorMessage(): void
    {
        $message = 'foo';

        $chainMock = m::mock(FlashMessengerPlugin::class);
        $chainMock->expects('addErrorMessage')->with($message);

        $this->sutWithFlashMessenger($chainMock)->addErrorMessage($message);
    }

    #[\PHPUnit\Framework\Attributes\Group('util')]
    #[\PHPUnit\Framework\Attributes\Group('flash_messenger_trait')]
    public function testAddSuccessMessage(): void
    {
        $message = 'foo';

        $chainMock = m::mock(FlashMessengerPlugin::class);
        $chainMock->expects('addSuccessMessage')->with($message);

        $this->sutWithFlashMessenger($chainMock)->addSuccessMessage($message);
    }

    #[\PHPUnit\Framework\Attributes\Group('util')]
    #[\PHPUnit\Framework\Attributes\Group('flash_messenger_trait')]
    public function testAddWarningMessage(): void
    {
        $message = 'foo';

        $chainMock = m::mock(FlashMessengerPlugin::class);
        $chainMock->expects('addWarningMessage')->with($message);

        $this->sutWithFlashMessenger($chainMock)->addWarningMessage($message);
    }

    #[\PHPUnit\Framework\Attributes\Group('util')]
    #[\PHPUnit\Framework\Attributes\Group('flash_messenger_trait')]
    public function testAddMessage(): void
    {
        $message = 'foo';
        $namespace = 'error';

        $chainMock = m::mock(FlashMessengerPlugin::class);
        $chainMock->expects('setNamespace')->with($namespace)->andReturnSelf();
        $chainMock->expects('addMessage')->with($message)->andReturnSelf();
        $chainMock->expects('setNamespace')->with('default')->andReturnSelf();

        $this->sutWithFlashMessenger($chainMock)->addMessage($message, $namespace);
    }
}
