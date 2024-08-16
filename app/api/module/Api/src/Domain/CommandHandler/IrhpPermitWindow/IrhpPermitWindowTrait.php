<?php

/**
 * Irhp permit window trait
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermitWindow;

use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;

trait IrhpPermitWindowTrait
{
    /**
     * Returns the number of Windows that overlap with the provided period.
     *
     * @param $irhpPermitStock
     * @param $proposedStartDate
     * @param $proposedEndDate
     * @param null $irhpPermitWindow
     * @return int
     */
    public function numberOfOverlappingWindows($irhpPermitStock, $proposedStartDate, $proposedEndDate, $irhpPermitWindow = null)
    {
        $windowsOverlapping = $this->getRepo()->findOverlappingWindowsByType($irhpPermitStock, $proposedStartDate, $proposedEndDate, $irhpPermitWindow);
        return count($windowsOverlapping);
    }

    /**
     * Validate ranges in the stock
     *
     *
     * @return void
     * @throws ValidationException
     */
    public function validateStockRanges(IrhpPermitStock $irhpPermitStock)
    {
        if (!$irhpPermitStock->getIrhpPermitType()->isBilateral()) {
            return;
        }

        $applicationPathGroup = $irhpPermitStock->getApplicationPathGroup();
        $hasCabotageRange = $irhpPermitStock->hasCabotageRange();
        $hasStandardRange = $irhpPermitStock->hasStandardRange();

        $err = '';

        if ($applicationPathGroup->isBilateralCabotageOnly()) {
            if (!$hasCabotageRange) {
                $err = $hasStandardRange
                    ? 'ERR_IRHP_BIL_CAB_ONLY_STOCK_WITHOUT_CAB_RANGE_WITH_STD_RANGE'
                    : 'ERR_IRHP_BIL_CAB_ONLY_STOCK_WITHOUT_CAB_RANGE';
            } elseif ($hasStandardRange) {
                $err = 'ERR_IRHP_BIL_CAB_ONLY_STOCK_WITH_STD_RANGE';
            }
        } elseif ($applicationPathGroup->isBilateralStandardOnly()) {
            if (!$hasStandardRange) {
                $err = $hasCabotageRange
                    ? 'ERR_IRHP_BIL_STD_ONLY_STOCK_WITHOUT_STD_RANGE_WITH_CAB_RANGE'
                    : 'ERR_IRHP_BIL_STD_ONLY_STOCK_WITHOUT_STD_RANGE';
            } elseif ($hasCabotageRange) {
                $err = 'ERR_IRHP_BIL_STD_ONLY_STOCK_WITH_CAB_RANGE';
            }
        } elseif ($applicationPathGroup->isBilateralStandardAndCabotage()) {
            if (!$hasStandardRange) {
                $err = $hasCabotageRange
                    ? 'ERR_IRHP_BIL_STD_AND_CAB_STOCK_WITHOUT_STD_RANGE'
                    : 'ERR_IRHP_BIL_STD_AND_CAB_STOCK_WITHOUT_STD_RANGE_WITHOUT_CAB_RANGE';
            } elseif (!$hasCabotageRange) {
                $err = 'ERR_IRHP_BIL_STD_AND_CAB_STOCK_WITH_CAB_RANGE';
            }
        }

        if (!empty($err)) {
            throw new ValidationException([$err]);
        }
    }
}
