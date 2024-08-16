<?php

namespace Dvsa\Olcs\Api\Service\Permits\ApplyRanges;

use Dvsa\Olcs\Api\Domain\Command\Result;

class ForCpWithCountriesAndMultipleMatchingRangesProvider
{
    /**
     * Create service instance
     *
     *
     * @return ForCpWithCountriesAndMultipleMatchingRangesProvider
     */
    public function __construct(private readonly WithFewestNonRequestedCountriesProvider $withFewestNonRequestedCountriesProvider, private readonly HighestAvailabilityRangeSelector $highestAvailabilityRangeSelector)
    {
    }

    /**
     * Selects the appropriate irhp_permit_range for a candidate permit with associated countries
     * and multiple matching ranges.
     *
     * @param array $ranges an array of the multiple matching ranges
     * @param array $applicationCountryIds The country ids specified in the application
     * @return array the single range identified as suitable
     */
    public function selectRange(Result $result, array $ranges, array $applicationCountryIds)
    {
        $result->addMessage('    - more than one range found with most matching countries:');
        foreach ($ranges as $range) {
            $message = sprintf(
                '      - range with id %d and countries %s',
                $range['entity']->getId(),
                implode(', ', $range['countryIds'])
            );
            $result->addMessage($message);
        }

        $matchingRanges = $this->withFewestNonRequestedCountriesProvider->getRanges(
            $applicationCountryIds,
            $ranges
        );

        if (count($matchingRanges) > 1) {
            $result->addMessage('    - multiple ranges have the fewest non-requested countries');
            return $this->highestAvailabilityRangeSelector->getRange($result, $matchingRanges);
        }

        $matchingRange = $matchingRanges[0];

        $result->addMessage(
            sprintf(
                '    - range %d with countries %s has the fewest non-requested countries',
                $matchingRange['entity']->getId(),
                implode(', ', $matchingRange['countryIds'])
            )
        );

        return $matchingRange;
    }
}
