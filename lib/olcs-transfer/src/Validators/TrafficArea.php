<?php

namespace Dvsa\Olcs\Transfer\Validators;

/**
 * Traffic Area Validator
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class TrafficArea extends InArrayExtra
{
    protected $haystack = ['B', 'C', 'D', 'F', 'G', 'H', 'K', 'M', 'N'];

    protected $messageTemplates = [
        self::NOT_IN_ARRAY => 'The input is not a valid Traffic Area Code',
    ];
}
