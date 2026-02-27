<?php

declare(strict_types=1);

namespace OlcsTest\Data\Mapper;

use Common\Service\Helper\FlashMessengerHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper;
use Laminas\Form\FormInterface;

#[\PHPUnit\Framework\Attributes\CoversClass(\Olcs\Data\Mapper\Task::class)]
class TaskTest extends MockeryTestCase
{
    /** @var  m\MockInterface | FlashMessengerHelperService */
    private $mockFlashMsg;

    public function setUp(): void
    {
        $this->mockFlashMsg = m::mock(FlashMessengerHelperService::class);
    }

    public function testMapFromResult(): void
    {
        static::assertNull(Mapper\Task::mapFromResult([]));
    }

    public function testMapFromErrors(): void
    {
        $errors = [
            'detach_error' => 'unit_DETACH_ERR_MSG',
        ];

        $formErrs = [];

        $mockForm = m::mock(FormInterface::class);
        $mockForm->shouldReceive('setMessages')->once()->with($formErrs);

        $this->mockFlashMsg
            ->shouldReceive('addCurrentErrorMessage')->once()->with('unit_DETACH_ERR_MSG');

        Mapper\Task::mapFormErrors($errors, $mockForm, $this->mockFlashMsg);
    }

    public function testMapApiErrorsEmpty(): void
    {
        static::assertNull(
            Mapper\Task::mapApiErrors([], $this->mockFlashMsg)
        );
    }
}
