<?php

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\SubmissionAction as Sut;
use Laminas\Form\FormInterface;

/**
 * SubmissionAction Mapper Test
 */
class SubmissionActionTest extends MockeryTestCase
{
    /**
     *
     * @param $inData
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('mapFromResultDataProvider')]
    public function testMapFromResult($inData, $expected)
    {
        $this->assertEquals($expected, Sut::mapFromResult($inData));
    }

    public static function mapFromResultDataProvider()
    {
        return [
            // add
            [
                [],
                ['fields' => []]
            ],
            // edit - recommendation
            [
                [
                    'id' => 987,
                    'submission' => ['id' => 100],
                    'isDecision' => 'N',
                    'actionTypes' => [
                        ['id' => 200]
                    ],
                ],
                [
                    'fields' => [
                        'id' => 987,
                        'submission' => 100,
                        'isDecision' => 'N',
                        'actionTypes' => [
                            ['id' => 200]
                        ],
                    ],
                ]
            ],
            // edit - decision
            [
                [
                    'id' => 987,
                    'submission' => ['id' => 100],
                    'isDecision' => 'Y',
                    'actionTypes' => [
                        ['id' => 200]
                    ],
                ],
                [
                    'fields' => [
                        'id' => 987,
                        'submission' => 100,
                        'isDecision' => 'Y',
                        'actionTypes' => ['id' => 200],
                    ],
                ]
            ]
        ];
    }

    /**
     *
     * @param $inData
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('mapFromFormDataProvider')]
    public function testMapFromForm($inData, $expected)
    {
        $this->assertEquals($expected, Sut::mapFromForm($inData));
    }

    public static function mapFromFormDataProvider()
    {
        return [
            // recommendation
            [
                [
                    'fields' => [
                        'actionTypes' => [200],
                    ]
                ],
                [
                    'actionTypes' => [200]
                ]
            ],
            // decision
            [
                [
                    'fields' => [
                        'actionTypes' => 200
                    ]
                ],
                [
                    'actionTypes' => [200]
                ]
            ],
        ];
    }

    public function testMapFromErrors()
    {
        $mockForm = m::mock(FormInterface::class);
        $errors = ['field' => 'data'];

        $this->assertEquals($errors, Sut::mapFromErrors($mockForm, $errors));
    }
}
