<?php

namespace Dvsa\Olcs\Transfer\Filter;

use Laminas\Filter\AbstractFilter;

/**
 * Removes duplicate items from an array
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 *
 * @template-extends AbstractFilter<array>
 */
class UniqueItems extends AbstractFilter
{
    #[\Override]
    public function filter($value)
    {
        return array_unique($value);
    }
}
