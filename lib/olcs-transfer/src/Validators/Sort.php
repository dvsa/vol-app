<?php

namespace Dvsa\Olcs\Transfer\Validators;

class Sort extends \Laminas\Validator\AbstractValidator
{
    public const INVALID_SORT = 'invalidSort';

    protected $messageTemplates = [
        self::INVALID_SORT => 'The sort value is not valid',
    ];

    #[\Override]
    public function isValid($value)
    {
        if (!is_string($value)) {
            $this->error(self::INVALID_SORT);
            return false;
        }

        if (preg_match('/[^a-zA-Z0-9 \.\-_,]/', $value) !== 0) {
            $this->error(self::INVALID_SORT);
            return false;
        }
        return true;
    }
}
