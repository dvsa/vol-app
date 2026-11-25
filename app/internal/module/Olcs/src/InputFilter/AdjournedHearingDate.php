<?php

namespace Olcs\InputFilter;

use Common\InputFilter\DateTimeParts;

class AdjournedHearingDate extends DateTimeParts
{
    #[\Override]
    protected function shouldValidateDateParts($context): bool
    {
        return !is_array($context) || !array_key_exists('isAdjourned', $context) || $context['isAdjourned'] === 'Y';
    }
}
