<?php

namespace Dvsa\Olcs\Transfer\Validators;

class SurrenderStatus extends \Laminas\Validator\InArray
{
    protected $haystack = [
        'surr_sts_approved',
        'surr_sts_comm_lic_docs_complete',
        'surr_sts_contacts_complete',
        'surr_sts_details_confirmed',
        'surr_sts_discs_complete',
        'surr_sts_lic_docs_complete',
        'surr_sts_signed',
        'surr_sts_start',
        'surr_sts_submitted',
        'surr_sts_withdrawn'
    ];

    protected $messageTemplates = [
        self::NOT_IN_ARRAY => 'Invalid status',
    ];
}
