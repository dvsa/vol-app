<?php

namespace PermitsTest\Data\Mapper;

use Common\Form\Form;
use Common\RefData;
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
                    'type' => RefData::ECMT_PERMIT_TYPE_ID,
                    'years' => [
                        'years' => []
                    ]
                ],
                'expected' => [
                    'type' => RefData::ECMT_PERMIT_TYPE_ID,
                    'years' => [
                        'years' => []
                    ]
                ],
                'expectedValueOptions' => [],
            ],
            'list with data' => [
                'data' => [
                    'type' => RefData::ECMT_PERMIT_TYPE_ID,
                    'years' => [
                        'years' => [
                            2019, 2020
                        ],
                    ]
                ],
                'expected' => [
                    'type' => RefData::ECMT_PERMIT_TYPE_ID,
                    'years' => [
                        'years' => [
                            2019, 2020
                        ],
                    ],
                    'hint' => 'permits.page.year.hint.multiple-years-available'
                ],
                'expectedValueOptions' => [
                    [
                        'value' => 2019,
                        'label' => 2019,
                        'label_attributes' => ['class' => 'govuk-label govuk-radios__label govuk-label--s']
                    ],
                    [
                        'value' => 2020,
                        'label' => 2020,
                        'label_attributes' => ['class' => 'govuk-label govuk-radios__label govuk-label--s']
                    ],
                ],
            ],
            'list with one_year' => [
                'data' => [
                    'type' => RefData::ECMT_PERMIT_TYPE_ID,
                    'years' => [
                        'years' => [
                            2019
                        ],

                    ]
                ],
                'expected' => [
                    'type' => RefData::ECMT_PERMIT_TYPE_ID,
                    'years' => [
                        'years' => [
                            2019
                        ],
                    ],
                ],
                'expectedValueOptions' => [
                    [
                        'value' => 2019,
                        'label' => 2019,
                        'label_attributes' => ['class' => 'govuk-label govuk-radios__label govuk-label--s']
                    ]
                ],
            ],
            'ECMT Short-term - list with data' => [
                'data' => [
                    'type' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
                    'years' => [
                        'years' => [
                            2019, 2020
                        ],
                    ]
                ],
                'expected' => [
                    'type' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
                    'years' => [
                        'years' => [
                            2019, 2020
                        ],
                    ],
                    'hint' => 'permits.page.year.hint.multiple-years-available'
                ],
                'expectedValueOptions' => [
                    [
                        'value' => 2019,
                        'label' => 'permits.page.year.ecmt-short-term.label.2019',
                        'label_attributes' => ['class' => 'govuk-label govuk-radios__label govuk-label--s']
                    ],
                    [
                        'value' => 2020,
                        'label' => 2020,
                        'label_attributes' => ['class' => 'govuk-label govuk-radios__label govuk-label--s']
                    ],
                ],
            ],
            'ECMT Short-term - list with one_year' => [
                'data' => [
                    'type' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
                    'years' => [
                        'years' => [
                            2019
                        ],

                    ]
                ],
                'expected' => [
                    'type' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
                    'years' => [
                        'years' => [
                            2019
                        ],
                    ],
                ],
                'expectedValueOptions' => [
                    [
                        'value' => 2019,
                        'label' => 'permits.page.year.ecmt-short-term.label.2019',
                        'label_attributes' => ['class' => 'govuk-label govuk-radios__label govuk-label--s']
                    ]
                ],
            ],
        ];
    }
}
