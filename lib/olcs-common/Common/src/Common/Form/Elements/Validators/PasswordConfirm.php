<?php

/**
 * Custom validator for confirming a password
 */

namespace Common\Form\Elements\Validators;

use Laminas\Validator\Identical;

/**
 * Custom validator for confirming a password
 */
class PasswordConfirm extends Identical
{
    /**
     * Error messages
     * @var array
     */
    protected $messageTemplates = [
        self::NOT_SAME      => 'error.form-validator.password-confirm.not-same',
        self::MISSING_TOKEN => 'error.form-validator.password-confirm.missing-token',
    ];
}
