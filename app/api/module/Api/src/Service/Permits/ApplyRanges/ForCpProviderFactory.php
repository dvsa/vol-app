<?php

namespace Dvsa\Olcs\Api\Service\Permits\ApplyRanges;

class ForCpProviderFactory
{
    /**
     * Create instance of ForCpProvider with the specified restricted countries list
     *
     *
     * @return ForCpProvider
     */
    public function create(array $restrictedCountryIds)
    {
        $restrictedCountryIdsProvider = new RestrictedCountryIdsProvider($restrictedCountryIds);

        $restrictedRangesProvider = new RestrictedRangesProvider();

        $unrestrictedWithLowestStartNumberProvider = new UnrestrictedWithLowestStartNumberProvider();

        $highestAvailabilityRangeSelector = new HighestAvailabilityRangeSelector();

        $restrictedWithFewestCountriesProvider = new RestrictedWithFewestCountriesProvider(
            $restrictedRangesProvider
        );

        $withFewestNonRequestedCountriesProvider = new WithFewestNonRequestedCountriesProvider(
            $restrictedCountryIdsProvider
        );

        $restrictedWithMostMatchingCountriesProvider = new RestrictedWithMostMatchingCountriesProvider(
            $restrictedRangesProvider
        );

        $forCpWithCountriesAndNoMatchingRangesProvider = new ForCpWithCountriesAndNoMatchingRangesProvider(
            $unrestrictedWithLowestStartNumberProvider,
            $restrictedWithFewestCountriesProvider,
            $highestAvailabilityRangeSelector
        );

        $forCpWithCountriesAndMultipleMatchingRangesProvider = new ForCpWithCountriesAndMultipleMatchingRangesProvider(
            $withFewestNonRequestedCountriesProvider,
            $highestAvailabilityRangeSelector
        );

        $forCpWithCountriesProvider = new ForCpWithCountriesProvider(
            $restrictedWithMostMatchingCountriesProvider,
            $forCpWithCountriesAndNoMatchingRangesProvider,
            $forCpWithCountriesAndMultipleMatchingRangesProvider
        );

        $forCpWithNoCountriesProvider = new ForCpWithNoCountriesProvider(
            $unrestrictedWithLowestStartNumberProvider,
            $restrictedWithFewestCountriesProvider,
            $highestAvailabilityRangeSelector
        );

        $entityIdsExtractor = new EntityIdsExtractor();

        $rangeSubsetGenerator = new RangeSubsetGenerator();

        return new ForCpProvider(
            $forCpWithCountriesProvider,
            $forCpWithNoCountriesProvider,
            $entityIdsExtractor,
            $rangeSubsetGenerator
        );
    }
}
