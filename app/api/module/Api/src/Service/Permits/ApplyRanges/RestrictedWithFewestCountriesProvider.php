<?php

namespace Dvsa\Olcs\Api\Service\Permits\ApplyRanges;

use Dvsa\Olcs\Api\Domain\Command\Result;

class RestrictedWithFewestCountriesProvider
{
    /**
     * Create service instance
     *
     *
     * @return RestrictedWithFewestCountriesProvider
     */
    public function __construct(private readonly RestrictedRangesProvider $restrictedRangesProvider)
    {
    }

    /**
     * Returns the set of one or more restricted ranges (i.e. ranges that allow travel to one or more of the
     * restricted countries) that share the lowest number of restricted countries amongst the full set of ranges
     *
     *
     * @return array
     */
    public function getRanges(array $ranges)
    {
        $fewestCountriesCount = null;
        $restrictedRangesWithFewestCountries = [];

        $restrictedRanges = $this->restrictedRangesProvider->getRanges($ranges);

        foreach ($restrictedRanges as $range) {
            $rangeCountriesCount = count($range['countryIds']);

            if (is_null($fewestCountriesCount) || ($rangeCountriesCount < $fewestCountriesCount)) {
                $fewestCountriesCount = $rangeCountriesCount;
                $restrictedRangesWithFewestCountries = [$range];
            } elseif ($rangeCountriesCount == $fewestCountriesCount) {
                $restrictedRangesWithFewestCountries[] = $range;
            }
        }

        return $restrictedRangesWithFewestCountries;
    }
}
