<?php

namespace Dvsa\Olcs\Transfer\Validators;

/**
 * ApplicationStatus Validator
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationStatus extends \Laminas\Validator\InArray
{
    protected $haystack = [
        'apsts_not_submitted',
        'apsts_granted',
        'apsts_consideration',
        'apsts_valid',
        'apsts_withdrawn',
        'apsts_refused',
        'apsts_ntu',
        'apsts_curtailed',
        'apsts_cancelled',
    ];

    protected $messageTemplates = [
        self::NOT_IN_ARRAY => 'The input is not a valid application status',
    ];
}
