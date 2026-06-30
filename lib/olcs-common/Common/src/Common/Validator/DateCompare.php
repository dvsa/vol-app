<?php

namespace Common\Validator;

use Common\Filter\DateTimeSelectNullifier;

/**
 * Class DateCompare - used to validate two dates via a legal operator:
 * 'gt' -> greater than
 * 'gte' -> greater than or equal to
 * 'lt' -> less than
 * 'lte' -> less than or equal to
 * @package Common\Validator
 */
class DateCompare extends AbstractCompare
{
    public const DATE_FORMAT = 'Y-m-d';

    public const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * Error messages
     * @var array
     */
    protected $messageTemplates = [
        self::NOT_GTE => "This date must be after or the same as '%compare_to_label%'",
        self::NOT_GT => "This date must be after '%compare_to_label%'",
        self::NOT_LTE => "This date must be before or the same as '%compare_to_label%'",
        self::NOT_LT => "This date must be before '%compare_to_label%'",
        self::INVALID_OPERATOR => "Invalid operator",
        self::INVALID_FIELD => "Input field being compared to doesn't exist",
        self::NO_COMPARE => "Unable to compare with '%compare_to_label%'"
    ];

    /**
     * Whether we're comparing the time also
     *
     * @var bool
     */
    protected $hasTime = false;

    /**
     * @internal This is out of scope from ZF 2.4+.  This is only used for the custom validator.
     *           There is no need to remove this for compatibility.
     *
     * Whether the date can be empty
     *
     * @var bool
     */
    protected $allowEmpty = false;

    /**
     * @param bool $hasTime
     * @return $this
     */
    public function setHasTime($hasTime)
    {
        $this->hasTime = (bool) $hasTime;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasTime()
    {
        return $this->hasTime;
    }

    /**
     * @param bool $allowEmpty allow empty
     * @return $this
     */
    public function setAllowEmpty($allowEmpty)
    {
        $this->allowEmpty = (bool) $allowEmpty;
        return $this;
    }

    /**
     * @return bool
     */
    public function getAllowEmpty()
    {
        return $this->allowEmpty;
    }

    /**
     * Sets options
     *
     * @param  array $options
     * @return DateCompare
     */
    #[\Override]
    public function setOptions($options = [])
    {
        if (isset($options['has_time'])) {
            $this->setHasTime($options['has_time']);
        }

        if (isset($options['allow_empty'])) {
            $this->setAllowEmpty($options['allow_empty']);
        }

        return parent::setOptions($options);
    }

    /**
     * Returns true if and only if a token has been set and the provided value
     * matches that token.
     * NB: if allow_empty option set to true, isValid will return true if original date
     * or compareTo date is empty
     *
     * @param  mixed $value
     * @return bool
     */
    #[\Override]
    public function isValid($value, array $context = null)
    {
        if (empty($value) && $this->getAllowEmpty()) {
            return true;
        }

        if (empty($value)) {
            $this->error(self::INVALID_FIELD); //@TO~DO~
            return false;
        }

        //  get compare To date(time) value
        $compareToDate = $this->getCompareToDate($context);
        if (empty($compareToDate) && $this->getAllowEmpty()) {
            return true;
        }

        if ($compareToDate === false) {
            return false;
        }

        //  get date(time) value
        $dateFormat = ($this->hasTime() ? self::DATETIME_FORMAT : self::DATE_FORMAT);

        $valueDate = \DateTime::createFromFormat($dateFormat, $value);

        if ($valueDate === false) {
            $this->error(self::NO_COMPARE); //@TO~DO~
            return false;
        }

        if (! $this->hasTime()) {
            $valueDate->setTime(0, 0, 0);
        }

        return $this->isValidForOperator($valueDate, $compareToDate);
    }

    /**
     * Generate the compareDate value.
     *
     * @param $compareToValue
     * @return \DateTime
     */
    protected function getCompareToDate(array|null $context)
    {
        $fieldName = $this->getCompareTo();

        if (!isset($context[$fieldName])) {
            $this->error(self::INVALID_FIELD);
            return false;
        }

        //  because we can't recognise type of CompareTo, is it Date or DateTime,
        //  we take the bigger object DateTime and prepare field value for this object
        $fieldValue = $context[$fieldName] +
            [
                'hour' => '00',
                'minute' => '00',
            ];

        $value = (new DateTimeSelectNullifier())->filter($fieldValue);

        $date = \DateTime::createFromFormat(self::DATETIME_FORMAT, $value);
        if ($date === false) {
            $this->error(self::INVALID_FIELD);
        }

        return $date;
    }
}
