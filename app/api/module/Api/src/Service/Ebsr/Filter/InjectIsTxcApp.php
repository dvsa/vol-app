<?php

namespace Dvsa\Olcs\Api\Service\Ebsr\Filter;

use Laminas\Filter\AbstractFilter;
use Laminas\Filter\Exception;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;

/**
 * Class InjectIsTxcApp
 * @package Dvsa\Olcs\Api\Service\Ebsr\Filter
 * @template-extends AbstractFilter<array>
 */
class InjectIsTxcApp extends AbstractFilter
{
    /**
     * Returns the result of filtering $value
     *
     * @param array $value input value
     *
     * @throws Exception\RuntimeException If filtering $value is impossible
     * @return array
     */
    public function filter($value)
    {
        $value['isTxcApp'] = 'Y';
        $value['ebsrRefresh'] = 'N';

        if (isset($value['txcAppType']) && $value['txcAppType'] === BusRegEntity::TXC_APP_NON_CHARGEABLE) {
            $value['ebsrRefresh'] = 'Y';
        }

        return $value;
    }
}
