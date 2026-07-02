<?php

/**
 * Flash Messenger Helper Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Service\Helper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Service\Helper\FlashMessengerHelperService;

/**
 * Flash Messenger Helper Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FlashMessengerHelperServiceTest extends MockeryTestCase
{
    /**
     * Holds the SUT
     *
     * @var \Common\Service\Helper\FlashMessengerHelperService
     */
    private $sut;

    private $mockFlashMessenger;

    /**
     * Setup the sut
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->mockFlashMessenger = m::mock(\Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger::class);

        $this->sut = new FlashMessengerHelperService($this->mockFlashMessenger);
    }

    /**
     * @group helper_service
     * @group flash_messenger_helper_service
     */
    public function testAddErrorMessage(): void
    {
        $message = 'foo';

        $this->mockFlashMessenger->shouldReceive('addErrorMessage')
            ->once()
            ->with($message)
            ->andReturnSelf();

        $this->assertSame($this->mockFlashMessenger, $this->sut->addErrorMessage($message));
    }

    /**
     * @group helper_service
     * @group flash_messenger_helper_service
     */
    public function testAddProminentErrorMessage(): void
    {
        $message = 'foo';

        $this->mockFlashMessenger->shouldReceive('getNamespace')->once()->andReturn('default');
        $this->mockFlashMessenger->shouldReceive('setNamespace')->once()->with('prominent-error');
        $this->mockFlashMessenger->shouldReceive('addMessage')->once()->with('foo');
        $this->mockFlashMessenger->shouldReceive('setNamespace')->once()->with('default');

        $this->sut->addProminentErrorMessage($message);
    }

    /**
     * @group helper_service
     * @group flash_messenger_helper_service
     */
    public function testAddSuccessMessage(): void
    {
        $message = 'foo';

        $this->mockFlashMessenger->shouldReceive('addSuccessMessage')
            ->once()
            ->with($message)
            ->andReturnSelf();

        $this->assertSame($this->mockFlashMessenger, $this->sut->addSuccessMessage($message));
    }

    /**
     * @group helper_service
     * @group flash_messenger_helper_service
     */
    public function testAddWarningMessage(): void
    {
        $message = 'foo';

        $this->mockFlashMessenger->shouldReceive('addWarningMessage')
            ->once()
            ->with($message)
            ->andReturnSelf();

        $this->assertSame($this->mockFlashMessenger, $this->sut->addWarningMessage($message));
    }

    /**
     * @group helper_service
     * @group flash_messenger_helper_service
     */
    public function testAddInfoMessage(): void
    {
        $message = 'foo';

        $this->mockFlashMessenger->shouldReceive('addInfoMessage')
            ->once()
            ->with($message)
            ->andReturnSelf();

        $this->assertSame($this->mockFlashMessenger, $this->sut->addInfoMessage($message));
    }

    public function testCurrentMessages(): void
    {
        $this->sut->addCurrentInfoMessage('info message');
        $this->sut->addCurrentInfoMessage('info message 2');
        $this->sut->addCurrentErrorMessage('error message');
        $this->sut->addCurrentWarningMessage('warning message');
        $this->sut->addCurrentSuccessMessage('success message');
        $this->sut->addCurrentMessage('success', 'success message 2');
        $this->sut->addCurrentUnknownError();

        $this->assertEquals(['info message', 'info message 2'], $this->sut->getCurrentMessages('info'));
        $this->assertEquals(['success message', 'success message 2'], $this->sut->getCurrentMessages('success'));
        $this->assertEquals(['error message', 'unknown-error'], $this->sut->getCurrentMessages('error'));
        $this->assertEquals(['warning message'], $this->sut->getCurrentMessages('warning'));
    }

    public function testAddUnknownError(): void
    {
        $this->mockFlashMessenger->shouldReceive('addErrorMessage')
            ->once()
            ->with('unknown-error')
            ->andReturnSelf();

        $this->assertSame($this->mockFlashMessenger, $this->sut->addUnknownError());
    }

    public function testAddConflictError(): void
    {
        $this->mockFlashMessenger->shouldReceive('addErrorMessage')
            ->once()
            ->with('conflict-error')
            ->andReturnSelf();

        $this->assertSame($this->mockFlashMessenger, $this->sut->addConflictError());
    }
}
