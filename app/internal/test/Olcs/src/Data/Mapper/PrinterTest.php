<?php

/**
 * Printer mapper test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\Printer as Sut;
use Laminas\Form\FormInterface;

/**
 * Printer mapper test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PrinterTest extends MockeryTestCase
{
    public function testMapFromResult()
    {
        $data = ['foo' => 'bar'];
        $expected = ['printer-details' => ['foo' => 'bar']];
        $this->assertEquals($expected, Sut::mapFromResult($data));
    }

    public function testMapFromForm()
    {
        $data = ['printer-details' => ['foo' => 'bar']];
        $expected = ['foo' => 'bar'];

        $this->assertEquals($expected, Sut::mapFromForm($data));
    }

    public function testMapFromErrors()
    {
        $errors = [
            'messages' => [
                'printerName' => 'foo',
                'description' => 'bar',
                'global' => 'baz'
            ]
        ];

        $expected = [
            'messages' => ['global' => 'baz']
        ];

        $formErrors = [
            'printer-details' => [
                'printerName' => 'foo',
                'description' => 'bar'
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
