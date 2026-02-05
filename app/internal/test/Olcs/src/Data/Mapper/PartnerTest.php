<?php

declare(strict_types=1);

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\Partner as Sut;
use Laminas\Form\FormInterface;

/**
 * Partner Mapper Test
 */
class PartnerTest extends MockeryTestCase
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
                [],
                ['fields' => []]
            ],
            // edit
            [
                [
                    'id' => 987,
                    'version' => 1,
                    'address' => [
                        'id' => 200,
                        'version' => 1,
                        'addressLine1' => 'a1'
                    ],
                ],
                [
                    'fields' => [
                        'id' => 987,
                        'version' => 1,
                        'address' => 200,
                    ],
                    'address' => [
                        'id' => 200,
                        'version' => 1,
                        'addressLine1' => 'a1',
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
    public function testMapFromForm(mixed $inData, mixed $expected): void
    {
        $this->assertEquals($expected, Sut::mapFromForm($inData));
    }

    public static function mapFromFormDataProvider(): array
    {
        return [
            [
                [
                    'fields' => [
                        'description' => 'p1',
                    ],
                    'address' => [
                        'id' => 200,
                        'version' => 1,
                        'addressLine1' => 'a1',
                    ],
                ],
                [
                    'description' => 'p1',
                    'address' => [
                        'id' => 200,
                        'version' => 1,
                        'addressLine1' => 'a1',
                    ],
                ]
            ],
        ];
    }

    public function testMapFromErrors(): void
    {
        $mockForm = m::mock(FormInterface::class);
        $errors = ['field' => 'data'];

        $this->assertEquals($errors, Sut::mapFromErrors($mockForm, $errors));
    }
}
