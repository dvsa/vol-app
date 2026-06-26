<?php

namespace CommonTest\Service\Qa\Custom\Ecmt;

use Common\Form\Elements\Custom\EcmtNoOfPermitsEitherElement;
use Common\Form\Elements\InputFilters\QaRadio;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Qa\Custom\Common\HtmlAdder;
use Common\Service\Qa\Custom\Ecmt\NoOfPermitsBaseInsetTextGenerator;
use Common\Service\Qa\Custom\Ecmt\NoOfPermitsEitherFieldsetPopulator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;

/**
 * NoOfPermitsEitherFieldsetPopulatorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class NoOfPermitsEitherFieldsetPopulatorTest extends MockeryTestCase
{
    /**
     * @dataProvider dpPopulate
     */
    public function testPopulate($euro5Value, $euro6Value, $expectedRadioValue, $expectedTextboxValue): void
    {
        $maxCanApplyFor = 30;
        $maxPermitted = 50;
        $euro5PermitsRemaining = 40;
        $euro6PermitsRemaining = 10;
        $skipAvailabilityValidation = true;

        $translatedSection1Heading = 'translated section 1 heading';
        $translatedSection1Blurb = 'translated section 1 blurb';
        $baseInsetText = '<div class="govuk-inset-text">base inset text</div>';
        $translatedHint = 'translated hint, maxPermitted = %s';
        $translatedSection2Heading = 'translated section 2 heading';

        $expectedInsetAndSection1HeaderMarkup = '<div class="govuk-inset-text">base inset text</div>' .
            '<p class="govuk-body"><strong>1. translated section 1 heading</strong></p>' .
            '<p class="govuk-body">translated section 1 blurb</p>';

        $expectedSection2HeaderMarkup = '<p class="govuk-body govuk-!-margin-top-6"><strong>2. translated section 2 heading</strong></p>';

        $options = [
            'maxCanApplyFor' => $maxCanApplyFor,
            'maxPermitted' => $maxPermitted,
            'skipAvailabilityValidation' => $skipAvailabilityValidation,
            'emissionsCategories' => [
                [
                    'type' => 'euro5',
                    'permitsRemaining' => $euro5PermitsRemaining,
                    'value' => $euro5Value
                ],
                [
                    'type' => 'euro6',
                    'permitsRemaining' => $euro6PermitsRemaining,
                    'value' => $euro6Value
                ]
            ]
        ];

        $form = m::mock(Form::class);

        $fieldset = m::mock(Fieldset::class);

        $htmlAdder = m::mock(HtmlAdder::class);
        $htmlAdder->shouldReceive('add')
            ->with($fieldset, 'insetAndSection1Header', $expectedInsetAndSection1HeaderMarkup)
            ->once()
            ->globally()
            ->ordered();

        $expectedQaRadioParams = [
            'name' => 'emissionsCategory',
            'type' => QaRadio::class,
            'options' => [
                'value_options' => [
                    'euro5' => 'qanda.ecmt.number-of-permits.either.radio-label.euro5',
                    'euro6' => 'qanda.ecmt.number-of-permits.either.radio-label.euro6'
                ],
                'not_selected_message' => 'qanda.ecmt.number-of-permits.either.error.select-emissions-category'
            ],
            'attributes' => [
                'value' => $expectedRadioValue
            ],
        ];

        $fieldset->shouldReceive('add')
            ->with($expectedQaRadioParams)
            ->once()
            ->globally()
            ->ordered();

        $htmlAdder->shouldReceive('add')
            ->with($fieldset, 'section2Header', $expectedSection2HeaderMarkup)
            ->once()
            ->globally()
            ->ordered();

        $expectedEcmtNoOfPermitsEitherElementParams = [
            'type' => EcmtNoOfPermitsEitherElement::class,
            'name' => 'permitsRequired',
            'options' => [
                'label' => 'qanda.ecmt.number-of-permits.caption',
                'hint' => 'translated hint, maxPermitted = 30',
                'maxPermitted' => 50,
                'skipAvailabilityValidation' => $skipAvailabilityValidation,
                'emissionsCategoryPermitsRemaining' => [
                    'euro5' => 40,
                    'euro6' => 10
                ]
            ],
            'attributes' => [
                'value' => $expectedTextboxValue
            ]
        ];

        $fieldset->shouldReceive('add')
            ->with($expectedEcmtNoOfPermitsEitherElementParams)
            ->once()
            ->globally()
            ->ordered();

        $translator = m::mock(TranslationHelperService::class);
        $translator->shouldReceive('translate')
            ->with('qanda.ecmt.number-of-permits.either.section-1.heading')
            ->andReturn($translatedSection1Heading);
        $translator->shouldReceive('translate')
            ->with('qanda.ecmt.number-of-permits.either.section-1.blurb')
            ->andReturn($translatedSection1Blurb);
        $translator->shouldReceive('translate')
            ->with('qanda.ecmt.number-of-permits.either.section-2.heading')
            ->andReturn($translatedSection2Heading);
        $translator->shouldReceive('translate')
            ->with('qanda.ecmt.number-of-permits.hint')
            ->andReturn($translatedHint);

        $noOfPermitsBaseInsetTextGenerator = m::mock(NoOfPermitsBaseInsetTextGenerator::class);
        $noOfPermitsBaseInsetTextGenerator->shouldReceive('generate')
            ->with($options, '<div class="govuk-inset-text">%s</div>')
            ->andReturn($baseInsetText);

        $noOfPermitsEitherFieldsetPopulator = new NoOfPermitsEitherFieldsetPopulator(
            $translator,
            $noOfPermitsBaseInsetTextGenerator,
            $htmlAdder
        );

        $noOfPermitsEitherFieldsetPopulator->populate($form, $fieldset, $options);
    }

    /**
     * @return (int|null|string)[][]
     *
     * @psalm-return list{list{20, null, 'euro5', 20}, list{null, 10, 'euro6', 10}}
     */
    public function dpPopulate(): array
    {
        return [
            [20, null, 'euro5', 20],
            [null, 10, 'euro6', 10],
        ];
    }
}
