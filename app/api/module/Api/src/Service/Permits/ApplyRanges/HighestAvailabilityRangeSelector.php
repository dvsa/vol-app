<?php

namespace Dvsa\Olcs\Api\Service\Permits\ApplyRanges;

use Dvsa\Olcs\Api\Domain\Command\Result;

class HighestAvailabilityRangeSelector
{
    /**
     * Returns the single range from the provided set with most permits remaining, or if there are multiple ranges
     * all with the most permits remaining, returns the range from this set with the lowest range id
     *
     * @param array $matchingRanges an array of the multiple matching ranges
     * @return array
     */
    public function getRange(Result $result, array $matchingRanges)
    {
        $rangeIds = $this->getIdsFromRanges($matchingRanges);

        $result->addMessage(
            sprintf(
                '    - selecting range with most permits remaining from ranges %s:',
                implode(',', $rangeIds)
            )
        );

        $mostPermitsRemaining = null;

        foreach ($matchingRanges as $range) {
            $rangePermitsRemaining = $range['permitsRemaining'];
            if (is_null($mostPermitsRemaining) || $rangePermitsRemaining > $mostPermitsRemaining) {
                $mostPermitsRemaining = $rangePermitsRemaining;
            }
        }

        $rangesWithMostPermits = [];
        foreach ($matchingRanges as $range) {
            if ($range['permitsRemaining'] == $mostPermitsRemaining) {
                $rangesWithMostPermits[] = $range;
            }
        }

        if (count($rangesWithMostPermits) == 1) {
            $rangeWithMostPermits = $rangesWithMostPermits[0];
            $result->addMessage(
                sprintf(
                    '    - Using range %s with %s permits remaining',
                    $rangeWithMostPermits['entity']->getId(),
                    $mostPermitsRemaining
                )
            );
            return $rangeWithMostPermits;
        }

        $rangeIds = $this->getIdsFromRanges($rangesWithMostPermits);

        $result->addMessage(
            sprintf(
                '    - multiple ranges %s all have the most number of permits remaining: %s',
                implode(',', $rangeIds),
                $mostPermitsRemaining
            )
        );

        $lowestRangeId = null;
        $rangeWithLowestRangeId = null;
        foreach ($rangesWithMostPermits as $range) {
            $rangeId = $range['entity']->getId();

            if (is_null($lowestRangeId) || $rangeId < $lowestRangeId) {
                $lowestRangeId = $rangeId;
                $rangeWithLowestRangeId = $range;
            }
        }

        $result->addMessage(
            sprintf(
                '    - using range with lowest id instead - range id is %s',
                $rangeWithLowestRangeId['entity']->getId()
            )
        );

        return $rangeWithLowestRangeId;
    }

    /**
     * Returns an array of range ids given an array of ranges
     *
     *
     * @return array
     */
    public function getIdsFromRanges(array $ranges)
    {
        $rangeIds = [];
        foreach ($ranges as $range) {
            $rangeIds[] = $range['entity']->getId();
        }

        return $rangeIds;
    }
}
