<?php

namespace Olcs\Form\Element\Permits;

class BilateralNoOfPermitsCombinedTotalValidator
{
    /**
     * Verify that at least one of the sibling no of permits elements contains a non-zero value
     *
     * @param array $context
     * @return bool
     */
    public static function validateNonZeroValuePresent(mixed $value, $context)
    {
        foreach ($context as $name => $value) {
            if ((str_contains($name, 'journey')) && is_string($value)) {
                $trimmedValue = trim($value);
                if (ctype_digit($trimmedValue)) {
                    if (intval($trimmedValue) > 0) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
