<?php

namespace Olcs\InputFilter;

use Common\InputFilter\DateTimeParts;

class ImpoundingHearingDate extends DateTimeParts
{
    #[\Override]
    protected function shouldValidateDateParts($context): bool
    {
        return !is_array($context) || !array_key_exists('impoundingType', $context)
            || $context['impoundingType'] === 'impt_hearing';
    }
}
