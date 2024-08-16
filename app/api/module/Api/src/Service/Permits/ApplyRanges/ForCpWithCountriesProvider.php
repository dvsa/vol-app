<?php

namespace Dvsa\Olcs\Api\Service\Permits\ApplyRanges;

use Dvsa\Olcs\Api\Domain\Command\Result;

class ForCpWithCountriesProvider
{
    /**
     * Create service instance
     *
     *
     * @return ForCpWithCountriesProvider
     */
    public function __construct(private readonly RestrictedWithMostMatchingCountriesProvider $restrictedWithMostMatchingCountriesProvider, private readonly ForCpWithCountriesAndNoMatchingRangesProvider $forCpWithCountriesAndNoMatchingRangesProvider, private readonly ForCpWithCountriesAndMultipleMatchingRangesProvider $forCpWithCountriesAndMultipleMatchingRangesProvider)
    {
    }

    /**
     * Returns the best fitting range for an application that specifies one or more countries
     *
     * @param array $applicationCountryIds The country ids specified in the application
     *
     * @return array
     */
    public function selectRange(Result $result, array $ranges, array $applicationCountryIds)
    {
        $matchingRanges = $this->restrictedWithMostMatchingCountriesProvider->getRanges(
            $ranges,
            $applicationCountryIds
        );

        switch (count($matchingRanges)) {
            case 0:
                return $this->forCpWithCountriesAndNoMatchingRangesProvider->selectRange($result, $ranges);
            case 1:
                $matchingRange = $matchingRanges[0];

                $message = sprintf(
                    '    - range %d with countries %s has the most matching countries',
                    $matchingRange['entity']->getId(),
                    implode(', ', $matchingRange['countryIds'])
                );

                $result->addMessage($message);
                return $matchingRange;
        }

        return $this->forCpWithCountriesAndMultipleMatchingRangesProvider->selectRange(
            $result,
            $matchingRanges,
            $applicationCountryIds
        );
    }
}
