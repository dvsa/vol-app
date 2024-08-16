<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Checkbox;

use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableText;

class CheckboxFactory
{
    /**
     * Create and return a Checkbox instance
     *
     * @param bool $checked
     *
     * @return Checkbox
     */
    public function create(TranslateableText $label, TranslateableText $notCheckedMessage, $checked)
    {
        return new Checkbox($label, $notCheckedMessage, $checked);
    }
}
