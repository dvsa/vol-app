<?php

namespace Dvsa\Olcs\Transfer\Validators;

/**
 * LicenceStatus Validator
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class LicenceStatus extends \Laminas\Validator\InArray
{
    protected $haystack = [
        'lsts_consideration',
        'lsts_not_submitted',
        'lsts_suspended',
        'lsts_valid',
        'lsts_curtailed',
        'lsts_granted',
        'lsts_surr_consideration',
        'lsts_surrendered',
        'lsts_withdrawn',
        'lsts_refused',
        'lsts_revoked',
        'lsts_ntu',
        'lsts_terminated',
        'lsts_cns',
        'lsts_unlicenced',
        'lsts_cancelled',
    ];

    protected $messageTemplates = [
        self::NOT_IN_ARRAY => 'The input is not a valid Licence status',
    ];
}
