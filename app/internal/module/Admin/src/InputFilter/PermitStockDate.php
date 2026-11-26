<?php

namespace Admin\InputFilter;

use Common\InputFilter\DateParts;
use Common\RefData;

class PermitStockDate extends DateParts
{
    #[\Override]
    protected function shouldValidateDateParts($context): bool
    {
        return !is_array($context) || !in_array(
            (string) ($context['irhpPermitType'] ?? ''),
            [
                (string) RefData::ECMT_REMOVAL_PERMIT_TYPE_ID,
                (string) RefData::CERT_ROADWORTHINESS_VEHICLE_PERMIT_TYPE_ID,
                (string) RefData::CERT_ROADWORTHINESS_TRAILER_PERMIT_TYPE_ID,
            ],
            true
        );
    }
}
