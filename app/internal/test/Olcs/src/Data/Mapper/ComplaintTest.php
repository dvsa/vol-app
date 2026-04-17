<?php

declare(strict_types=1);

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\Complaint as Sut;
use Laminas\Form\FormInterface;

/**
 * Complaint Mapper Test
 */
class ComplaintTest extends MockeryTestCase
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
                    'complainantContactDetails' => [
                        'person' => [
                            'forename' => 'Joe',
                            'familyName' => 'Smith'
                        ]
                    ],
                    'someEntity' => [
                        'id' => 44
                    ]
                ],
                [
                    'fields' => [
                        'case' => 24,
                        'complainantForename' => 'Joe',
                        'complainantFamilyName' => 'Smith',
                        'someEntity' => 44,
                        'complainantContactDetails' => [
                            'person' => [
                                'forename' => 'Joe',
                                'familyName' => 'Smith'
                            ]
                        ],
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
                    'complainantContactDetails' => [
                        'person' => [
                            'forename' => 'Joe',
                            'familyName' => 'Smith'
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
                        'complainantForename' => 'Joe',
                        'complainantFamilyName' => 'Smith',
                        'someEntity' => 44,
                        'complainantContactDetails' => [
                            'person' => [
                                'forename' => 'Joe',
                                'familyName' => 'Smith'
                            ]
                        ],
                    ],
                    'base' => [
                        'case' => 24,
                        'id' => 99,
                        'version' => 3,
                    ]
                ]
            ],
        ];
    }

    public function testMapFromForm(): void
    {
        $inData = [
            'fields' => [
                'complainantForename' => 'Joe2',
                'complainantFamilyName' => 'Smith2',
            ]
        ];
        $expected = [
            'complainantForename' => 'Joe2',
            'complainantFamilyName' => 'Smith2',
            'complainantContactDetails' => [
                'person' => [
                    'forename' => 'Joe2',
                    'familyName' => 'Smith2'
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
