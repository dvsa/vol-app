<?php

namespace Common\Filter;

use Laminas\Filter\AbstractFilter;

/**
 * Class NotPopulatedStringToZero
 * @package Common\Filter
 *
 * @template-extends AbstractFilter<mixed, string>
 */
class NotPopulatedStringToZero extends AbstractFilter
{
    public const ZERO = '0';

    /**
     * Filter
     *
     * @param mixed $value Value to check
     *
     * @return string
     */
    #[\Override]
    public function filter($value)
    {
        if (!is_string($value)) {
            return self::ZERO;
        }

        if ($value === '') {
            return self::ZERO;
        }

        return $value;
    }
}
