<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit as Entity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Query\IrhpCandidatePermit\GetListByIrhpApplication;
use Dvsa\Olcs\Transfer\Query\IrhpCandidatePermit\GetListByIrhpApplicationUnpaged;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * IRHP Candidate Permit
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class IrhpCandidatePermit extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Apply List Filters
     *
     * @param QueryBuilder   $qb    Doctrine Query Builder
     * @param QueryInterface $query Http Query
     *
     * @return void
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        if (
            ($query instanceof GetListByIrhpApplication || $query instanceof GetListByIrhpApplicationUnpaged)
            && $query->getIsPreGrant()
        ) {
            $qb->andWhere($qb->expr()->in('ia.status', ':status'))
                ->setParameter('status', IrhpInterface::PRE_GRANT_STATUSES);
            $qb->andWhere($qb->expr()->eq('ipa.irhpApplication', ':irhpApplicationId'))
                ->setParameter('irhpApplicationId', $query->getIrhpApplication());
        } elseif ($query instanceof GetListByIrhpApplication || $query instanceof GetListByIrhpApplicationUnpaged) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.successful', ':successful'))
                ->setParameter('successful', true);
            $qb->andWhere($qb->expr()->eq('ia.status', ':status'))
                ->setParameter('status', RefData::PERMIT_APP_STATUS_AWAITING_FEE);
            $qb->andWhere($qb->expr()->eq('ipa.irhpApplication', ':irhpApplicationId'))
                ->setParameter('irhpApplicationId', $query->getIrhpApplication());

            if ($query->getWantedOnly()) {
                $qb->andWhere($qb->expr()->eq($this->alias . '.wanted', ':wanted'))
                    ->setParameter('wanted', true);
            }
        }
    }

    /**
     * Add List Joins
     *
     * @param QueryBuilder $qb Doctrine Query Builder
     *
     * @return void
     */
    protected function applyListJoins(QueryBuilder $qb)
    {
        $this->getQueryBuilder()->modifyQuery($qb)
            ->with('irhpPermitApplication', 'ipa')
            ->with('ipa.irhpApplication', 'ia');
    }

    /**
     * Get the number of candidate permits in the specified range that belong to an application that is awaiting
     * payment of the issue fee
     *
     * @param int $rangeId
     *
     * @return int
     */
    public function fetchCountInRangeWhereApplicationAwaitingFee($rangeId)
    {
        $result = $this->getEntityManager()->createQueryBuilder()
            ->select('count(icp.id)')
            ->from(Entity::class, 'icp')
            ->innerJoin('icp.irhpPermitApplication', 'ipa')
            ->innerJoin('ipa.irhpApplication', 'ia')
            ->where('IDENTITY(icp.irhpPermitRange) = ?1')
            ->andWhere('ia.status = ?2')
            ->setParameter(1, $rangeId)
            ->setParameter(2, IrhpInterface::STATUS_AWAITING_FEE)
            ->getQuery()
            ->getSingleScalarResult();

        if (is_null($result)) {
            return 0;
        }

        return $result;
    }

    /**
     * Get the number of candidate permits in the specified stock that belong to an application that is awaiting
     * payment of the issue fee
     *
     * @param int $stockId
     * @param string $emissionsCategoryId
     *
     * @return int
     */
    public function fetchCountInStockWhereApplicationAwaitingFee($stockId, $emissionsCategoryId)
    {
        $result = $this->getEntityManager()->createQueryBuilder()
            ->select('count(icp.id)')
            ->from(Entity::class, 'icp')
            ->innerJoin('icp.irhpPermitApplication', 'ipa')
            ->innerJoin('icp.irhpPermitRange', 'ipr')
            ->innerJoin('ipa.irhpApplication', 'ia')
            ->where('IDENTITY(ipr.irhpPermitStock) = ?1')
            ->andWhere('ia.status = ?2')
            ->andWhere('IDENTITY(ipr.emissionsCategory) = ?3')
            ->setParameter(1, $stockId)
            ->setParameter(2, IrhpInterface::STATUS_AWAITING_FEE)
            ->setParameter(3, $emissionsCategoryId)
            ->getQuery()
            ->getSingleScalarResult();

        if (is_null($result)) {
            return 0;
        }

        return $result;
    }
}
