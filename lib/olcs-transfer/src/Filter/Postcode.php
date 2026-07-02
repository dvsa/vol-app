<?php

/**
 * Postcode filter
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Transfer\Filter;

use Laminas\Filter\FilterInterface;
use Laminas\Filter\StringTrim;

/**
 * Postcode filter
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class Postcode implements FilterInterface
{
    private StringTrim $stringTrimFilter;

    public function __construct()
    {
        $this->stringTrimFilter = new StringTrim();
    }

    /**
     * Returns the result of filtering $value
     *
     * @param  mixed $value
     * @return string
     */
    #[\Override]
    public function filter($value)
    {
        // apply StringTrim filter
        $value = $this->stringTrimFilter->filter($value);

        if (empty($value)) {
            return $value;
        }

        // normalise spacing and case
        $value = strtoupper(str_replace(' ', '', $value));

        // insert space between inward and outward postcode parts
        return substr($value, 0, -3) . ' ' . substr($value, -3, 3);
    }
}
