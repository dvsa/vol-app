<?php

namespace Common\Service\Qa\Custom\Ecmt;

use Common\Form\Elements\Custom\EcmtNoOfPermitsEmissionsCategoryHiddenElement;
use Common\Form\Elements\Custom\EcmtNoOfPermitsSingleElement;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Qa\Custom\Common\HtmlAdder;
use Common\Service\Qa\FieldsetPopulatorInterface;
use Laminas\Form\Fieldset;

class NoOfPermitsSingleFieldsetPopulator implements FieldsetPopulatorInterface
{
    /**
     * Create service instance
     *
     *
     * @return NoOfPermitsSingleFieldsetPopulator
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
        $emissionsCategory = $options['emissionsCategories'][0];
        $emissionsCategoryType = $emissionsCategory['type'];
        $maxCanApplyFor = $options['maxCanApplyFor'];
        $maxPermitted = $options['maxPermitted'];
        $skipAvailabilityValidation = $options['skipAvailabilityValidation'];

        $insetSupplementTranslationKey = sprintf(
            'qanda.ecmt.number-of-permits.single.inset.supplement.%s',
            $emissionsCategoryType
        );

        $insetAndBlurbTemplate = '<div class="govuk-inset-text">%s%s</div>' .
            '<p class="govuk-body"><strong>%s</strong><br><span class="hint">%s</span></p>';

        $maxPermittedHint = sprintf(
            $this->translator->translate('qanda.ecmt.number-of-permits.hint'),
            $maxCanApplyFor
        );

        $insetAndBlurb = sprintf(
            $insetAndBlurbTemplate,
            $this->noOfPermitsBaseInsetTextGenerator->generate($options, '%s<br><br>'),
            $this->translator->translate($insetSupplementTranslationKey),
            $this->translator->translate('qanda.ecmt.number-of-permits.caption'),
            $maxPermittedHint
        );

        $this->htmlAdder->add($fieldset, 'insetAndBlurb', $insetAndBlurb);

        $textboxLabel = sprintf(
            'qanda.ecmt.number-of-permits.textbox.label.%s',
            $emissionsCategoryType
        );

        $fieldset->add(
            [
                'type' => EcmtNoOfPermitsEmissionsCategoryHiddenElement::class,
                'name' => 'emissionsCategory',
                'options' => [
                    'expectedValue' => $emissionsCategoryType
                ],
                'attributes' => [
                    'value' => $emissionsCategoryType
                ]
            ]
        );

        $fieldset->add(
            [
                'type' => EcmtNoOfPermitsSingleElement::class,
                'name' => 'permitsRequired',
                'options' => [
                    'label' => $textboxLabel,
                    'maxPermitted' => $maxPermitted,
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
