<?php

namespace Dvsa\Olcs\Api\Service\Ebsr\Filter;

use Laminas\Filter\AbstractFilter;
use Laminas\Filter\Exception;
use Dvsa\Olcs\Api\Entity\Bus\BusNoticePeriod;

/**
 * Class NoticePeriod
 * @package Dvsa\Olcs\Api\Service\Ebsr\Filter
 * @template-extends AbstractFilter<array>
 */
class NoticePeriod extends AbstractFilter
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
        $value['busNoticePeriod'] = BusNoticePeriod::NOTICE_PERIOD_OTHER;
        foreach ($value['trafficAreas'] as $trafficArea) {
            if ($trafficArea == 'Scottish') {
                $value['busNoticePeriod'] = BusNoticePeriod::NOTICE_PERIOD_SCOTLAND;
                break;
            }
            if ($trafficArea == 'Welsh') {
                $value['busNoticePeriod'] = BusNoticePeriod::NOTICE_PERIOD_WALES;
            }
        }

        return $value;
    }
}
