<?php

/**
 * Ensure the username matches the required criteria
 */

namespace Dvsa\Olcs\Transfer\Validators;

use Laminas\Validator\StringLength;

/**
 * Ensure the username matches the required criteria
 */
class Username extends StringLength
{
    public const USERNAME_LENGTH_MIN = 2;
    public const USERNAME_LENGTH_MAX = 40;
    public const USERNAME_INVALID = 'usernameInvalid';

    /**
     * Sets validator options
     *
     * @param  array $options
     */
    public function __construct($options = [])
    {
        $options['min'] = self::USERNAME_LENGTH_MIN;
        $options['max'] = self::USERNAME_LENGTH_MAX;

        $this->messageTemplates[self::USERNAME_INVALID] = 'error.form-validator.username.invalid';

        parent::__construct($options);
    }

    /**
     * Check if username is valid
     *
     * @param mixed $value
     * @return bool
     */
    #[\Override]
    public function isValid($value)
    {
        if (parent::isValid($value)) {
            if (preg_match('/^[0-9a-zA-Z#\$%\'\+\-\/\=\?\^_\.@`\|~",\:;\<\>]*$/', $value)) {
                return true;
            }

            $this->error(self::USERNAME_INVALID);
        }

        return false;
    }
}
