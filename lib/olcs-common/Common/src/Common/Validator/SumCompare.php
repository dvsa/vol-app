<?php

namespace Common\Validator;

/**
 * Class DigitsCompare - used to validate sum of two fields with a third via a legal operator:
 * 'gt' -> greater than
 * 'gte' -> greater than or equal to
 * 'lt' -> less than
 * 'lte' -> less than or equal to
 * @package Common\Validator
 */
class SumCompare extends AbstractCompare
{
    /**
     * @var array
     */
    protected $messageVariables = [
        'compare_to_label' => 'compareToLabel',
    ];

    /**
     * Error messages
     * @var array
     */
    protected $messageTemplates = [
        self::NOT_GTE => "The sum must be greater than or equal to '%compare_to_label%'",
        self::NOT_GT => "The sum must be greater than '%compare_to_label%'",
        self::NOT_LTE => "The sum must be less than or equal to '%compare_to_label%'",
        self::NOT_LT => "The sum must be less than '%compare_to_label%'",
        self::INVALID_OPERATOR => "Invalid operator",
        self::INVALID_FIELD => "Input field being compared to doesn't exist",
        self::NO_COMPARE => "Unable to compare with '%compare_to_label%'"
    ];


    /**
     * Other field to sum the validated field with prior to comparison
     * @var mixed
     */
    protected $sumWith;
    private bool $allowEmpty;

    /**
     * @return $this
     */
    public function setSumWith(mixed $sumWith)
    {
        $this->sumWith = $sumWith;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSumWith()
    {
        return $this->sumWith;
    }

    /**
     * @param bool $allowEmpty
     * @return $this
     */
    public function setAllowEmpty($allowEmpty)
    {
        $this->allowEmpty = $allowEmpty;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAllowEmpty()
    {
        return $this->allowEmpty;
    }

    /**
     * Sets options
     *
     * @param array $options
     */
    #[\Override]
    public function setOptions($options = []): static
    {
        if (isset($options['sum_with'])) {
            $this->setSumWith($options['sum_with']);
        }

        if (isset($options['allow_empty'])) {
            $this->setAllowEmpty($options['allow_empty']);
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
        if ($this->getAllowEmpty() && empty($value)) {
            return true;
        }

        if (empty($value)) {
            $this->error(self::INVALID_FIELD);
            return false;
        }

        if (empty($context[$this->getCompareTo()])) {
            $this->error(self::INVALID_FIELD);
            return false;
        }

        $compareToValue = $context[$this->getCompareTo()];
        $sumWithValue = $context[$this->getSumWith()];
        return ($this->isValidForOperator($value + $sumWithValue, $compareToValue));
    }
}
