<?php

namespace Dvsa\Olcs\Api\Service\Permits\Availability;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\TransactionIsolationLevel;
use Dvsa\Olcs\Api\Domain\Repository\IrhpCandidatePermit as IrhpCandidatePermitRepository;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitRange as IrhpPermitRangeRepository;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitApplication as IrhpPermitApplicationRepository;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermit as IrhpPermitRepository;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as IrhpPermitStockRepository;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use RuntimeException;

class EmissionsCategoryAvailabilityCounter
{
    public const ERR_BAD_ISOLATION_LEVEL = 'Transaction isolation level must be set to REPEATABLE_READ';

    /**
     * Create service instance
     *
     *
     * @return EmissionsCategoryAvailabilityCounter
     */
    public function __construct(private readonly Connection $connection, private readonly IrhpPermitRangeRepository $irhpPermitRangeRepo, private readonly IrhpPermitApplicationRepository $irhpPermitApplicationRepo, private readonly IrhpPermitRepository $irhpPermitRepo, private readonly IrhpPermitStockRepository $irhpPermitStockRepo, private readonly IrhpCandidatePermitRepository $irhpCandidatePermitRepo)
    {
    }

    /**
     * Get the count of permits available to apply for within the scope of a specific stock and emissions category,
     * using a transaction to ensure read consistency
     *
     * @param int $irhpPermitStockId
     * @param int $emissionsCategoryId
     *
     * @return int
     *
     * @throws RuntimeException
     */
    public function getCount($irhpPermitStockId, $emissionsCategoryId)
    {
        if ($this->connection->getTransactionIsolation() != TransactionIsolationLevel::REPEATABLE_READ) {
            throw new RuntimeException(self::ERR_BAD_ISOLATION_LEVEL);
        }

        $this->connection->beginTransaction();
        $count = $this->getCountUsingQueries($irhpPermitStockId, $emissionsCategoryId);
        $this->connection->commit();

        return $count;
    }

    /**
     * Run queries to get the count of permits available to apply for
     *
     * @param int $irhpPermitStockId
     * @param int $emissionsCategoryId
     *
     * @return int
     */
    private function getCountUsingQueries($irhpPermitStockId, $emissionsCategoryId)
    {
        $combinedRangeSize = $this->irhpPermitRangeRepo->getCombinedRangeSize(
            $irhpPermitStockId,
            $emissionsCategoryId
        );

        if (is_null($combinedRangeSize)) {
            return 0;
        }

        $irhpPermitStock = $this->irhpPermitStockRepo->fetchById($irhpPermitStockId);
        $allocationMode = $irhpPermitStock->getAllocationMode();

        $permitsGranted = match ($allocationMode) {
            IrhpPermitStock::ALLOCATION_MODE_EMISSIONS_CATEGORIES => $this->irhpPermitApplicationRepo->getRequiredPermitCountWhereApplicationAwaitingPayment(
                $irhpPermitStockId,
                $emissionsCategoryId
            ),
            IrhpPermitStock::ALLOCATION_MODE_CANDIDATE_PERMITS => $this->irhpCandidatePermitRepo->fetchCountInStockWhereApplicationAwaitingFee(
                $irhpPermitStockId,
                $emissionsCategoryId
            ),
            default => throw new RuntimeException('Unsupported allocation mode: ' . $allocationMode),
        };

        $permitsAllocated = $this->irhpPermitRepo->getPermitCount($irhpPermitStockId, $emissionsCategoryId);

        return ($combinedRangeSize - ($permitsGranted + $permitsAllocated));
    }
}
