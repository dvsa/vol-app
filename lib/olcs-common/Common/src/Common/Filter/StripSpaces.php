<?php

namespace Common\Filter;

use Laminas\Filter\AbstractFilter;

/**
 * Class StripSpaces
 * @package Common\Filter
 *
 * @template-extends AbstractFilter<string, string>
 */
class StripSpaces extends AbstractFilter
{
    /**
     * Strip spaces
     *
     * @param string $value Value to strip spaces from
     *
     * @return string
     */
    #[\Override]
    public function filter($value)
    {
        if ($value === null) {
            return null;
        }
        return str_replace(' ', '', $value);
    }
}
