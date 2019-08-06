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
    /**
     * @dataProvider dpTestExceptionNotEcmtShortTerm
     */
    public function testExceptionNotEcmtShortTerm($typeId)
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(AvailableYears::ERR_UNSUPPORTED);

        $data = [
            'type' => $typeId
        ];

        AvailableYears::mapForFormOptions(
            $data,
            m::mock(Form::class),
            m::mock(TranslationHelperService::class)
        );
    }

    public function dpTestExceptionNotEcmtShortTerm()
    {
        return [
            [RefData::ECMT_PERMIT_TYPE_ID],
            [RefData::ECMT_REMOVAL_PERMIT_TYPE_ID],
            [RefData::IRHP_BILATERAL_PERMIT_TYPE_ID],
            [RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID],
        ];
    }

    /**
     * @dataProvider dpTestSingleOption
     */
    public function testSingleOption($year, $optionHintTranslationKey)
    {
        $data = [
            'type' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
            'years' => [
                'years' => [$year]
            ],
            'hint' => 'permits.page.year.hint.single-year-available',
            'question' => 'permits.page.year.question.single-year-available',
            'browserTitle' => 'permits.page.year.browser.title.single-year-available',
        ];

        $translatedHint = 'Translated hint';

        $translator = m::mock(TranslationHelperService::class);
        $translator->shouldReceive('translateReplace')
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

        $returnedData = AvailableYears::mapForFormOptions($data, $form, $translator);

        $this->assertEquals($returnedData, $data);
    }

    public function dpTestSingleOption()
    {
        return [
            [2018, 'permits.page.year.ecmt-short-term.option.hint.not-2019'],
            [2019, 'permits.page.year.ecmt-short-term.option.hint.2019'],
            [2020, 'permits.page.year.ecmt-short-term.option.hint.not-2019'],
        ];
    }

    public function testMultipleOptions()
    {
        $data = [
            'type' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
            'years' => [
                'years' => [2018, 2019, 2020]
            ],
            'hint' => 'permits.page.year.hint.single-year-available',
            'question' => 'permits.page.year.question.single-year-available',
            'browserTitle' => 'permits.page.year.browser.title.single-year-available',
        ];

        $translatedHint2018 = 'Translated hint 2018';
        $translatedHint2019 = 'Translated hint 2019';
        $translatedHint2020 = 'Translated hint 2020';

        $translator = m::mock(TranslationHelperService::class);
        $translator->shouldReceive('translateReplace')
            ->with('permits.page.year.ecmt-short-term.option.hint.not-2019', [2018])
            ->andReturn($translatedHint2018);
        $translator->shouldReceive('translateReplace')
            ->with('permits.page.year.ecmt-short-term.option.hint.2019', [2019])
            ->andReturn($translatedHint2019);
        $translator->shouldReceive('translateReplace')
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
        ];

        $returnedData = AvailableYears::mapForFormOptions($data, $form, $translator);

        $this->assertEquals($expectedData, $returnedData);
    }
}
