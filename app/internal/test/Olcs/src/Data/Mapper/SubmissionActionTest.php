<?php

declare(strict_types=1);

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\SubmissionAction as Sut;
use Laminas\Form\FormInterface;

/**
 * SubmissionAction Mapper Test
 */
final class SubmissionActionTest extends MockeryTestCase
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

    public static function mapFromResultDataProvider(): \Iterator
    {
        // add
        yield [
            [],
            ['fields' => []]
        ];
        // edit - recommendation
        yield [
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
        ];
        // edit - decision
        yield [
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
        ];
    }

    /**
     *
     * @param $inData
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('mapFromFormDataProvider')]
    public function testMapFromForm(mixed $inData, mixed $expected): void
    {
        $this->assertEquals($expected, Sut::mapFromForm($inData));
    }

    public static function mapFromFormDataProvider(): \Iterator
    {
        // recommendation
        yield [
            [
                'fields' => [
                    'actionTypes' => [200],
                ]
            ],
            [
                'actionTypes' => [200]
            ]
        ];
        // decision
        yield [
            [
                'fields' => [
                    'actionTypes' => 200
                ]
            ],
            [
                'actionTypes' => [200]
            ]
        ];
    }

    public function testMapFromErrors(): void
    {
        $mockForm = m::mock(FormInterface::class);
        $errors = ['field' => 'data'];

        $this->assertEquals($errors, Sut::mapFromErrors($mockForm, $errors));
    }
}
