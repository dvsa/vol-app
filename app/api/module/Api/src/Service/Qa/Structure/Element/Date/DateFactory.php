<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Date;

class DateFactory
{
    /**
     * Create and return a Date instance
     *
     * @param string $value (optional)
     *
     * @return Date
     */
    public function create($value = null)
    {
        return new Date($value);
    }
}
