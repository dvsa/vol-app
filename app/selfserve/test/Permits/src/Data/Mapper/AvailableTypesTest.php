<?php

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
    /**
     * @dataProvider dpTestMapForFormOptions
     */
    public function testMapForFormOptions($data, $expected, $expectedValueOptions)
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

    public function dpTestMapForFormOptions()
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
