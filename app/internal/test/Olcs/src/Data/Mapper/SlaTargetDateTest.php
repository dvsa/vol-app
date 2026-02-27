<?php

declare(strict_types=1);

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\SlaTargetDate as Sut;
use Laminas\Form\FormInterface;

/**
 * SlaTargetDate Mapper Test
 */
class SlaTargetDateTest extends MockeryTestCase
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
                    'details' => 'details',
                    'document' => [
                        'id' => 88,
                        'description' => 'foobar'
                    ]
                ],
                [
                    'fields' => [
                        'details' => 'details',
                        'document' => 88,
                        'entityType' => 'document',
                        'entityId' => 88,
                        'entityTypeHtml' => 'foobar'
                    ]
                ]
            ],
            // edit
            [
                [
                    'id' => 33,
                    'version' => 2,
                    'details' => 'details',
                    'document' => [
                        'id' => 88,
                        'description' => 'foobar'
                    ]
                ],
                [
                    'fields' => [
                        'id' => 33,
                        'version' => 2,
                        'details' => 'details',
                        'document' => 88,
                        'entityType' => 'document',
                        'entityId' => 88,
                        'entityTypeHtml' => 'foobar'
                    ]
                ]
            ]
        ];
    }

    public function testMapFromForm(): void
    {
        $inData = [
            'fields' => [
                'entityTypeHtml' => 'foobar',
                'somefield' => 'somevalue'
            ]
        ];
        $expected = [
            'somefield' => 'somevalue'
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
