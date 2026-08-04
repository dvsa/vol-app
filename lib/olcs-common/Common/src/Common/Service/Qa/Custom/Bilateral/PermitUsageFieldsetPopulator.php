<?php

namespace Common\Service\Qa\Custom\Bilateral;

use Common\Service\Helper\TranslationHelperService;
use Common\Service\Qa\Custom\Common\HtmlAdder;
use Common\Service\Qa\FieldsetPopulatorInterface;
use Common\Service\Qa\RadioFieldsetPopulator;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Fieldset;

class PermitUsageFieldsetPopulator implements FieldsetPopulatorInterface
{
    /**
     * Create service instance
     *
     *
     * @return PermitUsageFieldsetPopulator
     */
    public function __construct(private RadioFieldsetPopulator $radioFieldsetPopulator, private TranslationHelperService $translator, private HtmlAdder $htmlAdder)
    {
    }

    /**
     * Populate the fieldset with a radio or html element based on the supplied options array
     *
     * @param mixed $form
     */
    #[\Override]
    public function populate($form, Fieldset $fieldset, array $options): void
    {
        if (count($options['options']) === 1) {
            $this->populateSingleOption($fieldset, $options['options'][0]);
            $form->get('Submit')->get('SubmitButton')->setValue('permits.button.continue');
        } else {
            $this->populateMultipleOptions($form, $fieldset, $options);
        }

        $fieldset->add(
            [
                'name' => 'warningVisible',
                'type' => Hidden::class,
                'attributes' => [
                    'value' => 0
                ]
            ]
        );
    }

    /**
     * Populate the fieldset with a html element in a single option scenario
     */
    private function populateSingleOption(Fieldset $fieldset, array $firstOption): void
    {
        $translationKey = $this->generateTranslationKey($firstOption['value'], 'single-option');

        $markup = sprintf(
            '<p class="govuk-body-l">%s</p>',
            $this->translator->translate($translationKey)
        );

        $this->htmlAdder->add($fieldset, 'qaHtml', $markup);

        $fieldset->add(
            [
                'name' => 'qaElement',
                'type' => Hidden::class,
                'attributes' => [
                    'value' => $firstOption['value'],
                ]
            ]
        );
    }

    /**
     * Populate the fieldset with radio buttons in a multiple option scenario
     */
    private function populateMultipleOptions(mixed $form, Fieldset $fieldset, array $options): void
    {
        foreach ($options['options'] as $key => $option) {
            $options['options'][$key]['label'] = $this->generateTranslationKey($option['value'], 'multiple-options');
        }

        $this->radioFieldsetPopulator->populate($form, $fieldset, $options);
    }

    /**
     * Generate a translation key based upon an option value
     *
     * @param string $optionValue
     * @param string $usageContext
     *
     * @return string
     */
    private function generateTranslationKey($optionValue, $usageContext)
    {
        return sprintf(
            'qanda.bilaterals.permit-usage.%s.%s',
            $usageContext,
            str_replace('_', '-', $optionValue)
        );
    }
}
