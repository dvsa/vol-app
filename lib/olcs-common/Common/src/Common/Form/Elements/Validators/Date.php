<?php

namespace Common\Form\Elements\Validators;

use Laminas\Validator\Date as LaminasDate;

/**
 * Override ZFs date validation messages (As they are a bit rubbish)
 */
class Date extends LaminasDate
{
    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = [
        self::INVALID        => "Please select a date",
        self::INVALID_DATE   => "The input does not appear to be a valid value",
        self::FALSEFORMAT    => "The input does not fit the date format '%format%'",
    ];

    /**
     * Returns true if $value is a DateTime instance or can be converted into one.
     * Returns false if $value is empty of invalid  and gives an error message
     *
     * @param string|array|int|\DateTimeInterface $value Value
     *
     * @return bool
     */
    #[\Override]
    public function isValid($value)
    {
        if (is_null($value)) {
            return true;
        }

        $this->setValue($value);

        if (empty($value)) {
            $this->error(self::INVALID);
            return false;
        }

        if (!$this->convertToDateTime($value, false)) {
            $this->error(self::INVALID_DATE);
            return false;
        }

        return true;
    }
}
