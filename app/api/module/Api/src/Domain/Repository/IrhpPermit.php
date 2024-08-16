<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Repository\Query\Permits\ExpireIrhpPermits as ExpireIrhpPermitsQuery;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit as Entity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange as IrhpPermitRangeEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType as IrhpPermitTypeEntity;
use Dvsa\Olcs\Transfer\Query\IrhpPermit\GetList;
use Dvsa\Olcs\Transfer\Query\IrhpPermit\GetListByIrhpId;
use Dvsa\Olcs\Transfer\Query\IrhpPermit\GetListByLicence;
use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrint;
use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrintConfirm;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use PDO;

/**
 * IRHP Permit
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class IrhpPermit extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Returns the count of permits in the specified stock, filtered by emissions category if provided
     *
     * @param int $stockId
     * @param string $emissionsCategoryId (optional)
     *
     * @return int
     */
    public function getPermitCount($stockId, $emissionsCategoryId = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('count(ip.id)')
            ->from(Entity::class, 'ip')
            ->innerJoin('ip.irhpPermitRange', 'ipr')
            ->where('IDENTITY(ipr.irhpPermitStock) = ?1')
            ->andWhere('ipr.ssReserve = false')
            ->andWhere('ipr.lostReplacement = false')
            ->setParameter(1, $stockId);

        if (!is_null($emissionsCategoryId)) {
            $qb->andWhere('IDENTITY(ipr.emissionsCategory) = ?2')
                ->setParameter(2, $emissionsCategoryId);
        }

        return $qb->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Returns the count of permits in the specified range
     *
     * @param int $rangeId
     *
     * @return int
     */
    public function getPermitCountByRange($rangeId)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('count(ip.id)')
            ->from(Entity::class, 'ip')
            ->where('IDENTITY(ip.irhpPermitRange) = ?1')
            ->setParameter(1, $rangeId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Returns the count of ecmt annual permits by licence and stock end year
     *
     * @param int $licenceId
     * @param int $stockEndYear
     *
     * @return int
     */
    public function getEcmtAnnualPermitCountByLicenceAndStockEndYear($licenceId, $stockEndYear)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('count(ip.id)')
            ->from(Entity::class, 'ip')
            ->innerJoin('ip.irhpPermitRange', 'ipr')
            ->innerJoin('ipr.irhpPermitStock', 'ips')
            ->innerJoin('ip.irhpPermitApplication', 'ipa')
            ->innerJoin('ipa.irhpApplication', 'ia')
            ->where('IDENTITY(ia.licence) = ?1')
            ->andWhere('YEAR(ips.validTo) = ?2')
            ->andWhere('IDENTITY(ips.irhpPermitType) = ?3')
            ->setParameter(1, $licenceId)
            ->setParameter(2, $stockEndYear)
            ->setParameter(3, IrhpPermitTypeEntity::IRHP_PERMIT_TYPE_ID_ECMT)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Returns an array of assigned permit numbers in the specified range
     *
     * @param int $rangeId
     *
     * @return array
     */
    public function getAssignedPermitNumbersByRange($rangeId)
    {
        $rows = $this->getEntityManager()->createQueryBuilder()
            ->select('ip.permitNumber')
            ->from(Entity::class, 'ip')
            ->where('IDENTITY(ip.irhpPermitRange) = ?1')
            ->setParameter(1, $rangeId)
            ->getQuery()
            ->getScalarResult();

        return array_column($rows, 'permitNumber');
    }

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
        if ($query instanceof ReadyToPrint) {
            // this join would appear not to be required, but is used later when sorting the table by application id
            $qb->innerJoin('ipa.irhpApplication', 'ia');

            if ($query->getIrhpPermitStock() || $query->getIrhpPermitRangeType()) {
                $qb->innerJoin($this->alias . '.irhpPermitRange', 'ipr');

                if ($query->getIrhpPermitStock()) {
                    $qb->innerJoin('ipr.irhpPermitStock', 'ips')
                        ->andWhere($qb->expr()->eq('ips.id', ':stockId'))
                        ->setParameter('stockId', $query->getIrhpPermitStock());
                }

                if ($query->getIrhpPermitRangeType()) {
                    $rangeTypeCriteria
                        = IrhpPermitRangeEntity::BILATERAL_TYPES_CRITERIA[$query->getIrhpPermitRangeType()];

                    $qb->andWhere($qb->expr()->eq('ipr.journey', ':journey'))
                        ->andWhere($qb->expr()->eq('ipr.cabotage', ':cabotage'))
                        ->setParameter('journey', $rangeTypeCriteria['journey'])
                        ->setParameter('cabotage', $rangeTypeCriteria['cabotage']);
                }
            }

            $qb->andWhere($qb->expr()->in($this->alias . '.status', ':statuses'))
                ->setParameter('statuses', Entity::$readyToPrintStatuses);
        } elseif ($query instanceof ReadyToPrintConfirm) {
            $qb->andWhere($qb->expr()->in($this->alias . '.id', ':ids'))
                ->setParameter('ids', $query->getIds());
            $qb->orderBy($this->alias . '.permitNumber', 'ASC');
        }

        if (($query instanceof GetList) && ($query->getIrhpPermitApplication() != null)) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.irhpPermitApplication', ':irhpPermitApplication'));
            $qb->setParameter('irhpPermitApplication', $query->getIrhpPermitApplication());
        }

        if (($query instanceof GetListByIrhpId) && ($query->getIrhpApplication() != null)) {
            $qb->andWhere($qb->expr()->eq('ipa.irhpApplication', ':irhpId'))
                ->setParameter('irhpId', $query->getIrhpApplication());
        }

        if ($query instanceof GetListByLicence) {
            $statuses = Entity::ALL_STATUSES;

            if ($query->getStatus()) {
                // filter by specific status
                $statuses = [$query->getStatus()];
            } elseif ($query->getValidOnly()) {
                // valid only
                $statuses = Entity::$validStatuses;
            }

            $qb->innerJoin('ipa.irhpApplication', 'ia')
                ->innerJoin($this->alias . '.irhpPermitRange', 'ipr')
                ->innerJoin('ipr.irhpPermitStock', 'ips')
                ->leftJoin('ips.country', 'ipc')
                ->andWhere($qb->expr()->eq('ia.licence', ':licenceId'))
                ->setParameter('licenceId', $query->getLicence())
                ->andWhere($qb->expr()->in($this->alias . '.status', ':statuses'))
                ->setParameter('statuses', $statuses);

            if ($query->getIrhpPermitType()) {
                $qb->andWhere($qb->expr()->eq('ips.irhpPermitType', ':irhpPermitTypeId'))
                    ->setParameter('irhpPermitTypeId', $query->getIrhpPermitType());
            }

            if ($query->getCountry()) {
                $qb->andWhere($qb->expr()->eq('ips.country', ':countryId'))
                    ->setParameter('countryId', $query->getCountry());
            }

            $qb->orderBy('ipc.countryDesc', 'ASC');
            $qb->addOrderBy($this->alias . '.expiryDate', 'ASC');
            $qb->addOrderBy('ipa.id', 'ASC');
            $qb->addOrderBy($this->alias . '.permitNumber', 'ASC');
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
            ->with('irhpPermitApplication', 'ipa');
    }

    /**
     * @param int $permitNumber
     * @param int $permitRange
     * @return mixed
     */
    public function fetchByNumberAndRange($permitNumber, $permitRange)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('ip')
            ->from(Entity::class, 'ip')
            ->where('ip.permitNumber = ?1')
            ->andWhere('ip.irhpPermitRange = ?2')
            ->setParameter(1, $permitNumber)
            ->setParameter(2, $permitRange)
            ->getQuery()->execute();
    }

    /**
     * Returns the number of live permits for each stock belonging to the specified licence
     *
     * @param int $licenceId
     *
     * @return array
     */
    public function getLivePermitCountsGroupedByStock($licenceId)
    {
        $liveStatuses = [
            Entity::STATUS_PENDING,
            Entity::STATUS_AWAITING_PRINTING,
            Entity::STATUS_PRINTING,
            Entity::STATUS_PRINTED
        ];

        $dbalResult = $this->getEntityManager()->getConnection()->executeQuery(
            'select ips.id AS irhpPermitStockId, ' .
            'count(ip.id) AS irhpPermitCount ' .
            'from irhp_permit ip ' .
            'inner join irhp_permit_application ipa ON ip.irhp_permit_application_id = ipa.id ' .
            'and ip.status in (?) ' .
            'inner join irhp_application ia ON ipa.irhp_application_id = ia.id ' .
            'inner join irhp_permit_window ipw ON ipa.irhp_permit_window_id = ipw.id ' .
            'inner join irhp_permit_stock ips ON ipw.irhp_permit_stock_id = ips.id ' .
            'where ia.licence_id = ? ' .
            'group BY ips.id',
            [
                $liveStatuses,
                $licenceId
            ],
            [
                Types::STRING,
                PDO::PARAM_INT
            ]
        );

        return $dbalResult->fetchAllAssociative();
    }

    /**
     * Mark all permits with validity date in the past as expired
     *
     * @return void
     */
    public function markAsExpired()
    {
        $this->getDbQueryManager()->get(ExpireIrhpPermitsQuery::class)->execute([]);
    }
}
