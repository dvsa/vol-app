<?php

namespace Common\Validator;

/**
 * Class DigitsCompare - used to validate two digits via a legal operator:
 * 'gt' -> greater than
 * 'gte' -> greater than or equal to
 * 'lt' -> less than
 * 'lte' -> less than or equal to
 * @package Common\Validator
 */
class NumberCompare extends AbstractCompare
{
    /**
     * Error codes
     * @const string
     */
    public const MAX_DIFF_EXCEEDED = 'maxDiffExceeded';

    /**
     * @var array
     */
    protected $messageVariables = [
        'compare_to_label' => 'compareToLabel',
        'max_diff' => 'maxDiff',
    ];

    /**
     * Error messages
     * @var array
     */
    protected $messageTemplates = [
        self::NOT_GTE => "This number must be greater than or equal to '%compare_to_label%'",
        self::NOT_GT => "This number must be greater than '%compare_to_label%'",
        self::NOT_LTE => "This number must be less than or equal to '%compare_to_label%'",
        self::NOT_LT => "This number must be less than '%compare_to_label%'",
        self::MAX_DIFF_EXCEEDED
            => "Difference between this number and '%compare_to_label%' must be less than or equal to %max_diff%",
        self::INVALID_OPERATOR => "Invalid operator",
        self::INVALID_FIELD => "Input field being compared to doesn't exist",
        self::NO_COMPARE => "Unable to compare with '%compare_to_label%'"
    ];

    /**
     * Max difference between the compared numbers which makes it valid
     * @var mixed
     */
    protected $maxDiff;

    /**
     * @return $this
     */
    public function setMaxDiff(mixed $maxDiff)
    {
        $this->maxDiff = $maxDiff;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMaxDiff()
    {
        return $this->maxDiff;
    }

    /**
     * Sets options
     *
     * @param  array $options
     * @return NumberCompare
     */
    #[\Override]
    public function setOptions($options = [])
    {
        if (isset($options['max_diff'])) {
            $this->setMaxDiff($options['max_diff']);
        }

        return parent::setOptions($options);
    }

    /**
     * Returns true if and only if a token has been set and the provided value
     * matches that token.
     *
     * @param  mixed $value
     * @return bool
     */
    #[\Override]
    public function isValid($value, array $context = null)
    {
        if (empty($value)) {
            $this->error(self::INVALID_FIELD);
            return false;
        }

        if (empty($context[$this->getCompareTo()])) {
            $this->error(self::INVALID_FIELD);
            return false;
        }

        $compareToValue = $context[$this->getCompareTo()];

        return ($this->isValidForOperator($value, $compareToValue) && $this->isWithinMaxDiff($value, $compareToValue));
    }

    /**
     * Returns true if and only if numbers are within max_diff (if set).
     *
     * @return bool
     */
    private function isWithinMaxDiff(mixed $value, mixed $compareToValue)
    {
        $maxDiff = $this->getMaxDiff();

        if (!empty($maxDiff)) {
            $diff = abs($value - $compareToValue);

            if ($diff > $maxDiff) {
                $this->error(self::MAX_DIFF_EXCEEDED);
                return false;
            }
        }

        return true;
    }
}
