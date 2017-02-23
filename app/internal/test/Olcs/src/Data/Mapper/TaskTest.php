<?php

namespace OlcsTest\Data\Mapper;

use Common\Service\Helper\FlashMessengerHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper;
use Zend\Form\FormInterface;

/**
 * @covers \Olcs\Data\Mapper\Task
 */
class TaskTest extends MockeryTestCase
{
    /** @var  m\MockInterface | FlashMessengerHelperService */
    private $mockFlashMsg;

    public function setUp()
    {
        $this->mockFlashMsg = m::mock(FlashMessengerHelperService::class);
    }

    public function testMapFromResult()
    {
        static::assertNull(Mapper\Task::mapFromResult([]));
    }

    public function testMapFromErrors()
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

    public function testMapApiErrorsEmpty()
    {
        static::assertNull(
            Mapper\Task::mapApiErrors([], $this->mockFlashMsg)
        );
    }
}
