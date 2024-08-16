<?php

namespace Dvsa\Olcs\Api\Service\Permits\Availability;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;
use Dvsa\Olcs\Api\Domain\Repository\IrhpCandidatePermit as IrhpCandidatePermitRepository;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermit as IrhpPermitRepository;

class CandidatePermitsAvailableCountCalculator
{
    /**
     * Create service instance
     *
     *
     * @return CandidatePermitsAvailableCountCalculator
     */
    public function __construct(private readonly IrhpCandidatePermitRepository $irhpCandidatePermitRepo, private readonly IrhpPermitRepository $irhpPermitRepo)
    {
    }

    /**
     * Return the number of permits that would be available for granting in the specified range after taking into account
     * the request for permitsRequired permits. May return a negative number if there is insufficient availability.
     *
     * @param int $permitsRequired
     * @return int
     */
    public function getCount(IrhpPermitRange $irhpPermitRange, $permitsRequired)
    {
        $rangeId = $irhpPermitRange->getId();
        $rangeSize = $irhpPermitRange->getSize();

        $issuedCount = $this->irhpPermitRepo->getPermitCountByRange($rangeId);
        $grantedCount = $this->irhpCandidatePermitRepo->fetchCountInRangeWhereApplicationAwaitingFee($rangeId);

        $freePermitsInRange = ($rangeSize - ($issuedCount + $grantedCount));
        return $freePermitsInRange - $permitsRequired;
    }
}
