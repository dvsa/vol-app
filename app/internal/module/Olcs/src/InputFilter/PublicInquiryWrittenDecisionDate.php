<?php

namespace Olcs\InputFilter;

use Common\InputFilter\DateParts;

class PublicInquiryWrittenDecisionDate extends DateParts
{
    #[\Override]
    protected function shouldValidateDateParts($context): bool
    {
        return !is_array($context) || !array_key_exists('writtenOutcome', $context)
            || $context['writtenOutcome'] === 'piwo_decision';
    }
}
