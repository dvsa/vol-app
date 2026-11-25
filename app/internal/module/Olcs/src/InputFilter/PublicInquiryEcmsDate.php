<?php

namespace Olcs\InputFilter;

use Common\InputFilter\DateParts;

class PublicInquiryEcmsDate extends DateParts
{
    #[\Override]
    protected function shouldValidateDateParts($context): bool
    {
        return !is_array($context) || !array_key_exists('isEcmsCase', $context) || $context['isEcmsCase'] === 'Y';
    }
}
