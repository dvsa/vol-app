<?php

namespace Dvsa\Olcs\Api\Service\Permits\ApplyRanges;

use Dvsa\Olcs\Api\Domain\Command\Result;
use RuntimeException;

class ForCpWithNoCountriesProvider
{
    /**
     * Create service instance
     *
     *
     * @return ForCpWithNoCountriesProvider
     */
    public function __construct(private readonly UnrestrictedWithLowestStartNumberProvider $unrestrictedWithLowestStartNumberProvider, private readonly RestrictedWithFewestCountriesProvider $restrictedWithFewestCountriesProvider, private readonly HighestAvailabilityRangeSelector $highestAvailabilityRangeSelector)
    {
    }

    /**
     * Returns the best fitting range for an candidate permit that requests no countries
     *
     *
     * @return array
     */
    public function selectRange(Result $result, array $ranges)
    {
        $range = $this->unrestrictedWithLowestStartNumberProvider->getRange($ranges);

        if (is_null($range)) {
            $result->addMessage('    - no unrestricted ranges available, use restricted range with fewest countries');

            $ranges = $this->restrictedWithFewestCountriesProvider->getRanges($ranges);
            switch (count($ranges)) {
                case 0:
                    throw new RuntimeException(
                        'Assertion failed in method ' . __METHOD__ . ': count($ranges) == 0'
                    );
                case 1:
                    $range = $ranges[0];
                    break;
                default:
                    $result->addMessage('    - multiple ranges have the fewest countries');
                    return $this->highestAvailabilityRangeSelector->getRange($result, $ranges);
            }

            $message = sprintf(
                '    - using restricted range with fewest countries: id %d with countries %s',
                $range['entity']->getId(),
                implode(', ', $range['countryIds'])
            );
        } else {
            $rangeEntity = $range['entity'];

            $message = sprintf(
                '    - using unrestricted range with lowest start number: id %d starts at %d',
                $rangeEntity->getId(),
                $rangeEntity->getFromNo()
            );
        }

        $result->addMessage($message);

        return $range;
    }
}
