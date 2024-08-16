<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitSectorQuota as Entity;

/**
 * IRHP Permit Sector Quota
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class IrhpPermitSectorQuota extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Returns a list of sector id/sector quota pairs relating to the specified stock, where the sector quotas are
     * greater than zero
     *
     * @param int $stockId
     *
     * @return array
     */
    public function fetchByNonZeroQuota($stockId)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('IDENTITY(ipsq.sector) as sectorId, ipsq.quotaNumber')
            ->from(Entity::class, 'ipsq')
            ->where('ipsq.quotaNumber > 0')
            ->andWhere('IDENTITY(ipsq.irhpPermitStock) = ?1')
            ->setParameter(1, $stockId)
            ->getQuery()
            ->getScalarResult();
    }

    /**
     * Fetch Sectors by Permit Stock ID
     *
     * @param int $irhpPermitStockId
     * @return array
     */
    public function fetchByIrhpPermitStockId($irhpPermitStockId)
    {
        $doctrineQb = $this->createQueryBuilder();

        $doctrineQb
            ->andWhere($doctrineQb->expr()->eq($this->alias . '.irhpPermitStock', ':irhpPermitStock'))
            ->setParameter('irhpPermitStock', $irhpPermitStockId);

        return $doctrineQb->getQuery()->getResult();
    }
}
