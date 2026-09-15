<?php

namespace Common\Service\Helper;

class RestrictionHelperService
{
    /**
     * Check if restriction is satisfied
     *
     * @return boolean
     */
    public function isRestrictionSatisfied($restrictions, array $accessKeys = [], $reference = null)
    {
        return $this->checkRestriction($restrictions, $accessKeys, false, $reference);
    }

    /**
     * Check restriction
     *
     * @param bool $strict
     * @return boolean
     */
    private function checkRestriction(mixed $restrictions, array $accessKeys = [], $strict = true, $reference = null)
    {
        // Check for a callable first
        if (is_callable($restrictions)) {
            return $restrictions($reference);
        }

        // If we are just matching a string
        if (is_string($restrictions)) {
            return in_array($restrictions, $accessKeys);
        }

        // We should have an array at this stage
        if (!is_array($restrictions)) {
            return false;
        }

        // Check the restrictions
        foreach ($restrictions as $restriction) {
            // Check the individual restriction
            $satisfied = $this->checkRestriction($restriction, $accessKeys, !$strict, $reference);

            // If we are not strict and this has been satisfied, we can just return true
            if ($satisfied && !$strict) {
                return true;
            }
            // If we are being strict and we haven't been satisfied, we can just return false
            if ($satisfied) {
                continue;
            }
            if (!$strict) {
                continue;
            }
            return false;
        }

        // This looks wrong, but it is right.
        // [Strict] - If any of our criteria hasn't been met, we should have already returned false.
        // [Not strict] - If we have already met one of the criteria, we would have already returned true
        return $strict;
    }
}
