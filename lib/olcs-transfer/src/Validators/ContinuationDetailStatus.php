<?php

/**
 * ContinuationDetailStatus validator
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Validators;

/**
 * ContinuationDetailStatus validator
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class ContinuationDetailStatus extends \Laminas\Validator\InArray
{
    protected $haystack = [
        'con_det_sts_prepared',
        'con_det_sts_printing',
        'con_det_sts_printed',
        'con_det_sts_unacceptable',
        'con_det_sts_acceptable',
        'con_det_sts_complete',
        'con_det_sts_error',
    ];

    protected $messageTemplates = [
        self::NOT_IN_ARRAY => 'The input was not a valid ContinuationDetail status',
    ];
}
