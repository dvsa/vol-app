<?php

declare(strict_types=1);

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
     *
     * @param $inData
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('mapFromResultDataProvider')]
    public function testMapFromResult(mixed $inData, mixed $expected): void
    {
        $this->assertEquals($expected, Sut::mapFromResult($inData));
    }

    public static function mapFromResultDataProvider(): array
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

    public function testMapFromForm(): void
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

    public function testMapFromErrors(): void
    {
        $mockForm = m::mock(FormInterface::class);
        $errors = ['field' => 'data'];

        $this->assertEquals($errors, Sut::mapFromErrors($mockForm, $errors));
    }
}
