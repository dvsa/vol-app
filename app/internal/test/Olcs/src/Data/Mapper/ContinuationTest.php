<?php

declare(strict_types=1);

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\Continuation as Sut;
use Laminas\Form\Form;

/**
 * Continuation Mapper Test
 */
class ContinuationTest extends MockeryTestCase
{
    public function testMapFromErrors(): void
    {
        $errors = [
            'totAuthVehicles' => [['error1']],
            'general' => ['error2'],
        ];
        $expected = [
            'general' => ['error2']
        ];
        $messages = [
            'fields' => ['totalVehicleAuthorisation' => ['error1']]
        ];
        $mockForm = m::mock(Form::class)->makePartial()
            ->shouldReceive('setMessages')
            ->with($messages)
            ->once()
            ->getMock();

        $this->assertEquals($expected, Sut::mapFromErrors($mockForm, $errors));
    }
}
