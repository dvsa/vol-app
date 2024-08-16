<?php

namespace Dvsa\Olcs\Api\Service\Permits\ApplyRanges;

class WithFewestNonRequestedCountriesProvider
{
    /**
     * Create service instance
     *
     *
     * @return WithFewestNonRequestedCountriesProvider
     */
    public function __construct(private readonly RestrictedCountryIdsProvider $restrictedCountryIdsProvider)
    {
    }

    /**
     * From the set of ranges specified in the parameter list, return the ranges that have the fewest restricted
     * countries NOT requested by the application
     *
     * @param array $applicationCountryIds The country ids requested in the application
     * @param array $ranges The ranges to search
     *
     * @return array
     */
    public function getRanges(array $applicationCountryIds, array $ranges)
    {
        $restrictedCountryIds = $this->restrictedCountryIdsProvider->getIds();

        $nonRequestedCountryIds = array_diff(
            $restrictedCountryIds,
            $applicationCountryIds
        );

        $fewestCommonCountriesCount = null;
        foreach ($ranges as $range) {
            $commonCountries = array_intersect(
                $nonRequestedCountryIds,
                $range['countryIds']
            );

            $commonCountriesCount = count($commonCountries);

            if (is_null($fewestCommonCountriesCount) || ($commonCountriesCount < $fewestCommonCountriesCount)) {
                $fewestCommonCountriesCount = $commonCountriesCount;
                $rangesWithFewestCommonCountries = [$range];
            } elseif ($commonCountriesCount == $fewestCommonCountriesCount) {
                $rangesWithFewestCommonCountries[] = $range;
            }
        }

        return $rangesWithFewestCommonCountries;
    }
}
