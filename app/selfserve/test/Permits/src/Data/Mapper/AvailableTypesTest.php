<?php

declare(strict_types=1);

namespace PermitsTest\Data\Mapper;

use Common\Form\Form;
use Permits\Data\Mapper\AvailableTypes;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;

/**
 * AvailableTypesTest
 */
class AvailableTypesTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestMapForFormOptions')]
    public function testMapForFormOptions(array $data, array $expected, array $expectedValueOptions): void
    {
        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('get')
            ->with('fields')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('get')
            ->with('type')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setValueOptions')
            ->with($expectedValueOptions)
            ->once();

        $availableTypes = new AvailableTypes();

        self::assertEquals(
            $expected,
            $availableTypes->mapForFormOptions($data, $mockForm)
        );
    }

    /**
     * @return (((int|string|string[])[]|string)[]|bool|int|string)[][][][]
     *
     * @psalm-return array{'empty list': array{data: array{types: array{types: array<never, never>, selectedType: ''}}, expected: array{types: array{types: array<never, never>, selectedType: ''}}, expectedValueOptions: array<never, never>}, 'list with data': array{data: array{types: array{types: list{array{id: 1, description: 'desc 1', name: array{description: 'name 1'}}, array{id: 2, description: 'desc 2', name: array{description: 'name 2'}}}, selectedType: 2}}, expected: array{types: array{types: list{array{id: 1, description: 'desc 1', name: array{description: 'name 1'}}, array{id: 2, description: 'desc 2', name: array{description: 'name 2'}}}, selectedType: 2}}, expectedValueOptions: list{array{value: 1, label: 'name 1', hint: 'desc 1', label_attributes: array{class: 'govuk-label govuk-radios__label govuk-label--s'}, attributes: array{id: 'type'}, selected: false}, array{value: 2, label: 'name 2', hint: 'desc 2', label_attributes: array{class: 'govuk-label govuk-radios__label govuk-label--s'}, selected: true}}}}
     */
    public static function dpTestMapForFormOptions(): array
    {
        return [
            'empty list' => [
                'data' => [
                    'types' => [
                        'types' => [],
                        'selectedType' => ''
                    ],
                ],
                'expected' => [
                    'types' => [
                        'types' => [],
                        'selectedType' => ''
                    ],
                ],
                'expectedValueOptions' => [],
            ],
            'list with data' => [
                'data' => [
                    'types' => [
                        'types' => [
                            [
                                'id' => 1,
                                'description' => 'desc 1',
                                'name' => [
                                    'description' => 'name 1'
                                ]
                            ],
                            [
                                'id' => 2,
                                'description' => 'desc 2',
                                'name' => [
                                    'description' => 'name 2'
                                ]
                            ],
                        ],
                        'selectedType' => 2
                    ]
                ],
                'expected' => [
                    'types' => [
                        'types' => [
                            [
                                'id' => 1,
                                'description' => 'desc 1',
                                'name' => [
                                    'description' => 'name 1'
                                ]
                            ],
                            [
                                'id' => 2,
                                'description' => 'desc 2',
                                'name' => [
                                    'description' => 'name 2'
                                ]
                            ],
                        ],
                        'selectedType' => 2
                    ],
                ],
                'expectedValueOptions' => [
                    [
                        'value' => 1,
                        'label' => 'name 1',
                        'hint' => 'desc 1',
                        'label_attributes' => ['class' => 'govuk-label govuk-radios__label govuk-label--s'],
                        'attributes' => [
                            'id' => 'type'
                        ],
                        'selected' => false
                    ],
                    [
                        'value' => 2,
                        'label' => 'name 2',
                        'hint' => 'desc 2',
                        'label_attributes' => ['class' => 'govuk-label govuk-radios__label govuk-label--s'],
                        'selected' => true
                    ],
                ],
            ],
        ];
    }
}
