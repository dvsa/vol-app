<?php

namespace Olcs\InputFilter;

use Common\InputFilter\DateParts;

class FeePaymentDate extends DateParts
{
    #[\Override]
    protected function shouldValidateDateParts($context): bool
    {
        if (!is_array($context) || !array_key_exists('paymentType', $context)) {
            return true;
        }

        return $this->getName() === 'chequeDate'
            ? $context['paymentType'] === 'fpm_cheque'
            : $context['paymentType'] !== 'fpm_card_offline';
    }
}
