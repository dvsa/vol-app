<?php

/**
 * Team mapper test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\Team as Sut;
use Laminas\Form\FormInterface;

/**
 * Team mapper test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TeamTest extends MockeryTestCase
{
    public function testMapFromResult()
    {
        $data = [
            'foo' => 'bar',
            'teamPrinters' => [
                [
                    'printer' => 'not defult',
                    'user' => 'baz',
                    'subCategory' => null
                ],
                [
                    'printer' => 'cake',
                    'user' => null,
                    'subCategory' => null
                ]
            ]
        ];
        $expected = [
            'team-details' => [
                'foo' => 'bar',
                'defaultPrinter' => 'cake',
                'teamPrinters' => [
                    [
                        'printer' => 'not defult',
                        'user' => 'baz',
                        'subCategory' => null
                    ],
                    [
                        'printer' => 'cake',
                        'user' => null,
                        'subCategory' => null
                    ]
                ]
            ]
        ];
        $this->assertEquals($expected, Sut::mapFromResult($data));
    }

    public function testMapFromForm()
    {
        $data = ['team-details' => ['foo' => 'bar']];
        $expected = ['foo' => 'bar'];

        $this->assertEquals($expected, Sut::mapFromForm($data));
    }

    public function testMapFromErrors()
    {
        $errors = [
            'messages' => [
                'name' => 'foo',
                'description' => 'bar',
                'trafficArea' => 'cake',
                'global' => 'baz'
            ]
        ];

        $expected = [
            'messages' => ['global' => 'baz']
        ];

        $formErrors = [
            'team-details' => [
                'name' => 'foo',
                'description' => 'bar',
                'trafficArea' => 'cake',
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
