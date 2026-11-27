<?php

namespace Olcs\InputFilter;

use Common\InputFilter\DateParts;

class PublicInquiryWrittenDecisionDate extends DateParts
{
    #[\Override]
    public function isValid($context = null)
    {
        if (
            is_array($context)
            && array_key_exists('writtenOutcome', $context)
            && !$this->shouldValidateDateParts($context)
        ) {
            return true;
        }

        return parent::isValid($context);
    }

    #[\Override]
    protected function shouldValidateDateParts($context): bool
    {
        return !is_array($context) || !array_key_exists('writtenOutcome', $context)
            || $context['writtenOutcome'] === 'piwo_decision';
    }
}
