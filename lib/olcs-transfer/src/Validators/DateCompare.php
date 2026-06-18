<?php

namespace Dvsa\Olcs\Transfer\Validators;

use Laminas\Validator\AbstractValidator;

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
     * @var bool
     */
    protected $hasTime;

    /**
     * @param bool $hasTime
     * @return $this
     */
    public function setHasTime($hasTime)
    {
        $this->hasTime = $hasTime;
        return $this;
    }

    /**
     * @return bool
     */
    public function getHasTime()
    {
        return $this->hasTime;
    }

    /**
     * Sets options
     *
     * @param  mixed $options
     */
    #[\Override]
    public function setOptions($options = []): AbstractValidator
    {
        if (isset($options['has_time'])) {
            $this->setHasTime($options['has_time']);
        }

        return parent::setOptions($options);
    }

    /**
     * Returns true if and only if a token has been set and the provided value
     * matches that token.
     *
     * @param  mixed $value
     * @param  array $context
     * @return bool
     */
    #[\Override]
    public function isValid($value, array $context = null)
    {
        if (empty($value)) {
            $this->error(self::INVALID_FIELD);
            return false;
        }

        if (!isset($context[$this->getCompareTo()])) {
            $this->error(self::INVALID_FIELD);
            return false;
        }

        $compareToValue = $context[$this->getCompareTo()];

        if (empty($compareToValue)) {
            $this->error(self::INVALID_FIELD);
            return false;
        }

        $compareDateValue = $this->generateCompareDateValue($compareToValue);

        if (!empty($this->getMessages())) {
            // process any errors from sub class
            return false;
        }

        //if we're comparing a field which also has a time
        if ($this->getHasTime()) {
            $dateValue = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
        } else {
            $dateValue = \DateTime::createFromFormat('Y-m-d', $value);
        }

        if (!$dateValue) {
            $this->error(self::NO_COMPARE);
            return false;
        }

        $dateValue->setTime(0, 0, 0);

        return $this->isValidForOperator($dateValue, $compareDateValue);
    }

    /**
     * Generate the compareDate value.
     *
     * @param $compareToValue
     * @return \DateTime
     */
    protected function generateCompareDateValue($compareToValue)
    {
        $compareDateValue = \DateTime::createFromFormat('Y-m-d', $compareToValue);
        $compareDateValue->setTime(0, 0, 0);

        return $compareDateValue;
    }
}
