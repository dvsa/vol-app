<?php

namespace Common\Service\Qa\Custom\Ecmt;

use Common\Service\Qa\FieldsetPopulatorInterface;
use Laminas\Form\Fieldset;

class RestrictedCountriesFieldsetPopulator implements FieldsetPopulatorInterface
{
    /**
     * Create service instance
     *
     *
     * @return RestrictedCountriesFieldsetPopulator
     */
    public function __construct(private YesNoRadioFactory $yesNoRadioFactory, private RestrictedCountriesMultiCheckboxFactory $restrictedCountriesMultiCheckboxFactory)
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
        $yesNoRadio = $this->yesNoRadioFactory->create('restrictedCountries');
        $yesNoRadio->setStandardValueOptions();

        $optionsYesNo = $options['yesNo'];
        $yesNoRadio->setValue(is_null($optionsYesNo) ? null : ($optionsYesNo === true ? 'Y' : 'N'));

        $valueOptions = [];
        foreach ($options['countries'] as $country) {
            $valueOptions[] = [
                'value' => $country['code'],
                'label' => $country['labelTranslationKey'],
                'selected' => $country['checked'],
            ];
        }

        if ($valueOptions !== []) {
            $valueOptions[0]['attributes'] = [
                'id' => 'RestrictedCountriesList'
            ];
        }

        $restrictedCountries = $this->restrictedCountriesMultiCheckboxFactory->create('yesContent');
        $restrictedCountries->setValueOptions($valueOptions);

        $yesNoRadio->setOption('yesContentElement', $restrictedCountries);

        $fieldset->add($yesNoRadio);
        $fieldset->add($restrictedCountries);
        $fieldset->setOption('radio-element', 'restrictedCountries');
        $fieldset->setLabel($options['questionKey']);
        $fieldset->setLabelAttributes(['class' => 'govuk-visually-hidden']);
    }
}
