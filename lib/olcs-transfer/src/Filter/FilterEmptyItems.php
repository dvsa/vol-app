<?php

namespace Dvsa\Olcs\Transfer\Filter;

use Laminas\Filter\AbstractFilter;

/**
 * Removes empty items from an array
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 *
 * @template-extends AbstractFilter<array>
 */
class FilterEmptyItems extends AbstractFilter
{
    /**
     * Filters out array values that are empty ('', false or null). Array must be indexed numerically.
     *
     * @param mixed $value
     * @return array|mixed
     */
    #[\Override]
    public function filter($value)
    {
        if (!is_array($value)) {
            return $value;
        }

        $newValue = [];

        foreach ($value as $key => $val) {
            if (is_numeric($key)) {
                if (!empty($val)) {
                    $newValue[] = $val;
                }
            } else {
                throw new \RuntimeException(
                    'FilterEmptyItems should not be used for associative arrays as array keys will be re-indexed
                    numerically'
                );
            }
        }

        return $newValue;
    }
}
