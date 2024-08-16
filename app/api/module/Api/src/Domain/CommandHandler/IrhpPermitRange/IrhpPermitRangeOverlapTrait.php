<?php

/**
 * Irhp range overlap trait
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermitRange;

use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock;

trait IrhpPermitRangeOverlapTrait
{
    /**
     * Returns the number of Ranges that overlap with the provided values.
     *
     * @param IrhpPermitStock $irhpPermitStock
     * @param string $proposedPrefix
     * @param int $proposedStartValue
     * @param int $proposedEndValue
     * @param int $irhpPermitRange
     *
     * @return int
     */
    public function numberOfOverlappingRanges($irhpPermitStock, $proposedPrefix, $proposedStartValue, $proposedEndValue, $irhpPermitRange = null)
    {
        $rangesOverlapping = $this->getRepo()->findOverlappingRangesByType(
            $irhpPermitStock,
            $proposedPrefix,
            $proposedStartValue,
            $proposedEndValue,
            $irhpPermitRange
        );

        return count($rangesOverlapping);
    }
}
