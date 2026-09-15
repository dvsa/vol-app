<?php

namespace Dvsa\Olcs\Transfer\Validators;

/**
 * Locale Validator
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class Locale extends \Laminas\Validator\InArray
{
    protected $haystack = [
        'en_GB',
        'cy_GB',
        'en_NI',
        'cy_NI',
    ];

    protected $messageTemplates = [
        self::NOT_IN_ARRAY => 'The input is not a valid locale',
    ];
}
