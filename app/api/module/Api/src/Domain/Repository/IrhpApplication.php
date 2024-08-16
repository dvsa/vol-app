<?php

/**
 * IrhpApplication
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\Query\Permits\ExpireIrhpApplications as ExpireIrhpApplicationsQuery;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as Entity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit as IrhpCandidatePermitEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit;
use RuntimeException;

class IrhpApplication extends AbstractRepository
{
    protected $entity = Entity::class;
    protected $alias = 'ia';

    /**
     * @return array
     */
    public function fetchByLicence(int $licence)
    {
        return $this->fetchByX('licence', [$licence]);
    }

    /**
     * Fetch all applications by IRHP permit window id and status
     *
     * @param int|\Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow $windowId    IRHP Permit Window
     * @param array                                              $appStatuses List of app statuses
     *
     * @return array
     */
    public function fetchByWindowId($windowId, $appStatuses)
    {
        $qb = $this->createQueryBuilder();

        $qb
            ->innerJoin($this->alias . '.irhpPermitApplications', 'ipa')
            ->innerJoin('ipa.irhpPermitWindow', 'ipw')
            ->where('ipw.id = :windowId')
            ->andWhere($qb->expr()->in($this->alias . '.status', ':appStatuses'))
            ->setParameter('windowId', $windowId)
            ->setParameter('appStatuses', $appStatuses);

        return $qb->getQuery()->getResult();
    }

    /**
     * Fetch all applications in awaiting fee status
     *
     * @return array
     */
    public function fetchAllAwaitingFee()
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('ia')
            ->from(Entity::class, 'ia')
            ->where('ia.status = :status')
            ->setParameter('status', IrhpInterface::STATUS_AWAITING_FEE)
            ->getQuery()->getResult();
    }

    /**
     * Fetch applications for the roadworthiness report
     * - excludes not yet submitted, cancelled and withdrawn
     * - only permit type id 6 & 7
     *
     *
     * @return array
     */
    public function fetchForRoadworthinessReport(string $startDate, string $endDate): array
    {
        $excludeStatuses = [
            IrhpInterface::STATUS_NOT_YET_SUBMITTED,
            IrhpInterface::STATUS_CANCELLED,
            IrhpInterface::STATUS_WITHDRAWN,
        ];

        $qb = $this->createQueryBuilder();

        $qb->where($qb->expr()->in($this->alias . '.irhpPermitType', ':irhpPermitTypes'));
        $qb->andWhere($qb->expr()->notIn($this->alias . '.status', ':excludeStatuses'));
        $qb->andWhere($qb->expr()->between($this->alias . '.dateReceived', ':startDate', ':endDate'));

        $qb->setParameter('irhpPermitTypes', IrhpPermitType::CERTIFICATE_TYPES);
        $qb->setParameter('excludeStatuses', $excludeStatuses);
        $qb->setParameter('startDate', $startDate);
        $qb->setParameter('endDate', $endDate);

        return $qb->getQuery()->getResult();
    }

    /**
     * Fetch all valid roadworthiness applications
     *
     * @return array
     */
    public function fetchAllValidRoadworthiness()
    {
        $qb = $this->createQueryBuilder();

        $qb->where($qb->expr()->eq($this->alias . '.status', ':status'))
            ->andWhere($qb->expr()->in($this->alias . '.irhpPermitType', ':irhpPermitTypes'))
            ->setParameter('status', IrhpInterface::STATUS_VALID)
            ->setParameter('irhpPermitTypes', IrhpPermitType::CERTIFICATE_TYPES);

        return $qb->getQuery()->getResult();
    }

    /**
     * Mark all applications without valid permits as expired
     *
     * @return void
     */
    public function markAsExpired()
    {
        $this->getDbQueryManager()->get(ExpireIrhpApplicationsQuery::class)->execute([]);
    }

    /**
     * Fetch application ids within a stock that are awaiting scoring
     *
     * @param int $stockId
     *
     * @return array
     */
    public function fetchApplicationIdsAwaitingScoring($stockId)
    {
        $statement = $this->getEntityManager()->getConnection()->executeQuery(
            'select e.id from irhp_application e ' .
            'inner join licence as l on e.licence_id = l.id ' .
            'where e.id in (' .
            '    select irhp_application_id from irhp_permit_application where irhp_permit_window_id in (' .
            '        select id from irhp_permit_window where irhp_permit_stock_id = :stockId' .
            '    )' .
            ') ' .
            'and e.status = :status ' .
            'and l.licence_type in (:licenceType1, :licenceType2, :licenceType3) ' .
            'and l.status in (:licenceStatus1, :licenceStatus2, :licenceStatus3)',
            [
                'stockId' => $stockId,
                'status' => IrhpInterface::STATUS_UNDER_CONSIDERATION,
                'licenceType1' => LicenceEntity::LICENCE_TYPE_RESTRICTED,
                'licenceType2' => LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                'licenceType3' => LicenceEntity::LICENCE_TYPE_STANDARD_NATIONAL,
                'licenceStatus1' => LicenceEntity::LICENCE_STATUS_VALID,
                'licenceStatus2' => LicenceEntity::LICENCE_STATUS_SUSPENDED,
                'licenceStatus3' => LicenceEntity::LICENCE_STATUS_CURTAILED
            ]
        );

        return array_column($statement->fetchAll(), 'id');
    }

    /**
     * Fetch application ids within a stock that are both in scope and under consideration
     *
     * @param int $stockId
     *
     * @return array
     */
    public function fetchInScopeUnderConsiderationApplicationIds($stockId)
    {
        $statement = $this->getEntityManager()->getConnection()->executeQuery(
            'select e.id from irhp_application e ' .
            'where e.id in (' .
            '    select irhp_application_id from irhp_permit_application where irhp_permit_window_id in (' .
            '        select id from irhp_permit_window where irhp_permit_stock_id = :stockId' .
            '    )' .
            ') ' .
            'and e.in_scope = 1 ' .
            'and e.status = :status',
            [
                'stockId' => $stockId,
                'status' => IrhpInterface::STATUS_UNDER_CONSIDERATION
            ]
        );

        return array_column($statement->fetchAll(), 'id');
    }

    /**
     * Whether the specified stock has applications that are both in scope and under consideration
     *
     * @param int $stockId
     *
     * @return bool
     */
    public function hasInScopeUnderConsiderationApplications($stockId)
    {
        $applicationIds = $this->fetchInScopeUnderConsiderationApplicationIds($stockId);

        return count($applicationIds) > 0;
    }

    /**
     * Removes the existing scope from the specified stock id
     *
     * @param int $stockId
     */
    public function clearScope($stockId)
    {
        $statement = $this->getEntityManager()->getConnection()->executeQuery(
            'update irhp_application e ' .
            'set e.in_scope = 0 ' .
            'where e.id in (' .
            '    select irhp_application_id from irhp_permit_application where irhp_permit_window_id in (' .
            '        select id from irhp_permit_window where irhp_permit_stock_id = :stockId' .
            '    )' .
            ')',
            ['stockId' => $stockId]
        );

        $statement->execute();
    }

    /**
     * Applies a new scope to the specified stock id
     *
     * @param int $stockId
     */
    public function applyScope($stockId)
    {
        $statement = $this->getEntityManager()->getConnection()->executeQuery(
            'update irhp_application as e ' .
            'inner join licence as l on e.licence_id = l.id ' .
            'set e.in_scope = 1 ' .
            'where e.id in (' .
            '    select irhp_application_id from irhp_permit_application where irhp_permit_window_id in (' .
            '        select id from irhp_permit_window where irhp_permit_stock_id = :stockId' .
            '    )' .
            ') ' .
            'and e.status = :status ' .
            'and l.licence_type in (:licenceType1, :licenceType2, :licenceType3) ' .
            'and l.status in (:licenceStatus1, :licenceStatus2, :licenceStatus3)',
            [
                'stockId' => $stockId,
                'status' => IrhpInterface::STATUS_UNDER_CONSIDERATION,
                'licenceType1' => LicenceEntity::LICENCE_TYPE_RESTRICTED,
                'licenceType2' => LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                'licenceType3' => LicenceEntity::LICENCE_TYPE_STANDARD_NATIONAL,
                'licenceStatus1' => LicenceEntity::LICENCE_STATUS_VALID,
                'licenceStatus2' => LicenceEntity::LICENCE_STATUS_SUSPENDED,
                'licenceStatus3' => LicenceEntity::LICENCE_STATUS_CURTAILED
            ]
        );

        $statement->execute();
    }

    /**
     * Returns the ids and emissions categories of in scope candidate permits within the specified stock where
     * the associated application has requested the specified sector, ordered by randomised score descending
     *
     * @param int $stockId
     * @param string $applicationEntityName
     * @param int $sectorsId
     *
     * @return array
     */
    public function getScoreOrderedBySectorInScope($stockId, $sectorsId)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('icp.id, IDENTITY(icp.requestedEmissionsCategory) as emissions_category')
            ->from(IrhpCandidatePermitEntity::class, 'icp')
            ->innerJoin('icp.irhpPermitApplication', 'ipa')
            ->innerJoin('ipa.irhpPermitWindow', 'ipw')
            ->innerJoin('ipa.irhpApplication', 'epa')
            ->where('IDENTITY(ipw.irhpPermitStock) = ?1')
            ->andWhere('IDENTITY(epa.sectors) = ?2')
            ->andWhere('epa.inScope = 1')
            ->orderBy('icp.randomizedScore', 'DESC')
            ->setParameter(1, $stockId)
            ->setParameter(2, $sectorsId)
            ->getQuery()
            ->getScalarResult();
    }

    /**
     * Returns the count of candidate permits in the specified stock that are marked as successful
     * associated application relates to a licence for the specified jurisdiction/devolved administ
     *
     * @param int $stockId
     * @param int $jurisdictionId
     *
     * @return int
     */
    public function getSuccessfulDaCountInScope($stockId, $jurisdictionId)
    {
        $result = $this->getEntityManager()->createQueryBuilder()
            ->select('count(icp.id)')
            ->from(IrhpCandidatePermitEntity::class, 'icp')
            ->innerJoin('icp.irhpPermitApplication', 'ipa')
            ->innerJoin('ipa.irhpApplication', 'epa')
            ->innerJoin('ipa.irhpPermitWindow', 'ipw')
            ->innerJoin('epa.licence', 'l')
            ->where('IDENTITY(ipw.irhpPermitStock) = ?1')
            ->andWhere('icp.successful = 1')
            ->andWhere('IDENTITY(l.trafficArea) = ?2')
            ->andWhere('epa.inScope = 1')
            ->setParameter(1, $stockId)
            ->setParameter(2, $jurisdictionId)
            ->getQuery()
            ->getSingleScalarResult();

        if (is_null($result)) {
            return 0;
        }

        return $result;
    }

    /**
     * Returns the ids and requested emissions categories of candidate permits within the specified
     * scope and unsuccessful, ordered by randomised score descending. Optional parameter to furthe
     * by the traffic area of the associated application
     *
     * @param int $stockId
     * @param int $trafficAreaId (optional)
     *
     * @return array
     */
    public function getUnsuccessfulScoreOrderedInScope($stockId, $trafficAreaId = null)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
            ->select('icp.id, IDENTITY(icp.requestedEmissionsCategory) as emissions_category')
            ->from(IrhpCandidatePermitEntity::class, 'icp')
            ->innerJoin('icp.irhpPermitApplication', 'ipa')
            ->innerJoin('ipa.irhpPermitWindow', 'ipw')
            ->innerJoin('ipa.irhpApplication', 'epa')
            ->where('IDENTITY(ipw.irhpPermitStock) = ?1')
            ->andWhere('icp.successful = 0')
            ->andWhere('epa.inScope = 1')
            ->orderBy('icp.randomizedScore', 'DESC')
            ->setParameter(1, $stockId);

        if (!is_null($trafficAreaId)) {
            $queryBuilder->innerJoin('epa.licence', 'l')
            ->andWhere('IDENTITY(l.trafficArea) = ?2')
            ->setParameter(2, $trafficAreaId);
        }

        return $queryBuilder->getQuery()->getScalarResult();
    }

    /**
     * Returns the count of candidate permits in the specified stock marked as successful, filtered
     * category if specified
     *
     * @param int $stockId
     * @param string $assignedEmissionsCategoryId (optional)
     *
     * @return int
     */
    public function getSuccessfulCountInScope($stockId, $assignedEmissionsCategoryId = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('count(icp)')
            ->from(IrhpCandidatePermitEntity::class, 'icp')
            ->innerJoin('icp.irhpPermitApplication', 'ipa')
            ->innerJoin('ipa.irhpPermitWindow', 'ipw')
            ->innerJoin('ipa.irhpApplication', 'epa')
            ->where('IDENTITY(ipw.irhpPermitStock) = ?1')
            ->setParameter(1, $stockId)
            ->andWhere('icp.successful = 1')
            ->andWhere('epa.inScope = 1');

        if (!is_null($assignedEmissionsCategoryId)) {
            $qb->andWhere('IDENTITY(icp.assignedEmissionsCategory) = ?2')
                ->setParameter(2, $assignedEmissionsCategoryId);
        }

        return $qb->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Returns the candidate permits in the specified stock marked as successful
     *
     * @param int $stockId
     *
     * @return array
     */
    public function getSuccessfulScoreOrderedInScope($stockId)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('icp')
            ->from(IrhpCandidatePermitEntity::class, 'icp')
            ->innerJoin('icp.irhpPermitApplication', 'ipa')
            ->innerJoin('ipa.irhpPermitWindow', 'ipw')
            ->innerJoin('ipa.irhpApplication', 'epa')
            ->where('IDENTITY(ipw.irhpPermitStock) = ?1')
            ->andWhere('icp.successful = 1')
            ->andWhere('epa.inScope = 1')
            ->orderBy('icp.randomizedScore', 'DESC')
            ->setParameter(1, $stockId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Retrieves the ids of candidate permits and corresponding licence numbers in scope for the cu
     *
     * @param int $stockId the Id of the IrhpPermitStock that the scoring will be for
     *
     * @return array a list of candidate permit ids and corresponding licence numbers
     */
    public function fetchDeviationSourceValues($stockId)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select(
                'icp.id as candidatePermitId, l.licNo, epa.id as applicationId,' .
                '(ipa.requiredEuro5 + ipa.requiredEuro6) as permitsRequired'
            )
            ->from(IrhpCandidatePermitEntity::class, 'icp')
            ->innerJoin('icp.irhpPermitApplication', 'ipa')
            ->innerJoin('ipa.irhpPermitWindow', 'ipw')
            ->innerJoin('ipa.irhpApplication', 'epa')
            ->innerJoin('epa.licence', 'l')
            ->where('IDENTITY(ipw.irhpPermitStock) = ?1')
            ->andWhere('epa.inScope = 1')
            ->setParameter(1, $stockId)
            ->getQuery()
            ->getScalarResult();
    }

    /**
     * Fetch a flat list of application to country associations within the specified stock
     *
     * @param int $stockId
     *
     * @return array
     */
    public function fetchApplicationIdToCountryIdAssociations($stockId)
    {
        $statement = $this->getEntityManager()->getConnection()->executeQuery(
            'select e.id as applicationId, eacl.country_id as countryId ' .
            'from irhp_application_country_link eacl ' .
            'inner join irhp_application as e on e.id = eacl.irhp_application_id ' .
            'where e.id in (' .
            '    select irhp_application_id from irhp_permit_application where irhp_permit_window_id in (' .
            '        select id from irhp_permit_window where irhp_permit_stock_id = :stockId' .
            '    )' .
            ') ' .
            'and e.in_scope = 1 ',
            ['stockId' => $stockId]
        );

        return $statement->fetchAll();
    }

    /**
     * Retrieves a partial list of column values for the scoring report
     *
     * @param int $irhpPermitStockId the Id of the IrhpPermitStock that the scoring will be for
     *
     * @return array
     */
    public function fetchScoringReport($irhpPermitStockId)
    {
        $columns = [
            'icp.id as candidatePermitId',
            'epa.id as applicationId',
            'o.name as organisationName',
            'icp.applicationScore as candidatePermitApplicationScore',
            'icp.intensityOfUse as candidatePermitIntensityOfUse',
            'icp.randomFactor as candidatePermitRandomFactor',
            'icp.randomizedScore as candidatePermitRandomizedScore',
            'IDENTITY(icp.requestedEmissionsCategory) as candidatePermitRequestedEmissionsCategory',
            'IDENTITY(icp.assignedEmissionsCategory) as candidatePermitAssignedEmissionsCategory',
            'IDENTITY(epa.internationalJourneys) as applicationInternationalJourneys',
            'COALESCE(s.name, \'N/A\') as applicationSectorName',
            'l.licNo as licenceNo',
            'ta.id as trafficAreaId',
            'ta.name as trafficAreaName',
            'icp.successful as candidatePermitSuccessful',
            'IDENTITY(icp.irhpPermitRange) as candidatePermitRangeId'
        ];

        return $this->getEntityManager()->createQueryBuilder()
            ->select(implode(', ', $columns))
            ->from(IrhpCandidatePermitEntity::class, 'icp')
            ->innerJoin('icp.irhpPermitApplication', 'ipa')
            ->innerJoin('ipa.irhpPermitWindow', 'ipw')
            ->innerJoin('ipa.irhpApplication', 'epa')
            ->innerJoin('epa.licence', 'l')
            ->leftJoin('epa.sectors', 's')
            ->innerJoin('l.trafficArea', 'ta')
            ->innerJoin('l.organisation', 'o')
            ->where('IDENTITY(ipw.irhpPermitStock) = ?1')
            ->andWhere('epa.status = ?2')
            ->andWhere('epa.inScope = 1')
            ->setParameter(1, $irhpPermitStockId)
            ->setParameter(2, IrhpInterface::STATUS_UNDER_CONSIDERATION)
            ->getQuery()
            ->getScalarResult();
    }

    /**
     * Fetch a summary of issued permits for selfserve
     *
     * @param int $organisationId
     *
     * @return array
     */
    public function fetchSelfserveIssuedPermitsSummary($organisationId)
    {
        $applicationStatuses = [IrhpInterface::STATUS_VALID];
        $orderBy = ['l.lic_no', 'trd.description', 'ia.id'];
        $filterByColumnName = 'l.organisation_id';
        $filterByColumnValue = $organisationId;

        return $this->fetchIssuedPermitsSummary(
            $applicationStatuses,
            $orderBy,
            $filterByColumnName,
            $filterByColumnValue
        );
    }

    /**
     * Fetch a summary of permit applications for selfserve
     *
     * @param int $organisationId
     *
     * @return array
     */
    public function fetchSelfserveApplicationsSummary($organisationId)
    {
        $applicationStatuses = [
            IrhpInterface::STATUS_NOT_YET_SUBMITTED,
            IrhpInterface::STATUS_UNDER_CONSIDERATION,
            IrhpInterface::STATUS_AWAITING_FEE,
            IrhpInterface::STATUS_FEE_PAID,
            IrhpInterface::STATUS_ISSUING,
        ];
        $orderBy = ['l.lic_no' => 'ASC', 'trd.description' => 'ASC', 'ia.id' => 'ASC'];
        $filterByColumnName = 'l.organisation_id';
        $filterByColumnValue = $organisationId;

        return $this->fetchApplicationsSummary(
            $applicationStatuses,
            $orderBy,
            $filterByColumnName,
            $filterByColumnValue
        );
    }

    /**
     * Fetch a summary of permit applications for internal
     *
     * @param int $licenceId
     * @param string|null $status
     *
     * @return array
     */
    public function fetchInternalApplicationsSummary($licenceId, $status = null)
    {
        $applicationStatuses = IrhpInterface::ALL_STATUSES;

        if (isset($status)) {
            // only return apps of specific status, if provided
            $applicationStatuses = [$status];
        }

        $orderBy = ['ia.id' => 'DESC'];
        $filterByColumnName = 'l.id';
        $filterByColumnValue = $licenceId;

        return $this->fetchApplicationsSummary(
            $applicationStatuses,
            $orderBy,
            $filterByColumnName,
            $filterByColumnValue
        );
    }

    /**
     * Fetch all not yet submitted bilateral applications
     *
     * @return array
     */
    public function fetchNotYetSubmittedBilateralApplications()
    {
        $qb = $this->createQueryBuilder();

        $qb->where($qb->expr()->eq($this->alias . '.status', ':status'))
            ->andWhere($qb->expr()->eq($this->alias . '.irhpPermitType', ':irhpPermitType'))
            ->setParameter('status', IrhpInterface::STATUS_NOT_YET_SUBMITTED)
            ->setParameter('irhpPermitType', IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL);

        return $qb->getQuery()->getResult();
    }

    /**
     * Fetch a summary of issued permits, customised in accordance with the supplied parameters
     *
     * @param string $filterByColumnName
     * @param int $filterByColumnValue
     *
     * @return array
     */
    private function fetchIssuedPermitsSummary(
        array $applicationStatuses,
        array $orderBy,
        $filterByColumnName,
        $filterByColumnValue
    ) {
        $statementParameters = [
            'permitType1' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE,
            'permitType2' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER,
            'permitStatus1' => IrhpPermit::STATUS_PENDING,
            'permitStatus2' => IrhpPermit::STATUS_AWAITING_PRINTING,
            'permitStatus3' => IrhpPermit::STATUS_PRINTING,
            'permitStatus4' => IrhpPermit::STATUS_PRINTED,
            'permitStatus5' => IrhpPermit::STATUS_ERROR,
            'filterByColumnValue' => $filterByColumnValue,
        ];

        $applicationStatusParameterIndex = 1;
        $applicationStatusSqlParameters = [];

        foreach ($applicationStatuses as $applicationStatus) {
            $parameterName = 'applicationStatus' . $applicationStatusParameterIndex;
            $applicationStatusSqlParameters[] = ':' . $parameterName;
            $statementParameters[$parameterName] = $applicationStatus;
            $applicationStatusParameterIndex++;
        }

        $sql = 'select ' .
            'concat (l.lic_no, \' / \', ia.id) as applicationRef, ' .
            'ia.id as id, ' .
            'ia.irhp_permit_type_id as typeId, ' .
            'ia.status as statusId, ' .
            'l.id as licenceId, ' .
            'l.lic_no as licNo, ' .
            'srd.description as statusDescription, ' .
            'trd.description as typeDescription, ' .
            'count(ip.id) as validPermitCount ' .
            'from ' .
            'irhp_application ia ' .
            'inner join licence l on ia.licence_id = l.id ' .
            'inner join ref_data srd on ia.status = srd.id ' .
            'left join irhp_permit_application ipa on ipa.irhp_application_id = ia.id ' .
            'left join irhp_permit ip on ip.irhp_permit_application_id = ipa.id ' .
            'inner join irhp_permit_type ipt on ia.irhp_permit_type_id = ipt.id ' .
            'inner join ref_data trd on ipt.name = trd.id ' .
            'where ' . $this->escapeColumnName($filterByColumnName) . ' = :filterByColumnValue ' .
            'and ia.status in (' . implode(', ', $applicationStatusSqlParameters) . ') ' .
            'and (' .
            '    ia.irhp_permit_type_id in (:permitType1, :permitType2) ' .
            '    or ' .
            '    ip.status in (:permitStatus1, :permitStatus2, :permitStatus3, :permitStatus4, :permitStatus5)' .
            ') ' .
            'group by ia.id';

        if (count($orderBy)) {
            $escapedOrderBy = [];
            foreach ($orderBy as $columnName) {
                $escapedOrderBy[] = $this->escapeColumnName($columnName);
            }

            $sql .= ' order by ' . implode(', ', $escapedOrderBy);
        }

        $statement = $this->getEntityManager()->getConnection()->executeQuery($sql, $statementParameters);
        return $statement->fetchAll();
    }

    /**
     * Fetch a summary of current applications, customised in accordance with the supplied parameters
     *
     * @param string $filterByColumnName
     * @param int $filterByColumnValue
     *
     * @return array
     */
    private function fetchApplicationsSummary(
        array $applicationStatuses,
        array $orderBy,
        $filterByColumnName,
        $filterByColumnValue
    ) {
        $statementParameters = [
            'filterByColumnValue' => $filterByColumnValue,
        ];

        $applicationStatusParameterIndex = 1;
        $applicationStatusSqlParameters = [];

        foreach ($applicationStatuses as $applicationStatus) {
            $parameterName = 'applicationStatus' . $applicationStatusParameterIndex;
            $applicationStatusSqlParameters[] = ':' . $parameterName;
            $statementParameters[$parameterName] = $applicationStatus;
            $applicationStatusParameterIndex++;
        }

        $sql = 'select ' .
            'concat (l.lic_no, \' / \', ia.id) as applicationRef, ' .
            'sum(ifnull(ipa.permits_required, 0) + ifnull(ipa.required_euro5, 0) + ifnull(ipa.required_euro6, 0) + ifnull(ipa.required_standard, 0) + ifnull(ipa.required_cabotage, 0)) as permitsRequired, ' .
            'ia.id as id, ' .
            'ia.irhp_permit_type_id as typeId, ' .
            'ia.status as statusId, ' .
            'ia.date_received as dateReceived, ' .
            'srd.description as statusDescription, ' .
            'trd.description as typeDescription, ' .
            'ips.period_name_key as periodNameKey, ' .
            'ips.valid_to as stockValidTo, ' .
            'l.id as licenceId ' .
            'from ' .
            'irhp_application ia ' .
            'inner join licence l on ia.licence_id = l.id ' .
            'inner join ref_data srd on ia.status = srd.id ' .
            'left join irhp_permit_application ipa on ipa.irhp_application_id = ia.id ' .
            'inner join irhp_permit_type ipt on ia.irhp_permit_type_id = ipt.id ' .
            'inner join ref_data trd on ipt.name = trd.id ' .
            'left join irhp_permit_window ipw on ipa.irhp_permit_window_id = ipw.id ' .
            'left join irhp_permit_stock ips on ipw.irhp_permit_stock_id = ips.id ' .
            'where ' . $this->escapeColumnName($filterByColumnName) . ' = :filterByColumnValue ' .
            'and ia.status in (' . implode(', ', $applicationStatusSqlParameters) . ') ' .
            'group by ia.id';

        if (count($orderBy)) {
            $escapedOrderBy = [];
            foreach ($orderBy as $columnName => $order) {
                $escapedOrderBy[] = trim(
                    sprintf(
                        '%s %s',
                        $this->escapeColumnName($columnName),
                        $order === 'DESC' ? 'DESC' : ''
                    )
                );
            }

            $sql .= ' order by ' . implode(', ', $escapedOrderBy);
        }

        $statement = $this->getEntityManager()->getConnection()->executeQuery($sql, $statementParameters);
        return $statement->fetchAll();
    }

    /**
     * Reformulate a column name consisting of an optional prefix and identifier into something usable within a query,
     * throwing a RuntimeException if it contains risky characters
     *
     * @param string $columnName
     *
     * @return string
     *
     * @throws RuntimeException
     */
    private function escapeColumnName($columnName)
    {
        $columnNameElements = explode('.', $columnName);
        $columnNameElementsCount = count($columnNameElements);

        if ($columnNameElementsCount == 1) {
            return $this->escapeColumnNameWithoutPrefix($columnNameElements);
        } elseif ($columnNameElementsCount == 2) {
            return $this->escapeColumnNameWithPrefix($columnNameElements);
        }

        throw new RuntimeException('Unexpected number of elements in column name ' . $columnName);
    }

    /**
     * Reformulate a column name consisting of an identifier
     *
     * @param array $columnNameElements
     *
     * return string
     */
    private function escapeColumnNameWithoutPrefix(array $columnNameElements)
    {
        $identifier = $columnNameElements[0];

        $this->validateIdentifier($identifier);

        return sprintf('`%s`', $identifier);
    }

    /**
     * Reformulate a column name consisting of a prefix and identifier
     *
     * @param array $columnNameElements
     *
     * return string
     */
    private function escapeColumnNameWithPrefix(array $columnNameElements)
    {
        $prefix = $columnNameElements[0];
        $identifier = $columnNameElements[1];

        $this->validatePrefix($prefix);
        $this->validateIdentifier($identifier);

        return sprintf('%s.`%s`', $prefix, $identifier);
    }

    /**
     * Validate the prefix element of a column name
     *
     * @param string $prefix
     *
     * @throws RuntimeException
     */
    private function validatePrefix($prefix)
    {
        if (preg_match('/^[a-zA-Z0-9]+$/', $prefix) !== 1) {
            throw new RuntimeException('Unpermitted characters in prefix ' . $prefix);
        }
    }

    /**
     * Validate the identifier element of a column name
     *
     * @param string $identifier
     *
     * @throws RuntimeException
     */
    private function validateIdentifier($identifier)
    {
        if (preg_match('/^[a-zA-Z0-9_]+$/', $identifier) !== 1) {
            throw new RuntimeException('Unpermitted characters in identifier ' . $identifier);
        }
    }
}
