<?php

/**
 * SystemParameter mapper test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\SystemParameter as Sut;
use Laminas\Form\FormInterface;

/**
 * SystemParameter mapper test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class SystemParameterTest extends MockeryTestCase
{
    public function testMapFromResult()
    {
        $data = [
            'foo' => 'bar',
            'id' => 1
        ];
        $expected = [
            'system-parameter-details' => [
                'foo' => 'bar',
                'id' => 1,
                'hiddenId' => 1
            ]
        ];
        $this->assertEquals($expected, Sut::mapFromResult($data));
    }

    public function testMapFromForm()
    {
        $data = ['system-parameter-details' => ['foo' => 'bar', 'hiddenId' => 1]];
        $expected = ['foo' => 'bar', 'id' => 1];

        $this->assertEquals($expected, Sut::mapFromForm($data));
    }

    public function testMapFromErrors()
    {
        $errors = [
            'messages' => [
                'id' => 'foo',
                'paramValue' => 'bar',
                'description' => 'cake',
                'global' => 'baz'
            ]
        ];

        $expected = [
            'messages' => ['global' => 'baz']
        ];

        $formErrors = [
            'system-parameter-details' => [
                'id' => 'foo',
                'paramValue' => 'bar',
                'description' => 'cake',
            ]
        ];

        $mockForm = m::mock(FormInterface::class)
            ->shouldReceive('setMessages')
            ->with($formErrors)
            ->once()
            ->getMock();

        $this->assertEquals($expected, Sut::mapFromErrors($mockForm, $errors));
    }
}
