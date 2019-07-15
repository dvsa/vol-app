<?php

namespace PermitsTest\Data\Mapper;

use Common\Form\Form;
use Permits\Data\Mapper\AvailableYears;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;

/**
 * AvailableYearsTest
 */
class AvailableYearsTest extends TestCase
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
            ->with('year')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setValueOptions')
            ->with($expectedValueOptions)
            ->once();

        self::assertEquals($expected, AvailableYears::mapForFormOptions($data, $mockForm));
    }

    public function dpTestMapForFormOptions()
    {
        return [
            'empty list' => [
                'data' => [
                    'years' => [
                        'years' => []
                    ]
                ],
                'expected' => [
                    'years' => [
                        'years' => []
                    ]
                ],
                'expectedValueOptions' => [],
            ],
            'list with data' => [
                'data' => [
                    'years' => [
                        'years' => [
                            3030, 3031
                        ],

                    ]
                ],
                'expected' => [
                    'years' => [
                        'years' => [
                            3030, 3031
                        ],
                    ],
                    'hint' => 'permits.page.year.hint.multiple-years-available'
                ],
                'expectedValueOptions' => [
                    [
                        'value' => 3030,
                        'label' => 3030,
                        'label_attributes' => ['class' => 'govuk-label govuk-radios__label govuk-label--s']

                    ],
                    [
                        'value' => 3031,
                        'label' => 3031,
                        'label_attributes' => ['class' => 'govuk-label govuk-radios__label govuk-label--s']
                    ],
                ],
            ],
            'list with one_year' => [
                'data' => [
                    'years' => [
                        'years' => [
                            3030
                        ],

                    ]
                ],
                'expected' => [
                    'years' => [
                        'years' => [
                            3030
                        ],
                    ],
                ],
                'expectedValueOptions' => [
                    [
                        'value' => 3030,
                        'label' => 3030,
                        'label_attributes' => ['class' => 'govuk-label govuk-radios__label govuk-label--s']

                    ]
                ],
            ],
        ];
    }
}
