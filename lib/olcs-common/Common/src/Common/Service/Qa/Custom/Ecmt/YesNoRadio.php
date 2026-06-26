<?php

namespace Common\Service\Qa\Custom\Ecmt;

use Common\Form\Elements\Types\Radio;

class YesNoRadio extends Radio
{
    /** @var array */
    protected $attributes = [
        'id' => 'yesNoRadio',
        'radios_wrapper_attributes' => [
            'id' => 'yesNoRadio',
            'class' => 'govuk-radios--conditional',
            'data-module' => 'radios',
        ]
    ];

    /** @var array */
    protected $standardValueOptions = [
        'yes' => [
            'label' => 'Yes',
            'value' => 'Y',
            'attributes' => [
                'data-aria-controls' => 'RestrictedCountriesList',
            ],
        ],
        'no' => [
            'label' => 'No',
            'value' => 'N',
        ]
    ];

    /**
     * Set the standard value options for this type
     */
    public function setStandardValueOptions(): void
    {
        $this->setValueOptions($this->standardValueOptions);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getInputSpecification(): array
    {
        $spec = parent::getInputSpecification();

        $spec['validators'] = [
            new YesNoRadioValidator(
                $this->options['yesContentElement']
            )
        ];

        return $spec;
    }
}
