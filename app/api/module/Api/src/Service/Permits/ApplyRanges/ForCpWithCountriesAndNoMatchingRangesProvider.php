<?php

namespace Dvsa\Olcs\Api\Service\Permits\ApplyRanges;

use Dvsa\Olcs\Api\Domain\Command\Result;
use RuntimeException;

class ForCpWithCountriesAndNoMatchingRangesProvider
{
    /**
     * Create service instance
     *
     *
     * @return ForCpWithCountriesAndNoMatchingRangesProvider
     */
    public function __construct(private readonly UnrestrictedWithLowestStartNumberProvider $unrestrictedWithLowestStartNumberProvider, private readonly RestrictedWithFewestCountriesProvider $restrictedWithFewestCountriesProvider, private readonly HighestAvailabilityRangeSelector $highestAvailabilityRangeSelector)
    {
    }

    /**
     * Selects the range best-suited for a candidate permit that has countries but no matching ranges
     *
     *
     * @throws RuntimeException
     *
     * @return array the irhp_permit_range best suited for the candidate permit
     */
    public function selectRange(Result $result, array $ranges)
    {
        $result->addMessage('    - no restricted ranges found with matching countries');

        $matchingRange = $this->unrestrictedWithLowestStartNumberProvider->getRange($ranges);

        if (is_null($matchingRange)) {
            $result->addMessage('    - no unrestricted ranges found with lowest start number');

            $ranges = $this->restrictedWithFewestCountriesProvider->getRanges($ranges);

            if (empty($ranges)) {
                throw new RuntimeException(
                    'Assertion failed in method ' . __METHOD__ . ': count($ranges) == 0'
                );
            }

            if (count($ranges) > 1) {
                $result->addMessage('    - multiple ranges have the fewest countries');
                return $this->highestAvailabilityRangeSelector->getRange($result, $ranges);
            }

            $matchingRange = $ranges[0]; // Use first range

            $message = sprintf(
                '    - using first restricted range with fewest countries: id %d has countries %s',
                $matchingRange['entity']->getId(),
                implode(', ', $matchingRange['countryIds'])
            );
        } else {
            $rangeEntity = $matchingRange['entity'];
            $message = sprintf(
                '    - using unrestricted range with lowest start number: id %d starts at %d',
                $rangeEntity->getId(),
                $rangeEntity->getFromNo()
            );
        }

        $result->addMessage($message);
        return $matchingRange;
    }
}
