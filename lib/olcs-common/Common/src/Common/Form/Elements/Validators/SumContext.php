<?php

/**
 * Sum Context
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Form\Elements\Validators;

use Laminas\Validator\AbstractValidator;

/**
 * Sum Context - Checks that the sum of all context values is within a configured range
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SumContext extends AbstractValidator
{
    public const BELOW_MIN = 'belowMin';

    public const ABOVE_MAX = 'aboveMax';

    /**
     * @var int
     */
    protected $min;

    /**
     * @var int
     */
    protected $max;

    /**
     * @var array
     */
    protected $messageVariables = [
        'min' => 'min',
        'max' => 'max',
    ];

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::BELOW_MIN => 'The sum of all values must be greater than %min%',
        self::ABOVE_MAX => 'The sum of all values must be less than %max%',
    ];

    /**
     * Set minimum float value
     *
     * @param int $min Set minimum value for all fields
     */
    public function setMin($min): void
    {
        $this->min = $min;
    }

    /**
     * Set maximum float value
     *
     * @param int $max Set maximum value for all fields
     */
    public function setMax($max): void
    {
        $this->max = $max;
    }

    /**
     * Check if the context is valid.
     *
     * @param mixed      $value   Value of the input field
     * @param array|null $context Context is values of all fields in same fieldset
     *
     * @return bool
     */
    #[\Override]
    public function isValid($value, $context = null)
    {
        unset($value); // Removes CS violation

        $sum = array_sum($context);

        $valid = true;

        if ($this->min !== null && $sum < $this->min) {
            $valid = false;
            $this->error(self::BELOW_MIN);
        }

        if ($this->max !== null && $sum > $this->max) {
            $valid = false;
            $this->error(self::ABOVE_MAX);
        }

        return $valid;
    }
}
