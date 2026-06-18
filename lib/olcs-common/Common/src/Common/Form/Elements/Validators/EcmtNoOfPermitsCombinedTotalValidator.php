<?php

namespace Common\Form\Elements\Validators;

class EcmtNoOfPermitsCombinedTotalValidator
{
    /**
     * Verify that the total requested number of permits across all emission types is greater than zero
     *
     * @param array $context
     *
     * @return bool
     *
     * @psalm-param 3 $value
     */
    public static function validateMin($value, $context)
    {
        return (self::getTotal($context) >= 1);
    }

    /**
     * Verify that the total requested number of permits across all emission types is less than or equal to the
     * provided maxValue
     *
     * @param array $context
     * @param int $maxValue
     *
     * @return bool
     *
     * @psalm-param 3 $value
     */
    public static function validateMax($value, $context, $maxValue)
    {
        return (self::getTotal($context) <= $maxValue);
    }

    /**
     * Get the total number of requested permits across the specified context, disregarding any empty or non-numeric
     * values
     *
     * @param array $context
     *
     * @return int
     */
    private static function getTotal($context)
    {
        $total = 0;
        foreach ($context as $name => $value) {
            if ((str_starts_with($name, 'euro')) && is_string($value)) {
                $trimmedValue = trim($value);
                if (ctype_digit($trimmedValue)) {
                    $total += (int) $value;
                }
            }
        }

        return $total;
    }
}
