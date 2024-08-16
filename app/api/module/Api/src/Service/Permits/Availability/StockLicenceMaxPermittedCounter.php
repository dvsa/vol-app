<?php

namespace Dvsa\Olcs\Api\Service\Permits\Availability;

use Dvsa\Olcs\Api\Domain\Repository\IrhpPermit as IrhpPermitRepository;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use RuntimeException;

class StockLicenceMaxPermittedCounter
{
    public const ECMT_SHORT_TERM_MULTIPLIER = 2;
    public const ERR_INVALID_TYPE = 'LicenceMaxPermittedCounter is only applicable to short terms and annuals';

    /**
     * Create service instance
     *
     *
     * @return StockLicenceMaxPermittedCounter
     */
    public function __construct(private readonly IrhpPermitRepository $irhpPermitRepo)
    {
    }

    /**
     * Return a count of the maximum number of permits that can be applied for in the context of the specified stock
     * and licence
     *
     *
     * @return int
     *
     * @throws RuntimeException
     */
    public function getCount(IrhpPermitStock $irhpPermitStock, Licence $licence)
    {
        $irhpPermitType = $irhpPermitStock->getIrhpPermitType();

        if (!$irhpPermitType->isEcmtShortTerm() && !$irhpPermitType->isEcmtAnnual()) {
            throw new RuntimeException(self::ERR_INVALID_TYPE);
        }

        $totAuthVehicles = $licence->getTotAuthVehicles();

        if ($irhpPermitType->isEcmtShortTerm()) {
            return $totAuthVehicles * self::ECMT_SHORT_TERM_MULTIPLIER;
        }

        $allocatedPermitCount = $this->irhpPermitRepo->getEcmtAnnualPermitCountByLicenceAndStockEndYear(
            $licence->getId(),
            $irhpPermitStock->getValidityYear()
        );

        return $totAuthVehicles - $allocatedPermitCount;
    }
}
