<?php

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\Stay as Sut;
use Laminas\Form\FormInterface;

/**
 * Stay Mapper Test
 */
class StayTest extends MockeryTestCase
{
    /**
     * @dataProvider mapFromResultDataProvider
     *
     * @param $inData
     * @param $expected
     */
    public function testMapFromResult($inData, $expected)
    {
        $this->assertEquals($expected, Sut::mapFromResult($inData));
    }

    public function mapFromResultDataProvider()
    {
        return [
            // add
            [
                [
                    'case' => 24,
                    'withdrawnDate' => 'foo',
                    'someEntity' => [
                        'id' => 44
                    ],
                    'dvsaNotified' => 'Y'
                ],
                [
                    'fields' => [
                        'case' => 24,
                        'withdrawnDate' => 'foo',
                        'isWithdrawn' => 'Y',
                        'someEntity' => 44,
                        'dvsaNotified' => 'Y'
                    ]
                ]
            ],
            // edit
            [
                [
                    'id' => 99,
                    'version' => 3,
                    'case' => 24,
                    'withdrawnDate' => 'foo',
                    'someEntity' => [
                        'id' => 44
                    ],
                    'dvsaNotified' => 'Y'
                ],
                [
                    'fields' => [
                        'id' => 99,
                        'version' => 3,
                        'case' => 24,
                        'withdrawnDate' => 'foo',
                        'isWithdrawn' => 'Y',
                        'someEntity' => 44,
                        'dvsaNotified' => 'Y'
                    ]
                ]
            ],
        ];
    }

    public function testMapFromForm()
    {
        $inData = [
            'fields' => [
                'withdrawnDate' => 'foo',
                'isWithdrawn' => 'N',
                'dvsaNotified' => 'Y',
            ],
        ];
        $expected = [
            'isWithdrawn' => 'N',
            'withdrawnDate' => null,
            'dvsaNotified' => 'Y'
        ];

        $this->assertEquals($expected, Sut::mapFromForm($inData));
    }

    public function testMapFromErrors()
    {
        $mockForm = m::mock(FormInterface::class);
        $errors = ['field' => 'data'];

        $this->assertEquals($errors, Sut::mapFromErrors($mockForm, $errors));
    }
}
