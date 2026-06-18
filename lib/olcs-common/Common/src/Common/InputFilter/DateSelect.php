<?php

namespace Common\InputFilter;

use Laminas\InputFilter\Input as LaminasInput;

/**
 * Class DateSelect
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class DateSelect extends LaminasInput
{
    /**
     * @return mixed
     */
    #[\Override]
    public function getRawValue()
    {
        // if all elements of the date are empty then return null
        if (
            is_array($this->value) &&
            empty($this->value['day']) &&
            empty($this->value['month']) &&
            empty($this->value['year'])
        ) {
            return null;
        }

        return $this->value;
    }
}
