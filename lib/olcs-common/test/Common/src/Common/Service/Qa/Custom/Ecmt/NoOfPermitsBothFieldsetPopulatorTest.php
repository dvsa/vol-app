<?php

namespace CommonTest\Service\Qa\Custom\Ecmt;

use Common\Form\Elements\Custom\EcmtNoOfPermitsBothElement;
use Common\Form\Elements\Custom\EcmtNoOfPermitsCombinedTotalElement;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Qa\Custom\Common\HtmlAdder;
use Common\Service\Qa\Custom\Ecmt\NoOfPermitsBaseInsetTextGenerator;
use Common\Service\Qa\Custom\Ecmt\NoOfPermitsBothFieldsetPopulator;
use Common\Service\Qa\Custom\Ecmt\NoOfPermitsBothInsetTextGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;

/**
 * NoOfPermitsBothFieldsetPopulatorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class NoOfPermitsBothFieldsetPopulatorTest extends MockeryTestCase
{
    public function testPopulate(): void
    {
        $maxCanApplyFor = 35;
        $maxPermitted = 50;
        $skipAvailabilityValidation = true;
        $translatedHint = 'translated hint, maxPermitted = %s';
        $translatedInsetSupplement = 'translated inset supplement';
        $translatedCaption = 'translated caption';
        $baseInsetText = 'base inset text<br><br>';

        $euro5PermitsRemaining = 40;
        $euro5Value = 20;

        $euro6PermitsRemaining = 10;
        $euro6Value = 10;

        $expectedInsetAndBlurbMarkup = '<div class="govuk-inset-text">base inset text<br><br>translated inset supplement</div>' .
            '<p class="govuk-body"><strong>translated caption</strong><br>' .
            '<span class="hint">translated hint, maxPermitted = 35</span></p>';

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

        $euro5ElementParams = [
            'type' => EcmtNoOfPermitsBothElement::class,
            'name' => 'euro5',
            'options' => [
                'label' => 'qanda.ecmt.number-of-permits.textbox.label.euro5',
                'permitsRemaining' => $euro5PermitsRemaining,
                'skipAvailabilityValidation' => $skipAvailabilityValidation,
                'emissionsCategory' => 'euro5'
            ],
            'attributes' => [
                'value' => $euro5Value
            ]
        ];

        $euro6ElementParams = [
            'type' => EcmtNoOfPermitsBothElement::class,
            'name' => 'euro6',
            'options' => [
                'label' => 'qanda.ecmt.number-of-permits.textbox.label.euro6',
                'permitsRemaining' => $euro6PermitsRemaining,
                'skipAvailabilityValidation' => $skipAvailabilityValidation,
                'emissionsCategory' => 'euro6'
            ],
            'attributes' => [
                'value' => $euro6Value
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
            ->with($euro5ElementParams)
            ->once()
            ->globally()
            ->ordered();
        $fieldset->shouldReceive('add')
            ->with($euro6ElementParams)
            ->once()
            ->globally()
            ->ordered();

        $combinedTotalCheckerElementParams = [
            'name' => 'combinedTotalChecker',
            'type' => EcmtNoOfPermitsCombinedTotalElement::class,
            'options' => [
                'maxPermitted' => $maxPermitted
            ]
        ];

        $fieldset->shouldReceive('add')
            ->with($combinedTotalCheckerElementParams)
            ->once();

        $translator = m::mock(TranslationHelperService::class);
        $translator->shouldReceive('translate')
            ->with('qanda.ecmt.number-of-permits.hint')
            ->andReturn($translatedHint);
        $translator->shouldReceive('translate')
            ->with('qanda.ecmt.number-of-permits.both.inset.supplement')
            ->andReturn($translatedInsetSupplement);
        $translator->shouldReceive('translate')
            ->with('qanda.ecmt.number-of-permits.caption')
            ->andReturn($translatedCaption);

        $noOfPermitsBaseInsetTextGenerator = m::mock(NoOfPermitsBaseInsetTextGenerator::class);
        $noOfPermitsBaseInsetTextGenerator->shouldReceive('generate')
            ->with($options, '%s<br><br>')
            ->andReturn($baseInsetText);

        $noOfPermitsBothFieldsetPopulator = new NoOfPermitsBothFieldsetPopulator(
            $translator,
            $noOfPermitsBaseInsetTextGenerator,
            $htmlAdder
        );

        $noOfPermitsBothFieldsetPopulator->populate($form, $fieldset, $options);
    }
}
