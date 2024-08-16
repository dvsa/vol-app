<?php

namespace Dvsa\Olcs\Api\Service\Permits\ApplyRanges;

class UnrestrictedWithLowestStartNumberProvider
{
    /**
     * From the set of unrestricted ranges (i.e. ranges that do not allow travel to any of the restricted countries),
     * return the range that has the lowest start number. Returns null if no unrestricted ranges were found
     *
     *
     * @return array|null
     */
    public function getRange(array $ranges)
    {
        $lowestStartNumber = null;
        $unrestrictedRangeWithLowestStartNumber = null;

        foreach ($ranges as $range) {
            if (count($range['countryIds']) == 0) {
                $fromNo = $range['entity']->getFromNo();
                if (is_null($lowestStartNumber) || ($fromNo < $lowestStartNumber)) {
                    $lowestStartNumber = $fromNo;
                    $unrestrictedRangeWithLowestStartNumber = $range;
                }
            }
        }

        return $unrestrictedRangeWithLowestStartNumber;
    }
}
