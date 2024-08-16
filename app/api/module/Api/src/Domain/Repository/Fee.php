<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\Fee\Fee as Entity;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\System\RefData as RefDataEntity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Fee
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Fee extends AbstractRepository
{
    protected $entity = Entity::class;

    protected $alias = 'f';

    /**
     * Fetch application interim fees
     *
     * @param int $applicationId Application ID
     * @param bool $outstanding Include fees that are outstanding
     * @param bool $paid Include fees that are paid
     *
     * @return array
     */
    public function fetchInterimFeesByApplicationId($applicationId, $outstanding = false, $paid = false)
    {
        $doctrineQb = $this->getQueryByApplicationFeeTypeFeeType(
            $applicationId,
            FeeTypeEntity::FEE_TYPE_GRANTINT
        );

        if ($outstanding && !$paid) {
            $this->whereOutstandingFee($doctrineQb);
        }
        if ($paid && !$outstanding) {
            $this->wherePaidFee($doctrineQb);
        }
        if ($paid && $outstanding) {
            $this->whereOutstandingOrPaidFee($doctrineQb);
        }

        return $doctrineQb->getQuery()->getResult();
    }

    public function fetchInterimRefunds($after, $before, $sort, $order, array $trafficArea = [])
    {
        $doctrineQb = $this->createQueryBuilder();

        $this->getQueryBuilder()
            ->modifyQuery($doctrineQb)
            ->withRefdata()
            ->with('feeTransactions', 'ftr')
            ->with($this->alias . '.licence', 'l')
            ->with('l.organisation', 'o')
            ->order($sort, $order);

        $doctrineQb->andWhere($doctrineQb->expr()->in($this->alias . '.feeStatus', ':feeStatus'))
            ->setParameter(
                'feeStatus',
                [
                    Entity::STATUS_REFUNDED,
                    Entity::STATUS_REFUND_FAILED,
                    Entity::STATUS_REFUND_PENDING
                ]
            );

        $doctrineQb->leftJoin($this->alias . '.application', 'a')
            ->andWhere($doctrineQb->expr()->isNotNull("COALESCE(a.withdrawnDate, a.refusedDate, a.grantedDate)"));

        $doctrineQb->join($this->alias . '.feeType', 'fty')
            ->andWhere($doctrineQb->expr()->eq('fty.feeType', ':feeType'));
        $doctrineQb->setParameter('feeType', FeeTypeEntity::FEE_TYPE_GRANTINT);

        if (!is_null($after) && !is_null($before)) {
            $doctrineQb
                ->andWhere($doctrineQb->expr()->gte($this->alias . '.invoicedDate', ':after'))
                ->setParameter('after', $after);

            $doctrineQb
                ->andWhere($doctrineQb->expr()->lte($this->alias . '.invoicedDate', ':before'))
                ->setParameter('before', $before);
        }

        if (!empty($trafficArea)) {
            $doctrineQb->andWhere($doctrineQb->expr()->in('l.trafficArea', ':trafficArea'))
                ->setParameter(
                    'trafficArea',
                    $trafficArea
                );
        }

        return $doctrineQb->getQuery()->getResult();
    }

    /**
     * Fetch outstanding fees for an organisation
     * (only those associated to a valid licence or in progress application)
     *
     * @param int $organisationId organisation ID
     * @param bool $hideForCeasedLicences if true, then hide fees where the licence has ceased
     *
     * @return array
     */
    public function fetchOutstandingFeesByOrganisationId(
        $organisationId,
        $hideForCeasedLicences = false,
        $hideContinuationsFees = false
    ) {
        $doctrineQb = $this->createQueryBuilder();

        $this->getQueryBuilder()
            ->modifyQuery($doctrineQb)
            ->withRefdata()
            ->with('feeTransactions', 'ft')
            ->with('ft.transaction', 't')
            ->with('t.status')
            ->order('invoicedDate', 'ASC');

        $this->whereOutstandingFee($doctrineQb);
        $this->whereCurrentLicenceOrApplicationFee($doctrineQb, $organisationId);

        if ($hideForCeasedLicences) {
            $this->hideForCeasedLicences($doctrineQb);
        }
        if ($hideContinuationsFees) {
            $this->hideContinuationFees($doctrineQb);
        }

        return $doctrineQb->getQuery()->getResult();
    }

    /**
     * Hide continuation fees
     *
     * @param QueryBuilder $doctrineQb query builder
     *
     * @return void
     */
    protected function hideContinuationFees($doctrineQb)
    {
        $doctrineQb
            ->innerJoin($this->alias . '.feeType', 'ftype')
            ->andWhere($doctrineQb->expr()->neq('ftype.feeType', ':feeType'))
            ->setParameter('feeType', RefDataEntity::FEE_TYPE_CONT);
    }

    /**
     * Add a check to hide fees where licence has ceased
     *
     * @param QueryBuilder $doctrineQb Doctrine Query Builder
     *
     * @return void
     */
    protected function hideForCeasedLicences($doctrineQb)
    {
        $ceasedStatuses = [
            LicenceEntity::LICENCE_STATUS_CONTINUATION_NOT_SOUGHT,
            LicenceEntity::LICENCE_STATUS_REVOKED,
            LicenceEntity::LICENCE_STATUS_SURRENDERED,
            LicenceEntity::LICENCE_STATUS_TERMINATED
        ];

        $doctrineQb->andWhere(
            $doctrineQb->expr()->notIn('l.status', ':ceasedStatuses')
        )->setParameter('ceasedStatuses', $ceasedStatuses);
    }

    /**
     * Fetch outstanding fees for an application
     *
     * @param int $applicationId Application ID
     *
     * @return array
     */
    public function fetchOutstandingFeesByApplicationId($applicationId)
    {
        $doctrineQb = $this->createQueryBuilder();

        $this->whereOutstandingFee($doctrineQb);

        $doctrineQb
            ->andWhere($doctrineQb->expr()->eq($this->alias . '.application', ':application'))
            ->setParameter('application', $applicationId);

        return $doctrineQb->getQuery()->getResult();
    }

    /**
     * Fetch outstanding grant fees for an application
     *
     * @param int $applicationId Application ID
     *
     * @return array
     */
    public function fetchOutstandingGrantFeesByApplicationId($applicationId)
    {
        $doctrineQb = $this->createQueryBuilder();

        $this->whereOutstandingFee($doctrineQb);

        $doctrineQb
            ->innerJoin($this->alias . '.feeType', 'ft')
            ->andWhere($doctrineQb->expr()->eq($this->alias . '.application', ':application'))
            ->andWhere($doctrineQb->expr()->eq('ft.feeType', ':feeType'))
            ->setParameter('application', $applicationId)
            ->setParameter('feeType', RefDataEntity::FEE_TYPE_GRANT);

        return $doctrineQb->getQuery()->getResult();
    }

    /**
     * Fetch outstanding continuation fees for a licence
     *
     * @param int $licenceId Licence Id
     * @param DateTime $after if specified, only return fees with invoicedDate after this value
     * @param bool $hasAnyStatus outstanding or paid fee
     *
     * @return array
     */
    public function fetchOutstandingContinuationFeesByLicenceId($licenceId, $after = null, $hasAnyStatus = false)
    {
        $doctrineQb = $this->createQueryBuilder();

        $doctrineQb
            ->innerJoin($this->alias . '.feeType', 'ft')
            ->andWhere($doctrineQb->expr()->eq($this->alias . '.licence', ':licence'))
            ->andWhere($doctrineQb->expr()->eq('ft.feeType', ':feeType'))
            ->setParameter('licence', $licenceId)
            ->setParameter('feeType', RefDataEntity::FEE_TYPE_CONT);

        if (!$hasAnyStatus) {
            $this->whereOutstandingFee($doctrineQb);
        }

        if (!is_null($after)) {
            $doctrineQb
                ->andWhere($doctrineQb->expr()->gte($this->alias . '.invoicedDate', ':after'))
                ->setParameter('after', $after);
        }

        return $doctrineQb->getQuery()->getResult();
    }

    /**
     * Fetch outstanding fees by IDs
     *
     * @param array $ids List of Fees identifiers
     *
     * @return array
     */
    public function fetchOutstandingFeesByIds($ids)
    {
        $doctrineQb = $this->createQueryBuilder();

        $this->getQueryBuilder()
            ->modifyQuery($doctrineQb)
            ->withRefdata()
            ->with('licence')
            ->with('application')
            ->with('feeTransactions', 'ft')
            ->with('ft.transaction', 't')
            ->with('t.status')
            ->order('invoicedDate', 'ASC');

        $this->whereOutstandingFee($doctrineQb);

        $doctrineQb
            ->andWhere($doctrineQb->expr()->in($this->alias . '.id', ':feeIds'))
            ->setParameter('feeIds', $ids);

        return $doctrineQb->getQuery()->getResult();
    }

    /**
     * Fetch fees by IDs
     *
     * @param array $ids List of Fees identifiers
     *
     * @return array
     */
    public function fetchFeesByIds($ids)
    {
        $doctrineQb = $this->createQueryBuilder();

        $this->getQueryBuilder()
            ->modifyQuery($doctrineQb)
            ->withRefdata()
            ->with('licence')
            ->with('application')
            ->with('feeTransactions', 'ft')
            ->with('ft.transaction', 't')
            ->with('t.status')
            ->order('invoicedDate', 'ASC');

        $doctrineQb
            ->andWhere($doctrineQb->expr()->in($this->alias . '.id', ':feeIds'))
            ->setParameter('feeIds', $ids);

        return $doctrineQb->getQuery()->getResult();
    }

    /**
     * Fetch fees by irfoGvPermitId
     *
     * @param int $irfoGvPermitId Irfo Gv Permit Id
     *
     * @return array
     */
    public function fetchFeesByIrfoGvPermitId($irfoGvPermitId)
    {
        $doctrineQb = $this->createQueryBuilder();

        $this->getQueryBuilder()
            ->modifyQuery($doctrineQb)
            ->withRefdata()
            ->order('invoicedDate', 'ASC');

        $doctrineQb
            ->andWhere($doctrineQb->expr()->eq($this->alias . '.irfoGvPermit', ':irfoGvPermitId'))
            ->setParameter('irfoGvPermitId', $irfoGvPermitId);

        return $doctrineQb->getQuery()->getResult();
    }

    /**
     * Fetch all fees by irfoPsvAuthId
     *
     * @param int $irfoPsvAuthId Irfo Psv Auth Id
     * @param bool $outstanding Only get fees that are outstanding
     *
     * @return array
     */
    public function fetchFeesByIrfoPsvAuthId($irfoPsvAuthId, $outstanding = false)
    {
        $doctrineQb = $this->createQueryBuilder();

        $this->getQueryBuilder()
            ->modifyQuery($doctrineQb)
            ->withRefdata()
            ->order('invoicedDate', 'ASC');

        $doctrineQb
            ->andWhere($doctrineQb->expr()->eq($this->alias . '.irfoPsvAuth', ':irfoPsvAuthId'))
            ->setParameter('irfoPsvAuthId', $irfoPsvAuthId);

        if ($outstanding) {
            $this->whereOutstandingFee($doctrineQb);
        }

        return $doctrineQb->getQuery()->getResult();
    }

    /**
     * Fetch Application fee by irfoPsvAuthId
     *
     * @param int $irfoPsvAuthId Irfo Psv Auth Id
     *
     * @return array
     */
    public function fetchApplicationFeeByPsvAuthId($irfoPsvAuthId)
    {
        $fees = $this->fetchFeesByPsvAuthIdAndType($irfoPsvAuthId, RefDataEntity::FEE_TYPE_IRFOPSVAPP);

        if (count($fees) > 0) {
            return $fees[0];
        }
        return null;
    }

    /**
     * Fetch fees by irfoPsvAuthId
     *
     * @param int $irfoPsvAuthId Irfo Psv Auth Id
     * @param string $feeTypeFeeType Fee type
     *
     * @return array
     */
    public function fetchFeesByPsvAuthIdAndType($irfoPsvAuthId, $feeTypeFeeType)
    {
        $doctrineQb = $this->createQueryBuilder();
        $doctrineQb->join($this->alias . '.feeType', 'ft')
            ->andWhere($doctrineQb->expr()->eq('ft.feeType', ':feeTypeFeeType'))
            ->andWhere($doctrineQb->expr()->eq($this->alias . '.irfoPsvAuth', ':irfoPsvAuthId'))
            ->setParameter('irfoPsvAuthId', $irfoPsvAuthId)
            ->setParameter('feeTypeFeeType', $this->getRefdataReference($feeTypeFeeType));

        $this->getQueryBuilder()
            ->modifyQuery($doctrineQb)
            ->withRefdata()
            ->order('invoicedDate', 'DESC');

        return $doctrineQb->getQuery()->getResult();
    }

    /**
     * Fetch Latest Fee By Type Statuses And Application Id
     *
     * @param string $feeType Fee type
     * @param string $feeStatuses Fee status
     * @param int $applicationId Application Id
     *
     * @return null
     */
    public function fetchLatestFeeByTypeStatusesAndApplicationId(
        $feeType,
        $feeStatuses,
        $applicationId
    ) {
        $doctrineQb = $this->createQueryBuilder();
        $doctrineQb
            ->andWhere($doctrineQb->expr()->eq($this->alias . '.application', ':application'))
            ->andWhere($doctrineQb->expr()->in($this->alias . '.feeStatus', ':feeStatuses'))
            ->andWhere($doctrineQb->expr()->eq($this->alias . '.feeType', ':feeType'))
            ->setParameter('application', $applicationId)
            ->setParameter('feeStatuses', $feeStatuses)
            ->setParameter('feeType', $feeType)
            ->setMaxResults(1);

        $this->getQueryBuilder()
            ->modifyQuery($doctrineQb)
            ->withRefdata()
            ->order('invoicedDate', 'DESC');

        $results = $doctrineQb->getQuery()->getResult();

        if (!empty($results)) {
            return $results[0];
        }

        return null;
    }

    /**
     * Get a QueryBuilder for listing application fees of a certain feeType.feeType
     *
     * @param int $applicationId Application ID
     * @param string $feeTypeFeeType Ref data string eg \Dvsa\Olcs\Api\Entity\Fee\FeeType::FEE_TYPE_GRANTINT
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getQueryByApplicationFeeTypeFeeType($applicationId, $feeTypeFeeType)
    {
        $doctrineQb = $this->createQueryBuilder();
        $this->getQueryBuilder()->withRefdata()->order('invoicedDate', 'ASC');

        $doctrineQb->join($this->alias . '.feeType', 'ft')
            ->andWhere($doctrineQb->expr()->eq('ft.feeType', ':feeTypeFeeType'))
            ->andWhere($doctrineQb->expr()->eq($this->alias . '.application', ':applicationId'));

        $doctrineQb->setParameter('feeTypeFeeType', $this->getRefdataReference($feeTypeFeeType))
            ->setParameter('applicationId', $applicationId);

        return $doctrineQb;
    }

    /**
     * Add conditions to the query builder to only select fees that are outstanding
     *
     * @param \Doctrine\ORM\QueryBuilder $doctrineQb Doctrine Query Builder
     *
     * @return void
     */
    private function whereOutstandingFee(\Doctrine\ORM\QueryBuilder $doctrineQb)
    {
        $doctrineQb
            ->andWhere($doctrineQb->expr()->eq($this->alias . '.feeStatus', ':feeStatus'))
            ->setParameter('feeStatus', $this->getRefdataReference(Entity::STATUS_OUTSTANDING));
    }

    /**
     * Add conditions to the query builder to only select fees that are paid
     *
     * @param \Doctrine\ORM\QueryBuilder $doctrineQb Doctrine Query Builder
     *
     * @return void
     */
    private function wherePaidFee(QueryBuilder $doctrineQb)
    {
        $doctrineQb
            ->andWhere($doctrineQb->expr()->eq($this->alias . '.feeStatus', ':feeStatus'))
            ->setParameter('feeStatus', $this->getRefdataReference(Entity::STATUS_PAID));
    }

    /**
     * Add conditions to the query builder to only select fees that are outstanding or paid
     *
     * @param \Doctrine\ORM\QueryBuilder $doctrineQb Doctrine Query Builder
     *
     * @return void
     */
    private function whereOutstandingOrPaidFee(\Doctrine\ORM\QueryBuilder $doctrineQb)
    {
        $doctrineQb
            ->andWhere($doctrineQb->expr()->in($this->alias . '.feeStatus', ':feeStatus'))
            ->setParameter(
                'feeStatus',
                [
                    $this->getRefdataReference(Entity::STATUS_PAID),
                    $this->getRefdataReference(Entity::STATUS_OUTSTANDING)
                ]
            );
    }

    /**
     * Add conditions to the query builder to only select fees that are associated
     * to either:
     *  a) a valid/curtailed/suspended licence
     *  or
     *  b) an under consideration/granted application
     * for the given organisation
     *
     * @param \Doctrine\ORM\QueryBuilder $doctrineQb Doctrine Query Builder
     * @param int $organisationId Organisation id
     *
     * @return void
     */
    private function whereCurrentLicenceOrApplicationFee(\Doctrine\ORM\QueryBuilder $doctrineQb, $organisationId)
    {
        // NOTE: the way this query is built with all the left joins and orX conditions
        // makes it very slow when a number of fees in the system increases
        // as a full scan of the fee table is required to determine the results
        // it should be redone as a UNION of 2 SELECTs with INNER JOINs
        $doctrineQb
            ->leftJoin($this->alias . '.application', 'a')
            ->leftJoin($this->alias . '.licence', 'l')
            ->leftJoin('a.licence', 'al')
            ->andWhere(
                $doctrineQb->expr()->orX(
                    $doctrineQb->expr()->eq('l.organisation', ':organisationId'),
                    $doctrineQb->expr()->eq('al.organisation', ':organisationId')
                )
            )
            ->andWhere(
                $doctrineQb->expr()->orX(
                    $doctrineQb->expr()->in('a.status', ':appStatus'),
                    $doctrineQb->expr()->in('l.status', ':licStatus')
                )
            )
            ->andWhere(
                $doctrineQb->expr()->orX(
                    $doctrineQb->expr()->isNotNull($this->alias . '.licence'),
                    $doctrineQb->expr()->isNotNull($this->alias . '.application')
                )
            )
            ->setParameter('organisationId', $organisationId)
            ->setParameter(
                'appStatus',
                [
                    $this->getRefdataReference(ApplicationEntity::APPLICATION_STATUS_UNDER_CONSIDERATION),
                    $this->getRefdataReference(ApplicationEntity::APPLICATION_STATUS_GRANTED),
                ]
            )
            ->setParameter(
                'licStatus',
                [
                    $this->getRefdataReference(LicenceEntity::LICENCE_STATUS_VALID),
                    $this->getRefdataReference(LicenceEntity::LICENCE_STATUS_CURTAILED),
                    $this->getRefdataReference(LicenceEntity::LICENCE_STATUS_SUSPENDED),
                ]
            );
    }

    /**
     * Apply Filters
     *
     * @param QueryBuilder $qb Query Builder
     * @param \Dvsa\Olcs\Transfer\Query\Fee\FeeList $query Query
     *
     * @return void
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        $this->getQueryBuilder()
            ->filterByLicence($query->getLicence())
            ->filterByApplication($query->getApplication())
            ->filterByIds(!empty($query->getIds()) ? $query->getIds() : null);

        if ($query->getOrganisation() !== null) {
            // all fees linked to the organisation by irfo_gv_permit_id or irfo_psv_auth_id
            $qb
                ->leftJoin($this->alias . '.irfoGvPermit', 'igp')
                ->leftJoin($this->alias . '.irfoPsvAuth', 'ipa')
                // This where clause make the query run quicker by hinting to MySQL to use the indexes
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->isNotNull($this->alias . '.irfoGvPermit'),
                        $qb->expr()->isNotNull($this->alias . '.irfoPsvAuth')
                    )
                )
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->eq('igp.organisation', ':organisationId'),
                        $qb->expr()->eq('ipa.organisation', ':organisationId')
                    )
                )
                ->setParameter('organisationId', $query->getOrganisation());
        }

        if ($query->getBusReg() !== null) {
            /** @var array $busRegIds */
            $busRegIds = $this->getBusRegIdsForRouteFromBusRegId($query->getBusReg());
            $qb
                ->andWhere($qb->expr()->in($this->alias . '.busReg', ':busRegIds'))
                ->setParameter('busRegIds', $busRegIds);
        }

        if ($query->getTask() !== null) {
            $qb->andWhere($this->alias . '.task = :taskId');
            $qb->setParameter('taskId', $query->getTask());
        }

        if ($query->getIrfoGvPermit() !== null) {
            $qb->andWhere($this->alias . '.irfoGvPermit = :irfoGvPermitId');
            $qb->setParameter('irfoGvPermitId', $query->getIrfoGvPermit());
        }

        if ($query->getIrhpApplication() !== null) {
            $qb->andWhere($this->alias . '.irhpApplication = :irhpApplicationId');
            $qb->setParameter('irhpApplicationId', $query->getIrhpApplication());
        }

        if ($query->getIsMiscellaneous() !== null) {
            $qb->innerJoin($this->alias . '.feeType', 'ft')
                ->andWhere('ft.isMiscellaneous = :isMiscellaneous')
                ->setParameter('isMiscellaneous', $query->getIsMiscellaneous());
        }

        if ($query->getStatus() !== null) {
            $this->filterByStatus($qb, $query->getStatus());
        }

        $this->getQueryBuilder()->modifyQuery($qb)->withCreatedBy();
    }

    /**
     * Add Status criteria to query
     *
     * @param \Doctrine\ORM\QueryBuilder $qb Doctrine Query Builder
     * @param string $status Status
     *
     * @return void
     */
    private function filterByStatus(\Doctrine\ORM\QueryBuilder $qb, $status)
    {
        $feeStatus = match ($status) {
            'historical' => [
                Entity::STATUS_PAID,
                Entity::STATUS_CANCELLED,
                Entity::STATUS_REFUNDED,
                Entity::STATUS_REFUND_FAILED,
                Entity::STATUS_REFUND_PENDING,
            ],
            'all' => [],
            default => [
                Entity::STATUS_OUTSTANDING,
            ],
        };

        if (!empty($feeStatus)) {
            $qb
                ->andWhere($qb->expr()->in($this->alias . '.feeStatus', ':feeStatus'))
                ->setParameter('feeStatus', $feeStatus);
        }
    }

    /**
     * Get all bus reg ids for a route no
     *
     * @param int $busRegId Bus Registration Id
     *
     * @return array
     */
    protected function getBusRegIdsForRouteFromBusRegId($busRegId)
    {
        /** @var \Doctrine\ORM\QueryBuilder $qb */
        $qb = $this->getEntityManager()
            ->getRepository(BusRegEntity::class)
            ->createQueryBuilder('br');

        $qb
            ->select('br2.id')
            ->join(BusRegEntity::class, 'br2')
            ->where('br.routeNo = br2.routeNo')
            ->andWhere('br.id = :id')
            ->setParameter('id', $busRegId);

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Returns Fee with latest paid transaction
     *
     * @param int $applicationId App id
     *
     * @return \Dvsa\Olcs\Api\Entity\Fee\Fee
     */
    public function fetchLatestPaidFeeByApplicationId($applicationId)
    {
        $qb = $this->createQueryBuilder()
            ->innerJoin($this->alias . '.feeTransactions', 'ft')
            ->innerJoin('ft.transaction', 't')
            ->addOrderBy('t.completedDate', 'DESC')
            ->addOrderBy('t.id', 'DESC');

        $qb->andWhere($qb->expr()->eq($this->alias . '.application', ':application'))
            ->setParameter('application', $applicationId)
            ->setMaxResults(1);

        $results = $qb->getQuery()->getResult(Query::HYDRATE_OBJECT);

        return count($results) ? current($results) : [];
    }

    /**
     * Fetch latest paid continuation fee
     *
     * @param $licenceId licence id
     *
     * @return \Dvsa\Olcs\Api\Entity\Fee\Fee|null
     */
    public function fetchLatestPaidContinuationFee($licenceId)
    {
        $qb = $this->createQueryBuilder()
            ->innerJoin($this->alias . '.feeTransactions', 'ft')
            ->innerJoin($this->alias . '.feeType', 'ftp')
            ->innerJoin('ft.transaction', 't')
            ->addOrderBy('t.completedDate', 'DESC')
            ->addOrderBy('t.id', 'DESC');

        $qb->andWhere($qb->expr()->eq($this->alias . '.licence', ':licence'))
            ->andWhere($qb->expr()->eq($this->alias . '.feeStatus', ':feeStatus'))
            ->andWhere($qb->expr()->eq('ftp.feeType', ':feeType'))
            ->setParameter('licence', $licenceId)
            ->setMaxResults(1)
            ->setParameter('feeType', RefDataEntity::FEE_TYPE_CONT)
            ->setParameter('feeStatus', Entity::STATUS_PAID);

        $results = $qb->getQuery()->getResult(Query::HYDRATE_OBJECT);

        return count($results) ? current($results) : null;
    }

    /**
     * Fetch latest fee by type and application id
     *
     * @param string $feeType Fee type
     * @param int $applicationId Application Id
     *
     * @return array
     */
    public function fetchFeeByTypeAndApplicationId($feeType, $applicationId)
    {
        $doctrineQb = $this->createQueryBuilder();
        $doctrineQb
            ->join($this->alias . '.feeType', 'ft')
            ->andWhere($doctrineQb->expr()->eq($this->alias . '.application', ':application'))
            ->andWhere($doctrineQb->expr()->eq('ft.feeType', ':feeType'))
            ->setParameter('application', $applicationId)
            ->setParameter('feeType', $feeType);

        return $doctrineQb->getQuery()->getResult();
    }
}
