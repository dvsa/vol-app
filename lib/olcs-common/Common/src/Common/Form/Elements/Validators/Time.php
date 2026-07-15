<?php

/**
 * Checks a time hh:mm is valid
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Common\Form\Elements\Validators;

use Laminas\Validator\Date as DateValidator;

/**
 * Checks a time hh:mm is valid
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */


class Time extends DateValidator
{
    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = [
        self::INVALID        => "Invalid type given. A time of format hh:mm was expected",
        self::INVALID_DATE   => "The input does not appear to be a valid time, expected format hh:mm",
        self::FALSEFORMAT    => "The input does not fit the time format hh:mm",
    ];
}
