<?php

namespace CommonTest\Common\Form\View\Helper\Extended\Stub;

use Common\Form\View\Helper\FormRadio;
use Laminas\Form\Element\MultiCheckbox;

class FormRadioStub extends FormRadio
{
    #[\Override]
    public function renderOptions(MultiCheckbox $element, array $options, array $selectedOptions, array $attributes): string
    {
        return parent::renderOptions($element, $options, $selectedOptions, $attributes);
    }
}
