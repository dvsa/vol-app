<?php

namespace Olcs\InputFilter;

use Common\InputFilter\DateParts;

class CancelledHearingDate extends DateParts
{
    #[\Override]
    protected function shouldValidateDateParts($context): bool
    {
        return !is_array($context) || !array_key_exists('isCancelled', $context) || $context['isCancelled'] === 'Y';
    }
}
