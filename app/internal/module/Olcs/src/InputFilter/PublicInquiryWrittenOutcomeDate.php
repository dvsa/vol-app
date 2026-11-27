<?php

namespace Olcs\InputFilter;

use Common\InputFilter\DateParts;

class PublicInquiryWrittenOutcomeDate extends DateParts
{
    protected $continueIfEmpty = true;

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
        if (!is_array($context) || !array_key_exists('writtenOutcome', $context)) {
            return true;
        }

        $expected = match ($this->getName()) {
            'tcWrittenDecisionDate' => 'piwo_decision',
            'decisionLetterSentDate' => 'piwo_verbal',
            default => 'piwo_reason',
        };
        return $context['writtenOutcome'] === $expected;
    }
}
