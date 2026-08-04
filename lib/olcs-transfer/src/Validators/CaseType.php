<?php

namespace Dvsa\Olcs\Transfer\Validators;

/**
 * @author Dmitry Golubev <d.e.golubev@gmail.com>
 */
class CaseType extends \Laminas\Validator\InArray
{
    protected $haystack = [
        'case_t_app',
        'case_t_imp',
        'case_t_lic',
        'case_t_tm',
    ];

    protected $messageTemplates = [
        self::NOT_IN_ARRAY => 'The input is not a valid case type',
    ];
}
