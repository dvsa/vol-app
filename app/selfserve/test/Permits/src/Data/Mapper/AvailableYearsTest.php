<?php

namespace PermitsTest\Data\Mapper;

use Common\Form\Form;
use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Permits\Data\Mapper\AvailableYears;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;
use RuntimeException;

/**
 * AvailableYearsTest
 */
class AvailableYearsTest extends TestCase
{
    private $translator;

    private $availableYears;

    public function setUp()
    {
        $this->translator = m::mock(TranslationHelperService::class);

        $this->availableYears = new AvailableYears($this->translator);
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
            m::mock(Form::class),
            m::mock(TranslationHelperService::class)
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

    /**
     * @dataProvider dpTestEcmtShortTermSingleOption
     */
    public function testEcmtShortTermSingleOption($year, $optionHintTranslationKey)
    {
        $data = [
            'type' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
            'years' => [
                'years' => [$year]
            ],
        ];

        $translatedHint = 'Translated hint';

        $this->translator->shouldReceive('translateReplace')
            ->with($optionHintTranslationKey, [$year])
            ->andReturn($translatedHint);

        $expectedValueOptions = [
            [
                'value' => $year,
                'label' => $year,
                'label_attributes' => ['class' => 'govuk-label govuk-radios__label govuk-label--s'],
                'hint' => $translatedHint,
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

    public function dpTestEcmtShortTermSingleOption()
    {
        return [
            [2018, 'permits.page.year.ecmt-short-term.option.hint.not-2019'],
            [2019, 'permits.page.year.ecmt-short-term.option.hint.2019'],
            [2020, 'permits.page.year.ecmt-short-term.option.hint.not-2019'],
        ];
    }

    public function testEcmtShortTermMultipleOptions()
    {
        $data = [
            'type' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
            'years' => [
                'years' => [2018, 2019, 2020]
            ],
        ];

        $translatedHint2018 = 'Translated hint 2018';
        $translatedHint2019 = 'Translated hint 2019';
        $translatedHint2020 = 'Translated hint 2020';

        $this->translator->shouldReceive('translateReplace')
            ->with('permits.page.year.ecmt-short-term.option.hint.not-2019', [2018])
            ->andReturn($translatedHint2018);
        $this->translator->shouldReceive('translateReplace')
            ->with('permits.page.year.ecmt-short-term.option.hint.2019', [2019])
            ->andReturn($translatedHint2019);
        $this->translator->shouldReceive('translateReplace')
            ->with('permits.page.year.ecmt-short-term.option.hint.not-2019', [2020])
            ->andReturn($translatedHint2020);

        $expectedValueOptions = [
            [
                'value' => 2018,
                'label' => 2018,
                'label_attributes' => ['class' => 'govuk-label govuk-radios__label govuk-label--s'],
                'hint' => $translatedHint2018,
            ],
            [
                'value' => 2019,
                'label' => 2019,
                'label_attributes' => ['class' => 'govuk-label govuk-radios__label govuk-label--s'],
                'hint' => $translatedHint2019,
            ],
            [
                'value' => 2020,
                'label' => 2020,
                'label_attributes' => ['class' => 'govuk-label govuk-radios__label govuk-label--s'],
                'hint' => $translatedHint2020,
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
                        'label_attributes' => ['class' => 'govuk-label govuk-radios__label govuk-label--s']

                    ]
                ],
            ],
        ];
    }
}
