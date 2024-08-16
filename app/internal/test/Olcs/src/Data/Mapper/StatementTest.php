<?php

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\Statement as Sut;
use Laminas\Form\FormInterface;

/**
 * Statement Mapper Test
 */
class StatementTest extends MockeryTestCase
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
                    'requestorsContactDetails' => [
                        'person' => [
                            'forename' => 'Joe',
                            'familyName' => 'Smith'
                        ],
                        'address' => [
                            'addressLine1' => 'foo'
                        ]
                    ],
                    'someEntity' => [
                        'id' => 44
                    ]
                ],
                [
                    'fields' => [
                        'case' => 24,
                        'requestorsForename' => 'Joe',
                        'requestorsFamilyName' => 'Smith',
                        'someEntity' => 44,
                        'requestorsContactDetails' => [
                            'person' => [
                                'forename' => 'Joe',
                                'familyName' => 'Smith'
                            ],
                            'address' => [
                                'addressLine1' => 'foo'
                            ]
                        ],
                    ],
                    'requestorsAddress' => [
                        'addressLine1' => 'foo'
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
                    'requestorsContactDetails' => [
                        'person' => [
                            'forename' => 'Joe',
                            'familyName' => 'Smith'
                        ],
                        'address' => [
                            'addressLine1' => 'foo'
                        ]
                    ],
                    'someEntity' => [
                        'id' => 44
                    ]
                ],
                [
                    'fields' => [
                        'id' => 99,
                        'version' => 3,
                        'case' => 24,
                        'requestorsForename' => 'Joe',
                        'requestorsFamilyName' => 'Smith',
                        'someEntity' => 44,
                        'requestorsContactDetails' => [
                            'person' => [
                                'forename' => 'Joe',
                                'familyName' => 'Smith'
                            ],
                            'address' => [
                                'addressLine1' => 'foo'
                            ]
                        ],
                    ],
                    'requestorsAddress' => [
                        'addressLine1' => 'foo'
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
                'requestorsForename' => 'Joe2',
                'requestorsFamilyName' => 'Smith2',
            ],
            'requestorsAddress' => [
                'addressLine1' => 'foo'
            ],
        ];
        $expected = [
            'requestorsForename' => 'Joe2',
            'requestorsFamilyName' => 'Smith2',
            'requestorsContactDetails' => [
                'person' => [
                    'forename' => 'Joe2',
                    'familyName' => 'Smith2'
                ],
                'address' => [
                    'addressLine1' => 'foo'
                ]
            ]
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
