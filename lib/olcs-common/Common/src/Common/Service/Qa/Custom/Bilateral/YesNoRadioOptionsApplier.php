<?php

namespace Common\Service\Qa\Custom\Bilateral;

use Common\Form\Elements\Types\Radio;

class YesNoRadioOptionsApplier
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

    /**
     * Set the required options and attributes against the specified element
     *
     * @param Radio $radio
     * @param string $notSelectedMessage
     *
     * @psalm-param 'N'|'Y'|'radioValue'|null $value
     */
    public function applyTo(Radio $radio, array $valueOptions, string|null $value, $notSelectedMessage): void
    {
        $radio->setValueOptions($valueOptions);
        $radio->setAttributes($this->attributes);
        $radio->setValue($value);
        $radio->setOption('not_selected_message', $notSelectedMessage);
    }
}
