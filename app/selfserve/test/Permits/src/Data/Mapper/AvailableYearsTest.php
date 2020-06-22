<?php

namespace PermitsTest\Data\Mapper;

use Common\Form\Form;
use Common\RefData;
use Permits\Data\Mapper\AvailableYears;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;
use RuntimeException;

/**
 * AvailableYearsTest
 */
class AvailableYearsTest extends TestCase
{
    private $availableYears;

    public function setUp(): void
    {
        $this->availableYears = new AvailableYears();
    }

    /**
     * @dataProvider dpTestExceptionNotSupported
     */
    public function testExceptionNotSupported($typeId)
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('This mapper does not support permit type ' . $typeId);

        $data = [
            'type' => $typeId
        ];

        $this->availableYears->mapForFormOptions(
            $data,
            m::mock(Form::class)
        );
    }

    public function dpTestExceptionNotSupported()
    {
        return [
            [RefData::ECMT_REMOVAL_PERMIT_TYPE_ID],
            [RefData::IRHP_BILATERAL_PERMIT_TYPE_ID],
            [RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID],
        ];
    }

    public function testEcmtShortTermSingleOption()
    {
        $year = 2019;
        $data = [
            'type' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
            'years' => [
                'years' => [$year]
            ],
        ];

        $expectedValueOptions = [
            [
                'value' => $year,
                'label' => $year,
                'label_attributes' => ['class' => 'govuk-label govuk-radios__label govuk-label--s'],
                'attributes' => [
                    'id' => 'year'
                ]
            ]
        ];

        $form = m::mock(Form::class);
        $form->shouldReceive('get')
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

        $expectedData = [
            'type' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
            'years' => [
                'years' => [$year]
            ],
            'hint' => 'permits.page.year.hint.one-year-available',
            'question' => 'permits.page.year.question.one-year-available',
            'browserTitle' => 'permits.page.year.browser.title.one-year-available',
            'guidance' => [
                'value' => 'permits.page.year.ecmt-short-term.guidance',
                'disableHtmlEscape' => true,
            ],
        ];

        $returnedData = $this->availableYears->mapForFormOptions($data, $form);

        $this->assertEquals($expectedData, $returnedData);
    }

    public function testEcmtShortTermMultipleOptions()
    {
        $data = [
            'type' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
            'years' => [
                'years' => [2018, 2019, 2020]
            ],
        ];

        $expectedValueOptions = [
            [
                'value' => 2018,
                'label' => 2018,
                'label_attributes' => ['class' => 'govuk-label govuk-radios__label govuk-label--s'],
                'attributes' => [
                    'id' => 'year'
                ]
            ],
            [
                'value' => 2019,
                'label' => 2019,
                'label_attributes' => ['class' => 'govuk-label govuk-radios__label govuk-label--s'],
            ],
            [
                'value' => 2020,
                'label' => 2020,
                'label_attributes' => ['class' => 'govuk-label govuk-radios__label govuk-label--s'],
            ],
        ];

        $form = m::mock(Form::class);
        $form->shouldReceive('get')
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

        $expectedData = [
            'type' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
            'years' => [
                'years' => [2018, 2019, 2020]
            ],
            'hint' => 'permits.page.year.hint.multiple-years-available',
            'question' => 'permits.page.year.question.multiple-years-available',
            'browserTitle' => 'permits.page.year.browser.title.multiple-years-available',
            'guidance' => [
                'value' => 'permits.page.year.ecmt-short-term.guidance',
                'disableHtmlEscape' => true,
            ],
        ];

        $returnedData = $this->availableYears->mapForFormOptions($data, $form);

        $this->assertEquals($expectedData, $returnedData);
    }

    /**
     * @dataProvider dpTestEcmtAnnual
     */
    public function testEcmtAnnual($data, $expected, $expectedValueOptions)
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

        $this->assertEquals(
            $expected,
            $this->availableYears->mapForFormOptions($data, $mockForm)
        );
    }

    public function dpTestEcmtAnnual()
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
                    ],
                    'browserTitle' => 'permits.page.year.browser.title',
                    'question' => 'permits.page.year.question',
                    'hint' => 'permits.page.year.hint.one-year-available',
                ],
                'expectedValueOptions' => [],
            ],
            'list with data' => [
                'data' => [
                    'type' => RefData::ECMT_PERMIT_TYPE_ID,
                    'years' => [
                        'years' => [
                            3030, 3031
                        ],

                    ]
                ],
                'expected' => [
                    'type' => RefData::ECMT_PERMIT_TYPE_ID,
                    'years' => [
                        'years' => [
                            3030, 3031
                        ],
                    ],
                    'browserTitle' => 'permits.page.year.browser.title',
                    'question' => 'permits.page.year.question',
                    'hint' => 'permits.page.year.hint.multiple-years-available',
                ],
                'expectedValueOptions' => [
                    [
                        'value' => 3030,
                        'label' => 3030,
                        'label_attributes' => ['class' => 'govuk-label govuk-radios__label govuk-label--s'],
                        'attributes' => [
                            'id' => 'year'
                        ]
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
                    'type' => RefData::ECMT_PERMIT_TYPE_ID,
                    'years' => [
                        'years' => [
                            3030
                        ],

                    ]
                ],
                'expected' => [
                    'type' => RefData::ECMT_PERMIT_TYPE_ID,
                    'years' => [
                        'years' => [
                            3030
                        ],
                    ],
                    'browserTitle' => 'permits.page.year.browser.title',
                    'question' => 'permits.page.year.question',
                    'hint' => 'permits.page.year.hint.one-year-available',
                ],
                'expectedValueOptions' => [
                    [
                        'value' => 3030,
                        'label' => 3030,
                        'label_attributes' => ['class' => 'govuk-label govuk-radios__label govuk-label--s'],
                        'attributes' => [
                            'id' => 'year'
                        ]
                    ]
                ],
            ],
        ];
    }
}
