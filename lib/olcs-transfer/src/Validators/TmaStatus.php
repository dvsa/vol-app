<?php

namespace Dvsa\Olcs\Transfer\Validators;

class TmaStatus extends \Laminas\Validator\InArray
{
    protected $haystack = [
        'tmap_st_incomplete',
        'tmap_st_awaiting_signature',
        'tmap_st_tm_signed',
        'tmap_st_operator_signed',
        'tmap_st_postal_application',
        'tmap_st_received',
        'tmap_st_details_submitted',
        'tmap_st_details_checked',
        'tmap_st_operator_approved',
    ];

    protected $messageTemplates = [
        self::NOT_IN_ARRAY => 'Invalid status',
    ];
}
