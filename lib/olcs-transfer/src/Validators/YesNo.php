<?php

/**
 * Yes/No validator
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Validators;

/**
 * Yes/No validator
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class YesNo extends \Laminas\Validator\InArray
{
    protected $haystack = ['Y', 'N'];

    protected $messageTemplates = [
        self::NOT_IN_ARRAY => 'The input was not found, must be "Y" or "N"',
    ];
}
