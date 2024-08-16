<?php

namespace Dvsa\Olcs\Api\Service\Ebsr\Filter;

use Laminas\Filter\AbstractFilter;
use Laminas\Filter\Exception;

/**
 * Class InjectReceivedDate
 * @package Olcs\Ebsr\Filter
 * @template-extends AbstractFilter<array>
 */
class InjectReceivedDate extends AbstractFilter
{
    /**
     * Returns the result of filtering $value
     *
     * @param  mixed $value
     * @throws Exception\RuntimeException If filtering $value is impossible
     * @return mixed
     */
    public function filter($value)
    {
        $value['receivedDate'] = date('Y-m-d');
        return $value;
    }
}
