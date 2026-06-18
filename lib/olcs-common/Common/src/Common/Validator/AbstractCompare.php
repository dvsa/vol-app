<?php

namespace Common\Validator;

use Laminas\Validator\AbstractValidator;

/**
 * An abstract Compare Validator class
 * 'gt' -> greater than
 * 'gte' -> greater than or equal to
 * 'lt' -> less than
 * 'lte' -> less than or equal to
 * @package Common\Validator
 */
abstract class AbstractCompare extends AbstractValidator
{
    /**
     * Error codes
     * @const string
     */
    public const NOT_GTE = 'notGreaterThanOrEqual';

    public const NOT_GT = 'notGreaterThan';

    public const NOT_LTE = 'notLessThanOrEqual';

    public const NOT_LT = 'notLessThan';

    public const INVALID_OPERATOR = 'invalidOperator';

    public const INVALID_FIELD = 'invalidField';

    public const NO_COMPARE = 'noCompare';

    /**
     * @var array
     */
    protected $messageVariables = [
        'compare_to_label' => 'compareToLabel'
    ];

    /**
     * context field against which to validate
     * @var string
     */
    protected $compareTo;

    /**
     * Type of compare to do
     * @var string
     */
    protected $operator;

    /**
     * Label of compare to field to use in error message
     * @var string
     */
    protected $compareToLabel;

    /**
     * @param string $compareTo
     * @return $this
     */
    public function setCompareTo($compareTo)
    {
        $this->compareTo = $compareTo;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompareTo()
    {
        return $this->compareTo;
    }

    /**
     * @param string $compareToLabel
     * @return $this
     */
    public function setCompareToLabel($compareToLabel)
    {
        $this->compareToLabel = $compareToLabel;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompareToLabel()
    {
        return $this->compareToLabel;
    }

    /**
     * @param string $operator
     * @return $this
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
        return $this;
    }

    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Sets options
     *
     * @param  array $options
     */
    #[\Override]
    public function setOptions($options = [])
    {
        if (isset($options['compare_to'])) {
            $this->setCompareTo($options['compare_to']);
        }

        if (isset($options['operator'])) {
            $this->setOperator($options['operator']);
        }

        if (isset($options['compare_to_label'])) {
            $this->setCompareToLabel($options['compare_to_label']);
        }

        return parent::setOptions($options);
    }

    /**
     * Returns true if and only if a value is valid.
     *
     * @param  mixed $value
     * @return bool
     */
    #[\Override]
    abstract public function isValid($value, array $context = null);

    /**
     * Returns true if and only if values are valid for given operator.
     *
     * @return bool
     */
    protected function isValidForOperator(mixed $value, mixed $compareToValue)
    {
        switch ($this->getOperator()) {
            case 'gte':
                if ($value < $compareToValue) {
                    $this->error(self::NOT_GTE);
                    return false;
                }

                break;
            case 'lte':
                if ($value > $compareToValue) {
                    $this->error(self::NOT_LTE);
                    return false;
                }

                break;
            case 'gt':
                if ($value <= $compareToValue) {
                    $this->error(self::NOT_GT);
                    return false;
                }

                break;
            case 'lt':
                if ($value >= $compareToValue) {
                    $this->error(self::NOT_LT);
                    return false;
                }

                break;
            default:
                $this->error(self::INVALID_OPERATOR);
                return false;
        }

        return true;
    }
}
