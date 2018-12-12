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

        self::assertEquals($expected, AvailableTypes::mapForFormOptions($data, $mockForm));
    }

    public function dpTestMapForFormOptions()
    {
        return [
            'empty list' => [
                'data' => [
                    'types' => [
                        'types' => []
                    ]
                ],
                'expected' => [
                    'types' => [
                        'types' => []
                    ],
                    'guidance' => [
                        'disableHtmlEscape' => true,
                        'value' => 'permits.page.type.hint'
                    ]
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
                        ]
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
                        ]
                    ],
                    'guidance' => [
                        'disableHtmlEscape' => true,
                        'value' => 'permits.page.type.hint'
                    ]
                ],
                'expectedValueOptions' => [
                    [
                        'value' => 1,
                        'label' => 'name 1',
                        'hint' => 'desc 1',
                        'label_attributes' => 'govuk-label govuk-radios__label govuk-label--s',
                    ],
                    [
                        'value' => 2,
                        'label' => 'name 2',
                        'hint' => 'desc 2',
                        'label_attributes' => 'govuk-label govuk-radios__label govuk-label--s',
                    ],
                ],
            ],
        ];
    }
}
