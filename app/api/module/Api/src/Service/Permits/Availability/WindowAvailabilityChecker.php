<?php

namespace Dvsa\Olcs\Api\Service\Permits\Availability;

use DateTime;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitWindow as IrhpPermitWindowRepository;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;

class WindowAvailabilityChecker
{
    /**
     * Create service instance
     *
     *
     * @return WindowAvailabilityChecker
     */
    public function __construct(private readonly IrhpPermitWindowRepository $irhpPermitWindowRepo, private readonly StockAvailabilityChecker $stockAvailabilityChecker)
    {
    }

    /**
     * Whether there are any short term ecmt windows both open and having available stock
     *
     *
     * @return bool
     */
    public function hasAvailability(DateTime $now)
    {
        $openWindows = $this->irhpPermitWindowRepo->fetchOpenWindowsByType(
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM,
            $now
        );

        foreach ($openWindows as $openWindow) {
            $windowHasAvailability = $this->stockAvailabilityChecker->hasAvailability(
                $openWindow->getIrhpPermitStock()->getId()
            );

            if ($windowHasAvailability) {
                return true;
            }
        }

        return false;
    }
}
