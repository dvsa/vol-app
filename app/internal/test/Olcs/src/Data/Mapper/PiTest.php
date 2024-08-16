<?php

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\Pi as Sut;
use Laminas\Form\FormInterface;

/**
 * Pi Test
 */
class PiTest extends MockeryTestCase
{
    public function testMapFromErrors()
    {
        $mockForm = m::mock(FormInterface::class);
        $errors = ['field' => 'data', 'messages' => []];

        $this->assertEquals($errors, Sut::mapFromErrors($mockForm, $errors));
    }

    public function testMapFromErrorsDecisionDateBeforeHearing()
    {
        $mockForm = m::mock(FormInterface::class);
        $mockForm->shouldReceive('get->get->setMessages')
            ->with(['Decision date must be after or the same as the PI hearing date 26/05/2017'])
            ->once();

        $errors = ['messages' => ['DECISION_DATE_BEFORE_HEARING_DATE' => '2017-05-26']];
        $this->assertEquals(['messages' => []], Sut::mapFromErrors($mockForm, $errors));
    }
}
