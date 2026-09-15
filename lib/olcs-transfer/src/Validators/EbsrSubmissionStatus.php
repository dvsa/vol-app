<?php

namespace Dvsa\Olcs\Transfer\Validators;

use Laminas\Validator\InArray;

/**
 * EBSR submission status Validator
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class EbsrSubmissionStatus extends InArray
{
    protected $haystack = [
        'ebsrs_processed',
        'ebsrs_processing',
        'ebsrs_submitted',
        'ebsrs_validating',
        'ebsrs_failed',
        'ebsrs_uploaded'
    ];

    protected $messageTemplates = [
        self::NOT_IN_ARRAY => 'The input is not a valid EBSR submission status',
    ];
}
