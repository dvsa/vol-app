<?php

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\Impounding as Sut;
use Laminas\Form\FormInterface;

/**
 * Impounding Mapper Test
 */
class ImpoundingTest extends MockeryTestCase
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
                    'venue' => 'something',
                    'venueOther' => null,
                    'legislationTypes' => ['id' => 7]
                ],
                [
                    'fields' => [
                        'case' => 24,
                        'venue' => 'something',
                        'legislationTypes' => 7,
                        'venueOther' => null
                    ],
                    'base' => [
                        'case' => 24
                    ]
                ]
            ],
            // edit
            [
                [
                    'id' => 99,
                    'version' => 3,
                    'case' => 24,
                    'venue' => null,
                    'venueOther' => 'somethingelse',
                    'legislationTypes' => ['id' => 7]
                ],
                [
                    'fields' => [
                        'id' => 99,
                        'version' => 3,
                        'case' => 24,
                        'venue' => 'other',
                        'venueOther' => 'somethingelse',
                        'legislationTypes' => 7
                    ],
                    'base' => [
                        'id' => 99,
                        'version' => 3,
                        'case' => 24
                    ]
                ]
            ],
        ];
    }

    public function testMapFromForm()
    {
        $inData = [
            'fields' => [
                'venue' => 'something',
                'legislationTypes' => 7
            ],
            'base' => [
                'id' => 99,
                'version' => 3,
                'case' => 24
            ]
        ];
        $expected = [
            'case' => 24,
            'venue' => 'something',
            'venueOther' => null,
            'legislationTypes' => 7,
            'case' => 24,
            'id' => 99,
            'version' => 3,
            'publish' => 'N',
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
