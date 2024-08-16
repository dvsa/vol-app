<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\DBAL\Result;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;
use Dvsa\Olcs\Api\Domain\Exception;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact as PhoneContactEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as Entity;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address as AddressEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\NonUniqueResultException;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\Query\Licence\InternationalGoodsReport;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence as TMLicenceEntity;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication as TMApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\GracePeriod as GracePeriodEntity;

/**
 * Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Licence extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Fetches a licence based on the case id
     *
     * @param int      $caseId      case id
     * @param int      $hydrateMode hydrate mode
     * @param int|null $version     version
     *
     * @return mixed
     * @throws Exception\NotFoundException
     * @throws Exception\RuntimeException
     * @throws Exception\VersionConflictException
     */
    public function fetchByCaseId($caseId, $hydrateMode = Query::HYDRATE_ARRAY, $version = null)
    {
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()->modifyQuery($qb)->withRefdata()->with('trafficArea', 'ta');
        $qb->innerJoin($this->alias . '.cases', 'c');

        $qb->andWhere($qb->expr()->eq('c.id', ':caseId'));
        $qb->setParameter('caseId', $caseId);

        $results = $qb->getQuery()->getResult($hydrateMode);

        if (empty($results)) {
            throw new Exception\NotFoundException('Resource not found');
        }

        if ($hydrateMode === Query::HYDRATE_OBJECT && $version !== null) {
            $this->lock($results[0], $version);
        }

        return $results[0];
    }

    /**
     * fetch with addresses
     *
     * @param QueryInterface|int $query the query | the licence id
     *
     * @return mixed
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function fetchWithAddressesUsingId($query)
    {
        $qb = $this->createQueryBuilder();

        if (is_int($query)) {
            $licenceId = $query;
        } else {
            $licenceId = $query->getId();
        }

        $this->buildDefaultQuery($qb, $licenceId)
            ->withContactDetails('correspondenceCd', 'c')
            ->with('c.phoneContacts', 'c_p')
            ->with('c_p.phoneContactType', 'c_p_pct')
            ->withRefdata(PhoneContactEntity::class, 'c_p')
            ->with('organisation', 'o')
            ->withContactDetails('o.contactDetails', 'o_cd')
            ->withContactDetails('establishmentCd', 'e')
            ->withContactDetails('transportConsultantCd', 't')
            ->with('t.phoneContacts', 't_p')
            ->with('t_p.phoneContactType', 't_p_pct')
            ->withRefdata(PhoneContactEntity::class, 't_p');

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * fetch safety details using id
     *
     * @param QueryInterface $query       the query
     * @param int            $hydrateMode hydrate mode
     * @param null|int       $version     the version
     *
     * @return mixed
     * @throws Exception\NotFoundException
     */
    public function fetchSafetyDetailsUsingId($query, $hydrateMode = Query::HYDRATE_OBJECT, $version = null)
    {
        return $this->fetchSafetyDetailsById($query->getId(), $hydrateMode, $version);
    }

    /**
     * fetch safety details by id
     *
     * @param int      $id          the id
     * @param int      $hydrateMode hydrate mode
     * @param null|int $version     version
     *
     * @return mixed
     * @throws Exception\NotFoundException
     * @throws Exception\RuntimeException
     * @throws Exception\VersionConflictException
     */
    public function fetchSafetyDetailsById($id, $hydrateMode = Query::HYDRATE_OBJECT, $version = null)
    {
        $qb = $this->createQueryBuilder();

        $this->buildDefaultQuery($qb, $id)
            ->with('workshops', 'w')
            ->withContactDetails('w.contactDetails');

        $results = $qb->getQuery()->getResult($hydrateMode);

        if (empty($results)) {
            throw new Exception\NotFoundException('Resource not found');
        }

        if ($hydrateMode === Query::HYDRATE_OBJECT && $version !== null) {
            $this->lock($results[0], $version);
        }

        return $results[0];
    }

    /**
     * Get a licence by the licence number
     *
     * @param string $licNo the licence number
     *
     * @return Entity
     * @throws Exception\NotFoundException
     */
    public function fetchByLicNo($licNo)
    {
        $dqb = $this->createQueryBuilder();

        $this->getQueryBuilder()->modifyQuery($dqb)->withRefdata()
            ->with('operatingCentres', 'ocs')
            ->with('ocs.operatingCentre', 'ocs_oc')
            ->with('ocs_oc.address', 'ocs_oc_a');

        $dqb->where($dqb->expr()->eq($this->alias . '.licNo', ':licNo'))
            ->setParameter('licNo', $licNo);

        $results = $dqb->getQuery()->getResult();

        if (empty($results)) {
            throw new Exception\NotFoundException('Resource not found');
        }

        return $results[0];
    }

    /**
     * Returns whether or not the licence number exists in the database
     *
     * @param string $licNo the licence number
     *
     * @return bool
     */
    public function existsByLicNo($licNo)
    {
        $qb = $this->createQueryBuilder();

        $qb->where($qb->expr()->eq($this->alias . '.licNo', ':licNo'));
        $qb->setParameter('licNo', $licNo);
        $qb->setMaxResults(1);

        return count($qb->getQuery()->getResult()) === 1;
    }

    /**
     * Returns whether or not the licence number exists in the database
     *
     * @param string $licNo the licence number
     *
     * @return Entity
     * @throws Exception\NotFoundException
     */
    public function fetchByLicNoWithoutAdditionalData($licNo)
    {
        $qb = $this->createQueryBuilder();

        $qb->where($qb->expr()->eq($this->alias . '.licNo', ':licNo'));
        $qb->setParameter('licNo', $licNo);

        $result = $qb->getQuery()->getOneOrNullResult();

        if ($result === null) {
            throw new Exception\NotFoundException('Licence ' . $licNo . ' not found');
        }

        return $result;
    }

    /**
     * fetch a licence for a user registration
     *
     * @param string $licNo the licence number
     *
     * @return Entity
     * @throws Exception\NotFoundException
     * @throws Exception\ValidationException
     */
    public function fetchForUserRegistration($licNo)
    {
        $licence = $this->fetchByLicNo($licNo);

        // check if it has a correspondence address
        if (
            !($licence->getCorrespondenceCd() instanceof ContactDetailsEntity)
            || !($licence->getCorrespondenceCd()->getAddress() instanceof AddressEntity)
            || $licence->getCorrespondenceCd()->getAddress()->isEmpty()
        ) {
            throw new Exception\ValidationException(
                [
                    'licenceNumber' => [
                        'ERR_ADDRESS_NOT_FOUND'
                    ]
                ]
            );
        }

        // check if the org is not unlicenced
        if ($licence->getOrganisation()->getIsUnlicensed()) {
            throw new Exception\ValidationException(
                [
                    'licenceNumber' => [
                        'ERR_UNLICENCED_ORG'
                    ]
                ]
            );
        }

        // check if the org has any admin users already
        if (!$licence->getOrganisation()->getAdminOrganisationUsers()->isEmpty()) {
            throw new Exception\ValidationException(
                [
                    'licenceNumber' => [
                        'ERR_ADMIN_EXISTS'
                    ]
                ]
            );
        }

        return $licence;
    }

    /**
     * Fetch by vehicle VRM
     *
     * @param string $vrm           the vrm
     * @param bool   $checkByStatus check by status
     *
     * @return mixed
     */
    public function fetchByVrm($vrm, $checkByStatus = false)
    {
        $qb = $this->createQueryBuilder();

        $qb->innerJoin('m.licenceVehicles', 'lv')
            ->innerJoin('lv.vehicle', 'v')
            ->andWhere($qb->expr()->isNull('lv.removalDate'))
            ->andWhere($qb->expr()->eq('v.vrm', ':vrm'))
            ->setParameter('vrm', $vrm);

        if ($checkByStatus) {
            $qb->innerJoin('lv.application', 'a')
                ->andWhere(
                    $qb->expr()->notIn(
                        'a.status',
                        [
                            ApplicationEntity::APPLICATION_STATUS_CANCELLED,
                            ApplicationEntity::APPLICATION_STATUS_REFUSED,
                            ApplicationEntity::APPLICATION_STATUS_WITHDRAWN,
                            ApplicationEntity::APPLICATION_STATUS_NOT_TAKEN_UP
                        ]
                    )
                );
        }

        $query = $qb->getQuery();

        $query->execute();

        return $query->getResult();
    }

    /**
     * Fetch a licence with enforcement area data
     *
     * @param int $licenceId licence id
     *
     * @return mixed
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function fetchWithEnforcementArea($licenceId)
    {
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()->modifyQuery($qb)
            ->with('enforcementArea')
            ->byId($licenceId);

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * Fetch a licence with operating centre data
     *
     * @param int $licenceId licence id
     *
     * @return Entity
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function fetchWithOperatingCentres($licenceId)
    {
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()->modifyQuery($qb)
            ->with('operatingCentres', 'oc')
            ->with('oc.operatingCentre', 'oc_oc')
            ->with('oc_oc.address', 'oc_oc_a')
            ->byId($licenceId);

        return $qb->getQuery()->getSingleResult(Query::HYDRATE_OBJECT);
    }

    /**
     * Get a Licence and PrivateHireLicence data
     *
     * @param int $licenceId licence id
     *
     * @return Entity
     */
    public function fetchWithPrivateHireLicence($licenceId)
    {
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()->modifyQuery($qb)
            ->withRefdata()
            ->with('privateHireLicences', 'phl')
            ->with('phl.contactDetails', 'cd')
            ->with('cd.address', 'add')
            ->with('add.countryCode')
            ->byId($licenceId);

        return $qb->getQuery()->getSingleResult(Query::HYDRATE_OBJECT);
    }

    /**
     * Fetch for continuation not sought
     *
     * @param \DateTime|null $now   current datetime
     * @param int|null       $limit limit for fetching licences
     *
     * @return array
     */
    public function fetchForContinuationNotSought(\DateTime $now = null, $limit = null)
    {
        if (is_null($now)) {
            $now = new DateTime('now');
        }

        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()
            ->modifyQuery($qb)
            ->withRefdata()
            ->with('licenceVehicles', 'lv')
            ->with('lv.goodsDiscs', 'gd')
            ->with('psvDiscs', 'pd')
            ->with('trafficArea', 'ta');

        $qb
            // The query can generate a lot of data which can exceed PHP memory_limit
            // minimize this by only selecting the root entity
            ->select($this->alias, 'ta')
            // the continuation date is in the past;
            ->andWhere($qb->expr()->lt($this->alias . '.expiryDate', ':now'))
            // the status of the licence is valid, valid curtailed or valid suspended;
            ->andWhere($qb->expr()->in($this->alias . '.status', ':statuses'))
            // the licence is a goods licence or a PSV special restricted
            // (i.e. it excludes restricted and standard PSV licences)
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->eq($this->alias . '.goodsOrPsv', ':gv'),
                    $qb->expr()->andX(
                        $qb->expr()->eq($this->alias . '.goodsOrPsv', ':psv'),
                        $qb->expr()->eq($this->alias . '.licenceType', ':sr')
                    )
                )
            )
            // there is an outstanding continuation fee
            ->innerJoin($this->alias . '.fees', 'f')
            ->innerJoin('f.feeType', 'ft')
            ->andWhere($qb->expr()->eq('f.feeStatus', ':feeStatus'))
            ->andWhere($qb->expr()->eq('ft.feeType', ':feeType'))
            ->setParameter('now', $now)
            ->setParameter(
                'statuses',
                [
                    Entity::LICENCE_STATUS_VALID,
                    Entity::LICENCE_STATUS_CURTAILED,
                    Entity::LICENCE_STATUS_SUSPENDED,
                ]
            )
            ->setParameter('feeStatus', FeeEntity::STATUS_OUTSTANDING)
            ->setParameter('feeType', FeeTypeEntity::FEE_TYPE_CONT)
            ->setParameter('gv', Entity::LICENCE_CATEGORY_GOODS_VEHICLE)
            ->setParameter('psv', Entity::LICENCE_CATEGORY_PSV)
            ->setParameter('sr', Entity::LICENCE_TYPE_SPECIAL_RESTRICTED);

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        $query = $qb->getQuery();
        $results = $query->getResult(Query::HYDRATE_ARRAY);
        $licences = [];
        foreach ($results as $result) {
            $licences[] = [
                'id' => $result['id'],
                'version' => $result['version'],
                'licNo' => $result['licNo'],
                'taName' => $result['trafficArea']['name']
            ];
        }
        return $licences;
    }

    /**
     * Fetch PSV licence ids to surrender
     *
     * @param \DateTime|null $now current datetime
     *
     * @return array
     */
    public function fetchPsvLicenceIdsToSurrender(\DateTime $now = null)
    {
        if (is_null($now)) {
            $now = new DateTime('now');
        }

        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()
            ->modifyQuery($qb)
            ->withRefdata();

        $qb
            ->andWhere($qb->expr()->lt($this->alias . '.expiryDate', ':now'))
            ->andWhere($qb->expr()->eq($this->alias . '.goodsOrPsv', ':psv'))
            ->andWhere($qb->expr()->in($this->alias . '.licenceType', ':licTypes'))
            ->andWhere($qb->expr()->in($this->alias . '.status', ':statuses'))
            ->setParameter('now', $now)
            ->setParameter('psv', Entity::LICENCE_CATEGORY_PSV)
            ->setParameter(
                'licTypes',
                [
                    Entity::LICENCE_TYPE_RESTRICTED,
                    Entity::LICENCE_TYPE_STANDARD_NATIONAL,
                    Entity::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                ]
            )
            ->setParameter(
                'statuses',
                [
                    Entity::LICENCE_STATUS_VALID,
                    Entity::LICENCE_STATUS_CURTAILED,
                    Entity::LICENCE_STATUS_SUSPENDED,
                ]
            );

        $query = $qb->getQuery();
        $results = $query->getResult(Query::HYDRATE_ARRAY);
        $licences = array_column($results, 'id');

        return $licences;
    }

    /**
     * Override parent
     *
     * @param QueryBuilder   $qb    query builder
     * @param QueryInterface $query the query
     *
     * @return void
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        if (is_numeric($query->getOrganisation())) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.organisation', ':organisation'))
                ->setParameter('organisation', $query->getOrganisation());
        }

        if (!empty($query->getExcludeStatuses())) {
            $qb->andWhere($qb->expr()->notIn($this->alias . '.status', ':excludeStatuses'))
                ->setParameter('excludeStatuses', $query->getExcludeStatuses());
        }
    }

    /**
     * fetch for continuation
     *
     * @param int    $year        the year
     * @param int    $month       the month
     * @param string $trafficArea the traffic area
     *
     * @return array
     */
    public function fetchForContinuation($year, $month, $trafficArea)
    {
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()->modifyQuery($qb)
            ->withRefdata()
            ->with('trafficArea', 'ta');

        $startDate = new \DateTime($year . '-' . $month . '-01');
        $endDate = new \DateTime($year . '-' . $month . '-01');
        $endDate->modify('last day of this month');

        $qb->andWhere($qb->expr()->gte($this->alias . '.expiryDate', ':expiryFrom'))
            ->setParameter('expiryFrom', $startDate);
        $qb->andWhere($qb->expr()->lte($this->alias . '.expiryDate', ':expiryTo'))
            ->setParameter('expiryTo', $endDate);
        $qb->andWhere($qb->expr()->eq('ta.id', ':trafficArea'))
            ->setParameter('trafficArea', $trafficArea);

        return $qb->getQuery()->getResult(Query::HYDRATE_ARRAY);
    }

    /**
     * fetch with variations and interim in force
     *
     * @param int $licenceId licence id
     *
     * @return array
     */
    public function fetchWithVariationsAndInterimInforce($licenceId)
    {
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()->modifyQuery($qb)
            ->withRefdata()
            ->with('applications', 'a')
            ->with('a.interimStatus', 'ais')
            ->byId($licenceId);
        $qb->andWhere($qb->expr()->eq('a.isVariation', true));
        $qb->andWhere($qb->expr()->eq('a.status', ':applicationStatus'));
        $qb->andWhere($qb->expr()->eq('a.interimStatus', ':interimStatus'));
        $qb->setParameter('applicationStatus', ApplicationEntity::APPLICATION_STATUS_UNDER_CONSIDERATION);
        $qb->setParameter('interimStatus', ApplicationEntity::INTERIM_STATUS_INFORCE);

        return $qb->getQuery()->getResult();
    }

    /**
     * Fetch by organisation id and statuses
     *
     * @param int   $organisationId organisation id
     * @param array $statuses       statuses
     *
     * @return array
     */
    public function fetchByOrganisationIdAndStatuses($organisationId, $statuses)
    {
        /* @var \Doctrine\Orm\QueryBuilder $qb*/
        $qb = $this->createQueryBuilder();

        $qb
            ->andWhere($qb->expr()->eq($this->alias . '.organisation', ':organisationId'))
            ->andWhere($qb->expr()->in($this->alias . '.status', ':statuses'))
            ->setParameter('organisationId', $organisationId)
            ->setParameter('statuses', $statuses);

        return $qb->getQuery()->getResult(Query::HYDRATE_ARRAY);
    }

    /**
     * Fetch by organisation id
     *
     * @param int $organisationId organisation id
     *
     * @return array
     */
    public function fetchByOrganisationId($organisationId)
    {
        /* @var \Doctrine\Orm\QueryBuilder $qb*/
        $qb = $this->createQueryBuilder();

        $qb->andWhere($qb->expr()->eq($this->alias . '.organisation', ':organisationId'))
            ->setParameter('organisationId', $organisationId);

        return $qb->getQuery()->getResult(Query::HYDRATE_ARRAY);
    }

    /**
     * fetch information for the international goods report
     */
    public function internationalGoodsReport(): Result
    {
        return $this->getDbQueryManager()->get(InternationalGoodsReport::class)->execute([]);
    }

    public function fetchForLastTmAutoLetter()
    {
        $this->disableSoftDeleteable(
            [
                TMLicenceEntity::class
            ]
        );
        /* @var \Doctrine\Orm\QueryBuilder $qb*/
        $qb = $this->createQueryBuilder();
        $qb->distinct();

        //  Goods or PSV licence
        $qb->andWhere(
            $qb->expr()
                ->in(
                    $this->alias . '.goodsOrPsv',
                    ':goodsOrPsv'
                )
        );
        $qb->setParameter('goodsOrPsv', [Entity::LICENCE_CATEGORY_GOODS_VEHICLE, Entity::LICENCE_CATEGORY_PSV]);

        //  Valid licence status (Valid, Curtailed, Suspended)
        $qb->andWhere(
            $qb->expr()
                ->in(
                    $this->alias . '.status',
                    ':statuses'
                )
        );
        $qb->setParameter('statuses', [Entity::LICENCE_STATUS_SUSPENDED, Entity::LICENCE_STATUS_VALID, Entity::LICENCE_STATUS_CURTAILED]);

        //  Standard national or standard international licence
        $qb->andWhere(
            $qb->expr()
                ->in(
                    $this->alias . '.licenceType',
                    ':licenceTypes'
                )
        );
        $qb->setParameter('licenceTypes', [Entity::LICENCE_TYPE_STANDARD_NATIONAL, Entity::LICENCE_TYPE_STANDARD_INTERNATIONAL]);

        //  Licence continuation date has not passed (i.e. the licence has not expired) e.g. expiry date >= 1 day ahead of current date time
        $qb->andWhere(
            $qb->expr()
                ->gte(
                    $this->alias . '.expiryDate',
                    ':tomorrow'
                )
        );

        //  Letter has not been sent already for that TM
        $qb->andWhere(
            $qb->expr()->isNull('tml.lastTmLetterDate')
        );

        //  The licence is not marked for oupOut
        $qb->andWhere(
            $qb->expr()->eq($this->alias . '.optOutTmLetter', 0)
        );

        //  Vehicle authority >=1
        $qb->andWhere(
            $qb->expr()->gte($this->alias . '.totAuthVehicles', 1)
        );

        $qb->innerJoin(
            TMLicenceEntity::class,
            'tml',
            Join::WITH,
            $qb->expr()->eq($this->alias . '.id', 'tml.licence')
        );

        /* @var \Doctrine\Orm\QueryBuilder $graceQb */
        $graceQb = $this->getEntityManager()->getRepository(GracePeriodEntity::class)->createQueryBuilder('gp');
        $graceQb->select('IDENTITY(gp.licence)');

        //  And where there is no active period of grace for the licence or on a variation application for the licence
        $graceQb->andWhere(
            $graceQb->expr()->andX(
                $graceQb->expr()->lte(
                    'gp.startDate',
                    ':today'
                ),
                $graceQb->expr()->gte(
                    'gp.endDate',
                    ':today'
                )
            )
        );
        $qb->andWhere(
            $qb->expr()
                ->notIn(
                    $this->alias . '.id',
                    $graceQb->getDQL()
                )
        );

        /* @var \Doctrine\Orm\QueryBuilder $tmlQb */
        $tmlQb = $this->getEntityManager()->getRepository(TMLicenceEntity::class)->createQueryBuilder('tml2');
        $tmlQb->select('IDENTITY(tml2.licence)');

        //  And where there is no active period of grace for the licence or on a variation application for the licence
        $tmlQb->andWhere(
            $tmlQb->expr()->orX(
                $tmlQb->expr()->gte(
                    'tml2.deletedDate',
                    ':tomorrow'
                ),
                $tmlQb->expr()->isNull('tml2.deletedDate')
            )
        );
        $tmlQb->andWhere(
            $tmlQb->expr()->eq('tml2.licence', $this->alias . '.id')
        );
        $qb->andWhere(
            $qb->expr()
                ->notIn(
                    $this->alias . '.id',
                    $tmlQb->getDQL()
                )
        );

        $today = (new DateTime())
            ->setTime(0, 0, 0, 0)
            ->format('Y-m-d');
        $qb->setParameter('today', $today);

        $tomorrow = (new DateTime())
            ->add(new \DateInterval('P1D'))
            ->setTime(0, 0, 0, 0)
            ->format('Y-m-d H:i:s');
        $qb->setParameter('tomorrow', $tomorrow);

        return $qb->getQuery()->getResult();
    }
}
