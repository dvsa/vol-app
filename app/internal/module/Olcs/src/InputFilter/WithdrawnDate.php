<?php

namespace Olcs\InputFilter;

use Common\InputFilter\DateParts;

class WithdrawnDate extends DateParts
{
    #[\Override]
    protected function shouldValidateDateParts($context): bool
    {
        return !is_array($context) || !array_key_exists('isWithdrawn', $context) || $context['isWithdrawn'] === 'Y';
    }
}
