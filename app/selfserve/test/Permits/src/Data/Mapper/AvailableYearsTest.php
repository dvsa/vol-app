<?php

declare(strict_types=1);

namespace PermitsTest\Data\Mapper;

use Common\Form\Elements\Types\Html;
use Common\Form\Form;
use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Permits\Data\Mapper\AvailableYears;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;
use RuntimeException;
use Laminas\Form\Element\Hidden;

class AvailableYearsTest extends TestCase
{
    private $availableYears;
    private $translationHelperService;

    public function setUp(): void
    {
        $this->translationHelperService = m::mock(TranslationHelperService::class);
        $this->availableYears = new AvailableYears($this->translationHelperService);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestExceptionNotSupported')]
    public function testExceptionNotSupported(int $typeId): void
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

    /**
     * @return int[][]
     *
     * @psalm-return list{list{3}, list{4}, list{5}}
     */
    public static function dpTestExceptionNotSupported(): array
    {
        return [
            [RefData::ECMT_REMOVAL_PERMIT_TYPE_ID],
            [RefData::IRHP_BILATERAL_PERMIT_TYPE_ID],
            [RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID],
        ];
    }

    public function testEcmtShortTermSingleOption(): void
    {
        $year = 2019;
        $data = [
            'type' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
            'years' => [
                'years' => [$year],
                'selectedYear' => ''
            ],
        ];

        $form = m::mock(Form::class);
        $form->shouldReceive('get')
            ->with('fields')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('add')
            ->with(
                [
                    'name' => 'yearLabel',
                    'type' => Html::class,
                    'attributes' => [
                        'value' => '<p class="govuk-body-l">translated-text-with-year</p>',
                    ]
                ]
            )
            ->once()
            ->andReturnSelf()
            ->shouldReceive('add')
            ->with(
                [
                    'name' => 'year',
                    'type' => Hidden::class,
                    'attributes' => [
                        'value' => $year,
                    ]
                ]
            )
            ->once();

        $expectedData = [
            'type' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
            'years' => [
                'years' => [$year],
                'selectedYear' => ''
            ],
            'question' => 'permits.page.year.ecmt-short-term.question.one-year-available',
            'browserTitle' => 'permits.page.year.ecmt-short-term.question.one-year-available',
            'guidance' => [
                'value' => 'permits.page.year.ecmt-short-term.guidance',
                'disableHtmlEscape' => true,
            ],
        ];

        $this->translationHelperService->shouldReceive('translateReplace')
            ->once()
            ->with('permits.page.year.ecmt-short-term.hint.one-year-available', [$year])
            ->andReturn('translated-text-with-year');

        $returnedData = $this->availableYears->mapForFormOptions($data, $form);

        $this->assertEquals($expectedData, $returnedData);
    }

    public function testEcmtShortTermMultipleOptions(): void
    {
        $data = [
            'type' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
            'years' => [
                'years' => [2018, 2019, 2020],
                'selectedYear' => 2019
            ],
        ];

        $expectedValueOptions = [
            [
                'value' => 2018,
                'label' => 2018,
                'attributes' => [
                    'id' => 'year'
                ],
                'selected' => false
            ],
            [
                'value' => 2019,
                'label' => 2019,
                'selected' => true
            ],
            [
                'value' => 2020,
                'label' => 2020,
                'selected' => false
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
                'years' => [2018, 2019, 2020],
                'selectedYear' => 2019
            ],
            'hint' => 'permits.page.year.ecmt-short-term.hint.multiple-years-available',
            'question' => 'permits.page.year.ecmt-short-term.question.multiple-years-available',
            'browserTitle' => 'permits.page.year.ecmt-short-term.question.multiple-years-available',
            'guidance' => [
                'value' => 'permits.page.year.ecmt-short-term.guidance',
                'disableHtmlEscape' => true,
            ],
        ];

        $returnedData = $this->availableYears->mapForFormOptions($data, $form);

        $this->assertEquals($expectedData, $returnedData);
    }

    public function testEcmtAnnualSingleOption(): void
    {
        $year = 2019;
        $data = [
            'type' => RefData::ECMT_PERMIT_TYPE_ID,
            'years' => [
                'years' => [$year],
                'selectedYear' => ''
            ]
        ];

        $form = m::mock(Form::class);
        $form->shouldReceive('get')
            ->with('fields')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('add')
            ->with(
                [
                    'name' => 'yearLabel',
                    'type' => Html::class,
                    'attributes' => [
                        'value' => '<p class="govuk-body-l">translated-text-with-year</p>',
                    ]
                ]
            )
            ->once()
            ->andReturnSelf()
            ->shouldReceive('add')
            ->with(
                [
                    'name' => 'year',
                    'type' => Hidden::class,
                    'attributes' => [
                        'value' => $year,
                    ]
                ]
            )
            ->once();

        $expectedData = [
            'type' => RefData::ECMT_PERMIT_TYPE_ID,
            'years' => [
                'years' => [$year],
                'selectedYear' => ''
            ],
            'question' => 'permits.page.year.ecmt-annual.question.one-year-available',
            'browserTitle' => 'permits.page.year.ecmt-annual.question.one-year-available',
        ];

        $this->translationHelperService->shouldReceive('translateReplace')
            ->once()
            ->with('permits.page.year.ecmt-annual.hint.one-year-available', [$year])
            ->andReturn('translated-text-with-year');

        $returnedData = $this->availableYears->mapForFormOptions($data, $form);

        $this->assertEquals($expectedData, $returnedData);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestEcmtAnnualMultipleOptions')]
    public function testEcmtAnnualMultipleOptions(array $data, array $expected, array $expectedValueOptions): void
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

    /**
     * @return (((int|string)[]|bool|int|string)[]|int|string)[][][]
     *
     * @psalm-return array{'empty list': array{data: array{type: 1, years: array{years: array<never, never>, selectedYear: ''}}, expected: array{type: 1, years: array{years: array<never, never>, selectedYear: ''}, browserTitle: 'permits.page.year.ecmt-annual.question.multiple-years-available', question: 'permits.page.year.ecmt-annual.question.multiple-years-available', hint: 'permits.page.year.ecmt-annual.hint.multiple-years-available'}, expectedValueOptions: array<never, never>}, 'list with data': array{data: array{type: 1, years: array{years: list{3030, 3031}, selectedYear: 3031}}, expected: array{type: 1, years: array{years: list{3030, 3031}, selectedYear: 3031}, browserTitle: 'permits.page.year.ecmt-annual.question.multiple-years-available', question: 'permits.page.year.ecmt-annual.question.multiple-years-available', hint: 'permits.page.year.ecmt-annual.hint.multiple-years-available'}, expectedValueOptions: list{array{value: 3030, label: 3030, attributes: array{id: 'year'}, selected: false}, array{value: 3031, label: 3031, selected: true}}}}
     */
    public static function dpTestEcmtAnnualMultipleOptions(): array
    {
        return [
            'empty list' => [
                'data' => [
                    'type' => RefData::ECMT_PERMIT_TYPE_ID,
                    'years' => [
                        'years' => [],
                        'selectedYear' => ''
                    ]
                ],
                'expected' => [
                    'type' => RefData::ECMT_PERMIT_TYPE_ID,
                    'years' => [
                        'years' => [],
                        'selectedYear' => ''
                    ],
                    'browserTitle' => 'permits.page.year.ecmt-annual.question.multiple-years-available',
                    'question' => 'permits.page.year.ecmt-annual.question.multiple-years-available',
                    'hint' => 'permits.page.year.ecmt-annual.hint.multiple-years-available',
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
                        'selectedYear' => 3031
                    ]
                ],
                'expected' => [
                    'type' => RefData::ECMT_PERMIT_TYPE_ID,
                    'years' => [
                        'years' => [
                            3030, 3031
                        ],
                        'selectedYear' => 3031
                    ],
                    'browserTitle' => 'permits.page.year.ecmt-annual.question.multiple-years-available',
                    'question' => 'permits.page.year.ecmt-annual.question.multiple-years-available',
                    'hint' => 'permits.page.year.ecmt-annual.hint.multiple-years-available',
                ],
                'expectedValueOptions' => [
                    [
                        'value' => 3030,
                        'label' => 3030,
                        'attributes' => [
                            'id' => 'year'
                        ],
                        'selected' => false
                    ],
                    [
                        'value' => 3031,
                        'label' => 3031,
                        'selected' => true
                    ],
                ],
            ],
        ];
    }
}
