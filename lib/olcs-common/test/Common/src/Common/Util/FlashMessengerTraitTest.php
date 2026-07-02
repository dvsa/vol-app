<?php

declare(strict_types=1);

namespace CommonTest\Controller\Util;

use Common\Util\FlashMessengerTrait;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger as FlashMessengerPlugin;
use Mockery as m;

class FlashMessengerTraitTest extends m\Adapter\Phpunit\MockeryTestCase
{
    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = $this->getMockForTrait(
            FlashMessengerTrait::class,
            [],
            '',
            true,
            true,
            true,
            [
                'getFlashMessenger'
            ]
        );
    }

    /**
     * @group util
     * @group flash_messenger_trait
     */
    public function testGetFlashMessenger(): void
    {
        $this->sut = $this->getMockForTrait(
            FlashMessengerTrait::class,
            [],
            '',
            true,
            true,
            true,
            [
                'plugin'
            ]
        );

        $pluginManager = $this->createPartialMock(FlashMessengerPlugin::class, ['getNamespace']);

        $this->sut->expects($this->once())
            ->method('plugin')
            ->will($this->returnValue($pluginManager));

        $this->sut->getFlashMessenger();
    }

    /**
     * @group util
     * @group flash_messenger_trait
     */
    public function testAddInfoMessage(): void
    {
        $message = 'foo';

        $chainMock = $this->createPartialMock(FlashMessengerPlugin::class, ['addInfoMessage']);
        $chainMock->expects($this->once())
            ->method('addInfoMessage')
            ->with($message);

        $this->sut->expects($this->once())
            ->method('getFlashMessenger')
            ->will($this->returnValue($chainMock));

        $this->sut->addInfoMessage($message);
    }

    /**
     * @group util
     * @group flash_messenger_trait
     */
    public function testAddErrorMessage(): void
    {
        $message = 'foo';

        $chainMock = $this->createPartialMock(FlashMessengerPlugin::class, ['addErrorMessage']);
        $chainMock->expects($this->once())
            ->method('addErrorMessage')
            ->with($message);

        $this->sut->expects($this->once())
            ->method('getFlashMessenger')
            ->will($this->returnValue($chainMock));

        $this->sut->addErrorMessage($message);
    }

    /**
     * @group util
     * @group flash_messenger_trait
     */
    public function testAddSuccessMessage(): void
    {
        $message = 'foo';

        $chainMock = $this->createPartialMock(FlashMessengerPlugin::class, ['addSuccessMessage']);
        $chainMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with($message);

        $this->sut->expects($this->once())
            ->method('getFlashMessenger')
            ->will($this->returnValue($chainMock));

        $this->sut->addSuccessMessage($message);
    }

    /**
     * @group util
     * @group flash_messenger_trait
     */
    public function testAddWarningMessage(): void
    {
        $message = 'foo';

        $chainMock = $this->createPartialMock(FlashMessengerPlugin::class, ['addWarningMessage']);
        $chainMock->expects($this->once())
            ->method('addWarningMessage')
            ->with($message);

        $this->sut->expects($this->once())
            ->method('getFlashMessenger')
            ->will($this->returnValue($chainMock));

        $this->sut->addWarningMessage($message);
    }

    /**
     * @group util
     * @group flash_messenger_trait
     */
    public function testAddMessage(): void
    {
        $message = 'foo';
        $namespace = 'error';

        $chainMock = m::mock(FlashMessengerPlugin::class);
        $chainMock->expects('setNamespace')->with($namespace)->andReturnSelf();
        $chainMock->expects('addMessage')->with($message)->andReturnSelf();
        $chainMock->expects('setNamespace')->with('default')->andReturnSelf();

        $this->sut->expects($this->once())
            ->method('getFlashMessenger')
            ->will($this->returnValue($chainMock));

        $this->sut->addMessage($message, $namespace);
    }
}
