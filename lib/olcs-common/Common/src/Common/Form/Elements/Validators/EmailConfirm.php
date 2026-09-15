<?php

/**
 * Custom validator for confirming an email address
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Common\Form\Elements\Validators;

use Laminas\Validator\Identical;

/**
 * Custom validator for confirming an email address
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class EmailConfirm extends Identical
{
    /**
     * Error messages
     * @var array
     */
    protected $messageTemplates = [
        self::NOT_SAME      => 'error.form-validator.email-confirm.not-same',
        self::MISSING_TOKEN => 'error.form-validator.email-confirm.missing-token',
    ];
}
