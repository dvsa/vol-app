<?php

namespace Common\Service\Qa\Custom\Ecmt;

use Common\Form\Elements\Custom\EcmtNoOfPermitsEitherElement;
use Common\Form\Elements\InputFilters\QaRadio;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Qa\Custom\Common\HtmlAdder;
use Common\Service\Qa\FieldsetPopulatorInterface;
use Laminas\Form\Fieldset;

class NoOfPermitsEitherFieldsetPopulator implements FieldsetPopulatorInterface
{
    /**
     * Create service instance
     *
     *
     * @return NoOfPermitsEitherFieldsetPopulator
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
        $insetAndSection1Header = sprintf(
            '%s<p class="govuk-body"><strong>1. %s</strong></p><p class="govuk-body">%s</p>',
            $this->noOfPermitsBaseInsetTextGenerator->generate($options, '<div class="govuk-inset-text">%s</div>'),
            $this->translator->translate('qanda.ecmt.number-of-permits.either.section-1.heading'),
            $this->translator->translate('qanda.ecmt.number-of-permits.either.section-1.blurb')
        );

        $this->htmlAdder->add($fieldset, 'insetAndSection1Header', $insetAndSection1Header);

        $valueOptions = [];
        $textboxValue = null;
        $radioValue = null;
        $emissionsCategoryPermitsRemaining = [];
        foreach ($options['emissionsCategories'] as $emissionsCategory) {
            $emissionsCategoryType = $emissionsCategory['type'];

            $label = sprintf(
                'qanda.ecmt.number-of-permits.either.radio-label.%s',
                $emissionsCategoryType
            );

            $emissionsCategoryPermitsRemaining[$emissionsCategoryType] = $emissionsCategory['permitsRemaining'];
            $valueOptions[$emissionsCategoryType] = $label;

            if ($emissionsCategory['value']) {
                $textboxValue = $emissionsCategory['value'];
                $radioValue = $emissionsCategory['type'];
            }
        }

        $fieldset->add(
            [
                'name' => 'emissionsCategory',
                'type' => QaRadio::class,
                'options' => [
                    'value_options' => $valueOptions,
                    'not_selected_message' => 'qanda.ecmt.number-of-permits.either.error.select-emissions-category'
                ],
                'attributes' => [
                    'value' => $radioValue
                ],
            ]
        );

        $section2Header = sprintf(
            '<p class="govuk-body govuk-!-margin-top-6"><strong>2. %s</strong></p>',
            $this->translator->translate('qanda.ecmt.number-of-permits.either.section-2.heading')
        );

        $this->htmlAdder->add($fieldset, 'section2Header', $section2Header);

        $maxCanApplyFor = $options['maxCanApplyFor'];
        $maxPermitted = $options['maxPermitted'];
        $skipAvailabilityValidation = $options['skipAvailabilityValidation'];

        $textboxHint = sprintf(
            $this->translator->translate('qanda.ecmt.number-of-permits.hint'),
            $maxCanApplyFor
        );

        $fieldset->add(
            [
                'type' => EcmtNoOfPermitsEitherElement::class,
                'name' => 'permitsRequired',
                'options' => [
                    'label' => 'qanda.ecmt.number-of-permits.caption',
                    'hint' => $textboxHint,
                    'maxPermitted' => $maxPermitted,
                    'skipAvailabilityValidation' => $skipAvailabilityValidation,
                    'emissionsCategoryPermitsRemaining' => $emissionsCategoryPermitsRemaining
                ],
                'attributes' => [
                    'value' => $textboxValue
                ]
            ]
        );
    }
}
