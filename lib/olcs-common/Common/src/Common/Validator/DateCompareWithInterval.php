<?php

namespace Common\Validator;

/**
 * Class DateCompareSla - used to validate two dates via a legal operator:
 * 'gt' -> greater than
 * 'gte' -> greater than or equal to
 * 'lt' -> less than
 * 'lte' -> less than or equal to
 * @package Common\Validator
 */
class DateCompareWithInterval extends DateCompare
{
    /**
     * Error codes
     * @const string
     */
    public const INVALID_INTERVAL = 'invalidInterval';

    /**
     * Error messages
     * @var array
     */
    protected $messageTemplates = [
        self::NOT_GT => "This date must be %interval_label% after the '%compare_to_label%'",
        self::NOT_LT => "This date must be %interval_label% before '%compare_to_label%'",
        self::INVALID_OPERATOR => "Invalid operator",
        self::INVALID_INTERVAL => "Invalid interval",
        self::INVALID_FIELD => "Input field being compared to doesn't exist",
        self::NO_COMPARE => "Unable to compare with '%compare_to_label%'"
    ];

    /**
     * @var array
     */
    protected $messageVariables = [
        'compare_to_label' => 'compareToLabel',
        'interval_label' => 'intervalLabel'
    ];

    /**
     * Label of interval field to use in error message
     * @var string
     */
    protected $intervalLabel;

    /**
     * Additional Date Interval to add to the compare to date
     * @var string $interval_spec
     */
    protected $dateInterval;

    /**
     * @param string $interval_spec
     * @link http://php.net/manual/en/dateinterval.construct.php
     */
    public function setDateInterval($dateInterval): void
    {
        $this->dateInterval = $dateInterval;
    }

    /**
     * @param string $interval_spec
     *
     * @link http://php.net/manual/en/dateinterval.construct.php
     */
    public function getDateInterval(): string
    {
        return $this->dateInterval;
    }

    /**
     * @param string $intervalLabel
     * @return $this
     */
    public function setIntervalLabel($intervalLabel)
    {
        $this->intervalLabel = $intervalLabel;
        return $this;
    }

    /**
     * @return string
     */
    public function getIntervalLabel()
    {
        return $this->intervalLabel;
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
        if (isset($options['date_interval'])) {
            $this->setDateInterval($options['date_interval']);
        }

        if (isset($options['interval_label'])) {
            $this->setIntervalLabel($options['interval_label']);
        }

        return parent::setOptions($options);
    }

    /**
     * Override to add additional date interval
     *
     * @param array $context
     */
    #[\Override]
    protected function getCompareToDate($context): \DateTime|false
    {
        $compareDateValue = parent::getCompareToDate($context);
        if ($compareDateValue === false) {
            return false;
        }

        if (!empty($this->getDateInterval())) {
            try {
                $dv = new \DateInterval($this->getDateInterval());
                if (in_array($this->getOperator(), ['lt', 'lte'], true)) {
                    $compareDateValue->sub($dv);
                } else {
                    $compareDateValue->add($dv);
                }
            } catch (\Exception) {
                $this->error(self::INVALID_INTERVAL); //@TO~DO~
                return false;
            }
        }

        return $compareDateValue;
    }

    /**
     * Returns true if and only if values are valid for given operator.
     *
     * @param  mixed $value
     * @param  mixed $compareToValue
     * @return bool
     */
    #[\Override]
    protected function isValidForOperator($value, $compareToValue)
    {
        switch ($this->getOperator()) {
            case 'gt':
                if ($value < $compareToValue) {
                    $this->error(self::NOT_GT);
                    return false;
                }

                break;
            case 'lt':
                if ($value > $compareToValue) {
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
