<?php

namespace Common\Form\Elements\Validators;

use Laminas\Validator\AbstractValidator;

/**
 * Checks a date is not in the future
 */
class DateNotInFuture extends AbstractValidator
{
    /**
     * Error codes
     * @const string
     */
    public const IN_FUTURE = 'inFuture';

    /**
     * Error messages
     * @var array
     */
    protected $messageTemplates = [
        self::IN_FUTURE => "This date is not allowed to be in the future",
    ];

    /**
     * Returns true if the date is not in the future
     *
     * @param  mixed $value
     * @return bool
     */
    #[\Override]
    public function isValid($value)
    {
        $date = strtotime($value);
        $today = strtotime(date('Y-m-d'));

        if ($date > $today) {
            $this->error(self::IN_FUTURE);
            return false;
        }

        return true;
    }
}
