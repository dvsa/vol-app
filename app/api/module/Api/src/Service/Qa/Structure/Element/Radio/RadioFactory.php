<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Radio;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options\OptionList;
use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableText;

class RadioFactory
{
    /**
     * Create and return a Radio instance
     *
     *
     * @return Radio
     */
    public function create(OptionList $optionList, TranslateableText $notSelectedMessage, mixed $value)
    {
        return new Radio($optionList, $notSelectedMessage, $value);
    }
}
