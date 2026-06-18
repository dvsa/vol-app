<?php

namespace Dvsa\Olcs\Transfer\Validators;

use Laminas\Validator\StringLength;

/**
 * Ensure the username matches the newly defined required criteria.
 */
class UsernameCreate extends StringLength
{
    public const USERNAME_LENGTH_MIN = 4;
    public const USERNAME_LENGTH_MAX = 40;
    public const USERNAME_INVALID = 'usernameCreateInvalid';

    /**
     * Sets validator options
     *
     * @param  array $options
     */
    public function __construct($options = [])
    {
        $options['min'] = self::USERNAME_LENGTH_MIN;
        $options['max'] = self::USERNAME_LENGTH_MAX;

        $this->messageTemplates[self::USERNAME_INVALID] = 'error.form-validator.usernameCreate.invalid';

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
            if (preg_match('/^[a-z][a-z0-9]{3,39}$/', $value)) {
                return true;
            }

            $this->error(self::USERNAME_INVALID);
        }

        return false;
    }
}
