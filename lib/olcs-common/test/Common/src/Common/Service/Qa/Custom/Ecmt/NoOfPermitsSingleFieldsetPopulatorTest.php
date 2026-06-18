<?php

namespace CommonTest\Service\Qa\Custom\Ecmt;

use Common\Form\Elements\Custom\EcmtNoOfPermitsEmissionsCategoryHiddenElement;
use Common\Form\Elements\Custom\EcmtNoOfPermitsSingleElement;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Qa\Custom\Common\HtmlAdder;
use Common\Service\Qa\Custom\Ecmt\NoOfPermitsBaseInsetTextGenerator;
use Common\Service\Qa\Custom\Ecmt\NoOfPermitsSingleFieldsetPopulator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;

/**
 * NoOfPermitsSingleFieldsetPopulatorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class NoOfPermitsSingleFieldsetPopulatorTest extends MockeryTestCase
{
    /**
     * @dataProvider dpPopulate
     */
    public function testPopulate($emissionsCategoryType, $expectedTextboxLabelKey, $expectedInsetSupplementKey): void
    {
        $maxCanApplyFor = 21;
        $maxPermitted = 25;
        $skipAvailabilityValidation = true;
        $translatedHint = 'translated hint, maxPermitted = %s';
        $translatedInsetSupplement = 'translated inset supplement';
        $translatedCaption = 'translated caption';
        $baseInsetText = 'base inset text<br><br>';

        $permitsRemaining = 40;
        $value = 20;

        $expectedInsetAndBlurbMarkup = '<div class="govuk-inset-text">base inset text<br><br>translated inset supplement</div>' .
            '<p class="govuk-body"><strong>translated caption</strong><br>' .
            '<span class="hint">translated hint, maxPermitted = 21</span></p>';

        $options = [
            'maxCanApplyFor' => $maxCanApplyFor,
            'maxPermitted' => $maxPermitted,
            'skipAvailabilityValidation' => $skipAvailabilityValidation,
            'emissionsCategories' => [
                [
                    'type' => $emissionsCategoryType,
                    'permitsRemaining' => $permitsRemaining,
                    'value' => $value
                ],
            ]
        ];

        $form = m::mock(Form::class);

        $expectedSingleElementParams = [
            'type' => EcmtNoOfPermitsSingleElement::class,
            'name' => 'permitsRequired',
            'options' => [
                'label' => $expectedTextboxLabelKey,
                'maxPermitted' => $maxPermitted,
                'permitsRemaining' => $permitsRemaining,
                'skipAvailabilityValidation' => $skipAvailabilityValidation,
                'emissionsCategory' => $emissionsCategoryType,
            ],
            'attributes' => [
                'value' => $value
            ]
        ];

        $expectedHiddenElementParams = [
            'type' => EcmtNoOfPermitsEmissionsCategoryHiddenElement::class,
            'name' => 'emissionsCategory',
            'options' => [
                'expectedValue' => $emissionsCategoryType
            ],
            'attributes' => [
                'value' => $emissionsCategoryType
            ]
        ];

        $fieldset = m::mock(Fieldset::class);

        $htmlAdder = m::mock(HtmlAdder::class);
        $htmlAdder->shouldReceive('add')
            ->with($fieldset, 'insetAndBlurb', $expectedInsetAndBlurbMarkup)
            ->once()
            ->globally()
            ->ordered();
        $fieldset->shouldReceive('add')
            ->with($expectedSingleElementParams)
            ->once()
            ->globally()
            ->ordered();
        $fieldset->shouldReceive('add')
            ->with($expectedHiddenElementParams)
            ->once();

        $translator = m::mock(TranslationHelperService::class);
        $translator->shouldReceive('translate')
            ->with('qanda.ecmt.number-of-permits.hint')
            ->andReturn($translatedHint);
        $translator->shouldReceive('translate')
            ->with($expectedInsetSupplementKey)
            ->andReturn($translatedInsetSupplement);
        $translator->shouldReceive('translate')
            ->with('qanda.ecmt.number-of-permits.caption')
            ->andReturn($translatedCaption);

        $noOfPermitsBaseInsetTextGenerator = m::mock(NoOfPermitsBaseInsetTextGenerator::class);
        $noOfPermitsBaseInsetTextGenerator->shouldReceive('generate')
            ->with($options, '%s<br><br>')
            ->andReturn($baseInsetText);

        $noOfPermitsSingleFieldsetPopulator = new NoOfPermitsSingleFieldsetPopulator(
            $translator,
            $noOfPermitsBaseInsetTextGenerator,
            $htmlAdder
        );

        $noOfPermitsSingleFieldsetPopulator->populate($form, $fieldset, $options);
    }

    /**
     * @return string[][]
     *
     * @psalm-return list{list{'euro5', 'qanda.ecmt.number-of-permits.textbox.label.euro5', 'qanda.ecmt.number-of-permits.single.inset.supplement.euro5'}, list{'euro6', 'qanda.ecmt.number-of-permits.textbox.label.euro6', 'qanda.ecmt.number-of-permits.single.inset.supplement.euro6'}}
     */
    public function dpPopulate(): array
    {
        return [
            [
                'euro5',
                'qanda.ecmt.number-of-permits.textbox.label.euro5',
                'qanda.ecmt.number-of-permits.single.inset.supplement.euro5'
            ],
            [
                'euro6',
                'qanda.ecmt.number-of-permits.textbox.label.euro6',
                'qanda.ecmt.number-of-permits.single.inset.supplement.euro6'
            ]
        ];
    }
}
