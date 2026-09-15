<?php

namespace Common\Validator;

/**
 * @package Common\Validator
 */
class DateInFuture extends \Laminas\Validator\AbstractValidator
{
    public const NOT_IN_FUTURE = 'notInFuture';

    /**
     * Error messages
     * @var array
     */
    protected $messageTemplates = [
        self::NOT_IN_FUTURE => "The date must be in the future",
    ];

    /**
     * is the value valid
     *
     * @param  mixed $value
     * @param  array $context
     *
     * @return bool
     */
    #[\Override]
    public function isValid($value)
    {
        $valueDateTime = new \DateTime($value);
        $interval = $valueDateTime->diff($this->getNowDateTime());
        if ($interval->invert !== 1) {
            $this->error(self::NOT_IN_FUTURE);
            return false;
        }

        return true;
    }

    /**
     * Get Now
     *
     * @return \DateTime
     */
    protected function getNowDateTime()
    {
        return new \DateTime();
    }
}
