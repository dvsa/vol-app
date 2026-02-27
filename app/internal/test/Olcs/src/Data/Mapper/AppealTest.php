<?php

declare(strict_types=1);

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\Appeal as Sut;
use Laminas\Form\FormInterface;

/**
 * Appeal Mapper Test
 */
class AppealTest extends MockeryTestCase
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

    public function testMapFromForm(): void
    {
        $inData = [
            'fields' => [
                'withdrawnDate' => 'foo',
                'isWithdrawn' => 'N',
                'dvsaNotified' => 'Y',
            ],
        ];
        $expected = [
            'dvsaNotified' => 'Y',
            'isWithdrawn' => 'N',
            'withdrawnDate' => null,
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
