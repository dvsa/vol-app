<?php

namespace Common\Service\Qa\Custom\Ecmt;

use Common\Form\Elements\Custom\EcmtNoOfPermitsBothElement;
use Common\Form\Elements\Custom\EcmtNoOfPermitsCombinedTotalElement;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Qa\Custom\Common\HtmlAdder;
use Common\Service\Qa\FieldsetPopulatorInterface;
use Laminas\Form\Fieldset;

class NoOfPermitsBothFieldsetPopulator implements FieldsetPopulatorInterface
{
    /**
     * Create service instance
     *
     *
     * @return NoOfPermitsBothFieldsetPopulator
     */
    public function __construct(private TranslationHelperService $translator, private NoOfPermitsBaseInsetTextGenerator $noOfPermitsBaseInsetTextGenerator, private HtmlAdder $htmlAdder)
    {
    }

    /**
     * Populate the fieldset with elements based on the supplied options array
     *
     * @param mixed $form
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function populate($form, Fieldset $fieldset, array $options): void
    {
        $maxCanApplyFor = $options['maxCanApplyFor'];
        $maxPermitted = $options['maxPermitted'];
        $skipAvailabilityValidation = $options['skipAvailabilityValidation'];

        $insetAndBlurbTemplate = '<div class="govuk-inset-text">%s%s</div>' .
            '<p class="govuk-body"><strong>%s</strong><br><span class="hint">%s</span></p>';

        $maxPermittedHint = sprintf(
            $this->translator->translate('qanda.ecmt.number-of-permits.hint'),
            $maxCanApplyFor
        );

        $insetAndBlurb = sprintf(
            $insetAndBlurbTemplate,
            $this->noOfPermitsBaseInsetTextGenerator->generate($options, '%s<br><br>'),
            $this->translator->translate('qanda.ecmt.number-of-permits.both.inset.supplement'),
            $this->translator->translate('qanda.ecmt.number-of-permits.caption'),
            $maxPermittedHint
        );

        $this->htmlAdder->add($fieldset, 'insetAndBlurb', $insetAndBlurb);

        $fieldset->add(
            [
                'name' => 'combinedTotalChecker',
                'type' => EcmtNoOfPermitsCombinedTotalElement::class,
                'options' => [
                    'maxPermitted' => $maxPermitted
                ]
            ]
        );

        foreach ($options['emissionsCategories'] as $emissionsCategory) {
            $emissionsCategoryType = $emissionsCategory['type'];

            $textboxLabel = sprintf(
                'qanda.ecmt.number-of-permits.textbox.label.%s',
                $emissionsCategoryType
            );

            $fieldset->add(
                [
                    'type' => EcmtNoOfPermitsBothElement::class,
                    'name' => $emissionsCategoryType,
                    'options' => [
                        'label' => $textboxLabel,
                        'permitsRemaining' => $emissionsCategory['permitsRemaining'],
                        'skipAvailabilityValidation' => $skipAvailabilityValidation,
                        'emissionsCategory' => $emissionsCategoryType
                    ],
                    'attributes' => [
                        'value' => $emissionsCategory['value']
                    ]
                ]
            );
        }
    }
}
