<?php

namespace Dvsa\Olcs\Api\Service\Permits\ApplyRanges;

use Dvsa\Olcs\Api\Domain\Command\Result;

class RestrictedWithMostMatchingCountriesProvider
{
    /**
     * Create service instance
     *
     *
     * @return RestrictedWithMostMatchingCountriesProvider
     */
    public function __construct(private readonly RestrictedRangesProvider $restrictedRangesProvider)
    {
    }

    /**
     * From the set of restricted ranges (i.e. ranges that allow travel to one or more restricted countries), return
     * the ranges that have the most countries in common with those passed in
     *
     * @param array $applicationCountryIds The country ids requested in the application
     * @return array
     */
    public function getRanges(array $ranges, array $applicationCountryIds)
    {
        $maxCommonCountryCount = 0;
        $matchingRanges = [];

        $restrictedRanges = $this->restrictedRangesProvider->getRanges($ranges);

        foreach ($restrictedRanges as $range) {
            $commonCountryIds = array_intersect(
                $applicationCountryIds,
                $range['countryIds']
            );
            $commonCountryCount = count($commonCountryIds);

            if ($commonCountryCount > 0) {
                if ($commonCountryCount > $maxCommonCountryCount) {
                    $maxCommonCountryCount = $commonCountryCount;
                    $matchingRanges = [$range];
                } elseif ($commonCountryCount == $maxCommonCountryCount) {
                    $matchingRanges[] = $range;
                }
            }
        }

        return $matchingRanges;
    }
}
