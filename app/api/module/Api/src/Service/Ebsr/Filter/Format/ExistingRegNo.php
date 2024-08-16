<?php

namespace Dvsa\Olcs\Api\Service\Ebsr\Filter\Format;

use Laminas\Filter\AbstractFilter as AbstractFilter;

/**
 * Class ExistingRegNo
 * @package Dvsa\Olcs\Api\Service\Ebsr\Filter\Format
 * @template-extends AbstractFilter<array>
 */
class ExistingRegNo extends AbstractFilter
{
    /**
     * Returns the result of filtering $value, a regNo we can use to retrieve the previous bus reg
     *
     * @param mixed $value
     * @return mixed
     */
    public function filter($value)
    {
        $value['existingRegNo'] = $value['licNo'] . '/' . $value['routeNo'];

        return $value;
    }
}
