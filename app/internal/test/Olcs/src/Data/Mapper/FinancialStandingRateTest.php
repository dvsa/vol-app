<?php

declare(strict_types=1);

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\FinancialStandingRate as Sut;
use Laminas\Form\FormInterface;

/**
 * FinancialStandingRate Mapper Test
 */
class FinancialStandingRateTest extends MockeryTestCase
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
                    'foo' => 'bar',
                ],
                [
                    'details' => [
                        'foo' => 'bar',
                    ],
                ]
            ],
            // edit
            [
                [
                    'id' => 99,
                    'version' => 3,
                    'foo' => 'bar',
                ],
                [
                    'details' => [
                        'id' => 99,
                        'version' => 3,
                        'foo' => 'bar',
                    ],
                ]
            ],
        ];
    }

    public function testMapFromForm(): void
    {
        $inData = [
            'details' => [
                'id' => 99,
                'version' => 3,
                'foo' => 'bar',
            ]
        ];
        $expected = [
            'id' => 99,
            'version' => 3,
            'foo' => 'bar',
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
