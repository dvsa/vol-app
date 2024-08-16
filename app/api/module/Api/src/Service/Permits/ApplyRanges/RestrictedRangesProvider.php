<?php

namespace Dvsa\Olcs\Api\Service\Permits\ApplyRanges;

class RestrictedRangesProvider
{
    /**
     * Returns the set of restricted ranges (i.e. ranges that allow travel to one or more of the restricted countries)
     *
     *
     * @return array
     */
    public function getRanges(array $ranges)
    {
        $restrictedRanges = [];

        foreach ($ranges as $range) {
            if (count($range['countryIds']) > 0) {
                $restrictedRanges[] = $range;
            }
        }

        return $restrictedRanges;
    }
}
