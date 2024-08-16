<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail as Entity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Query;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * ContinuationDetail
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ContinuationDetail extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Fetch ContinuationDetail (used for markers) for a licence
     *
     * @param int $licenceId Licence ID
     *
     * @return array of ContinuationDetail
     */
    public function fetchForLicence($licenceId)
    {
        /* @var \Doctrine\Orm\QueryBuilder $qb */
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()
            ->modifyQuery($qb)
            ->withRefdata()
            ->with('licence', 'l')
            ->with('continuation', 'c');

        // where licence is
        $qb->andWhere($qb->expr()->eq($this->alias . '.licence', ':licence'))
            ->setParameter('licence', $licenceId);

        // AND licence status is 'Valid', 'Curtailed' or 'Suspended'
        $qb->andWhere($qb->expr()->in('l.status', ':licenceStatuses'))
            ->setParameter(
                'licenceStatuses',
                [
                    LicenceEntity::LICENCE_STATUS_VALID,
                    LicenceEntity::LICENCE_STATUS_CURTAILED,
                    LicenceEntity::LICENCE_STATUS_SUSPENDED,
                ]
            );

        // AND continuation data is within 4 years of current date
        $dateTime = new DateTime();
        $year = $dateTime->format('Y');
        $month = $dateTime->format('n');

        $dateTime->modify('+4 years');
        $futureYear = $dateTime->format('Y');
        $futureMonth = $month;

        $pastDateTime = (new DateTime())->modify('-4 years');
        $pastYear = $pastDateTime->format('Y');
        $pastMonth = $pastDateTime->format('n');

        $qb->andWhere(
            // 4 years in the future
            '(c.month >= :month AND c.year = :year) OR '
            . '(c.year > :year AND c.year < :futureYear) OR '
            . '(c.month <= :futureMonth AND c.year = :futureYear) OR '
            // 4 years in the past
            . '(c.month <= :month AND c.year = :year) OR '
            . '(c.year > :pastYear AND c.year < :year) OR '
            . '(c.month >= :pastMonth AND c.year = :pastYear)'
        )
            ->setParameter('month', $month)
            ->setParameter('year', $year)
            ->setParameter('futureMonth', $futureMonth)
            ->setParameter('futureYear', $futureYear)
            ->setParameter('pastMonth', $pastMonth)
            ->setParameter('pastYear', $pastYear);

        // AND continuation status is Printed, Acceptable or Unacceptable
        // OR where the status is Complete but the checklist has not yet been received;
        $qb->andWhere(
            "{$this->alias}.status IN (:continuationDetailStatuses)"
        )->setParameter(
            'continuationDetailStatuses',
            [
                Entity::STATUS_PRINTED,
                Entity::STATUS_ACCEPTABLE,
                Entity::STATUS_UNACCEPTABLE
            ]
        );

        return $qb->getQuery()->getResult();
    }

    /**
     * Get ongoing continuation detail for a licence
     *
     * @param int $licenceId Licence ID
     *
     * @return Entity
     * @throws \Doctrine\ORM\NoResultException if not found
     */
    public function fetchOngoingForLicence($licenceId)
    {
        /* @var \Doctrine\Orm\QueryBuilder $qb */
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()
            ->modifyQuery($qb)
            ->withRefdata()
            ->with('continuation', 'c');

        // where licence is
        $qb->andWhere($qb->expr()->eq($this->alias . '.licence', ':licence'))
            ->setParameter('licence', $licenceId);
        $qb->andWhere(
            $qb->expr()->orX(
                // and status is Acceptable
                $qb->expr()->eq($this->alias . '.status', ':status'),
                // or status is not Complete and isDigital flag = 1
                $qb->expr()->andX(
                    $qb->expr()->neq($this->alias . '.status', ':notStatus'),
                    $qb->expr()->eq($this->alias . '.isDigital', 1)
                )
            )
        )
        ->setParameter('status', Entity::STATUS_ACCEPTABLE)
        ->setParameter('notStatus', Entity::STATUS_COMPLETE);

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * Fetch a list of continuation details for that are require reminders
     *
     * @param array $trafficAreas   List of traffic area ids
     * @param int   $month          Month of the continuation
     * @param int   $year           Year of the continuation
     * @param array $ids            List of continuation details IDs
     *
     * @return ArrayCollection of ContinuationDetail
     */
    public function fetchChecklistReminders(array $trafficAreas, $month, $year, array $ids = [])
    {
        /* @var \Doctrine\Orm\QueryBuilder $qb */
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()
            ->modifyQuery($qb)
            ->withRefdata();

        $qb
            ->select(
                $this->alias .
                ', partial l.{id, licNo}' .
                ', partial lgp.{id}' .
                ', partial lo.{id, name, allowEmail}' .
                ', partial ls.{id, description}' .
                ', partial lf.{id, feeType, feeStatus}' .
                ', partial lfft.{id}' .
                ', partial lfftft.{id}' .
                ', partial lffs.{id}'
            )
            ->innerJoin($this->alias . '.continuation', 'c')
            ->innerJoin($this->alias . '.licence', 'l')
            ->leftJoin('l.status', 'ls')
            ->leftJoin('l.goodsOrPsv', 'lgp')
            ->leftJoin('l.organisation', 'lo')
            ->leftJoin('l.fees', 'lf')
            ->leftJoin('lf.feeType', 'lfft')
            ->leftJoin('lfft.feeType', 'lfftft')
            ->leftJoin('lf.feeStatus', 'lffs');

        $qb->andWhere($qb->expr()->in('l.status', ':licenceStatuses'))
            ->setParameter(
                'licenceStatuses',
                [
                    LicenceEntity::LICENCE_STATUS_VALID,
                    LicenceEntity::LICENCE_STATUS_CURTAILED,
                    LicenceEntity::LICENCE_STATUS_SUSPENDED,
                ]
            );

        $qb->andWhere($qb->expr()->eq($this->alias . '.received', 0));
        $qb->andWhere($qb->expr()->eq($this->alias . '.isDigital', 0));

        if ($ids) {
            $this->getQueryBuilder()
                ->modifyQuery($qb)
                ->filterByIds($ids);
        }
        if ($month) {
            $qb->andWhere($qb->expr()->eq('c.month', ':month'))
                ->setParameter('month', $month);
        }
        if ($year) {
            $qb->andWhere($qb->expr()->eq('c.year', ':year'))
                ->setParameter('year', $year);
        }

        $qb->andWhere($qb->expr()->in('l.trafficArea', ':trafficAreas'));
        $qb->setParameter('trafficAreas', $trafficAreas);

        //  check continuation details status
        $qb->andWhere(
            $qb->expr()->neq($this->alias . '.status', ':status')
        )
        ->setParameter('status', Entity::STATUS_PREPARED);

        return $this->filterByFee(
            $qb->getQuery()->getResult(\Doctrine\ORM\Query::HYDRATE_OBJECT),
            FeeTypeEntity::FEE_TYPE_CONT,
            [FeeEntity::STATUS_OUTSTANDING]
        );
    }

    /**
     * Filter by fee
     *
     * @param array  $entities    Array of continuation detail entities
     * @param string $feeType     Fee type to filter by (exclude)
     * @param array  $feeStatuses Fee statuses to filter by (exclude)
     *
     * @return ArrayCollection
     */
    protected function filterByFee($entities, $feeType, $feeStatuses)
    {
        $filtered = new ArrayCollection();
        foreach ($entities as $entity) {
            $fees = $entity->getLicence()->getFees();
            if (count($fees)) {
                foreach ($fees as $fee) {
                    if (
                        $fee->getFeeType()->getFeeType()->getId() === $feeType
                        && in_array($fee->getFeeStatus()->getId(), $feeStatuses, true) !== false
                    ) {
                        continue 2;
                    }
                }
            }
            $filtered->add($entity);
        }

        return $filtered;
    }

    /**
     * Fetch Continuation Detail details
     *
     * @param int    $continuationId  Continuation ID
     * @param array  $licenceStatuses List of licence statuses
     * @param string $licNo           Licence no
     * @param string $method          Organisation receiving preference Entity::METHOD_EMAIL or Entity::METHOD_POST
     * @param string $status          Continuation detail status
     *
     * @return array of Entity
     */
    public function fetchDetails($continuationId, $licenceStatuses, $licNo, $method, $status)
    {
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()
            ->modifyQuery($qb)
            ->withRefdata()
            ->with('continuation', 'c')
            ->with('status', 's')
            ->with('licence', 'l')
            ->with('l.status', 'ls')
            ->with('l.organisation', 'lo')
            ->with('l.licenceType', 'lt')
            ->with('l.goodsOrPsv', 'lg');

        $qb->orderBy('l.licNo', 'ASC');

        if ($continuationId) {
            $qb->andWhere($qb->expr()->eq('c.id', ':continuationId'))
                ->setParameter('continuationId', $continuationId);
        }
        if ($licenceStatuses) {
            $qb->andWhere($qb->expr()->in('l.status', ':licenceStatuses'))
                ->setParameter('licenceStatuses', $licenceStatuses);
        }
        if ($licNo) {
            $qb->andWhere($qb->expr()->eq('l.licNo', ':licNo'))
                ->setParameter('licNo', $licNo);
        }
        if ($method) {
            if ($method === Entity::METHOD_EMAIL) {
                $qb->andWhere($qb->expr()->eq('lo.allowEmail', 1));
            } elseif ($method === Entity::METHOD_POST) {
                $qb->andWhere($qb->expr()->eq('lo.allowEmail', 0));
            }
        }
        if ($status) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.status', ':status'))
                ->setParameter('status', $status);
        }

        return $qb->getQuery()->getResult(Query::HYDRATE_OBJECT);
    }

    /**
     * Fetch Continuation Detail with Licence data
     *
     * @param int $id Continuation Detail ID
     *
     * @return Entity
     */
    public function fetchWithLicence($id)
    {
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()
            ->modifyQuery($qb)
            ->withRefdata()
            ->with('status', 's')
            ->with('licence', 'l')
            ->with('l.licenceType', 'lt')
            ->with('l.goodsOrPsv', 'lg')
            ->byId($id);

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * Fetch licence ids where continuation details exists for given continuation
     *
     * @param int   $continuationId Continuation ID
     * @param array $licenceIds     Licence IDs
     *
     * @return array Licence IDs
     */
    public function fetchLicenceIdsForContinuationAndLicences($continuationId, $licenceIds)
    {
        /* @var $qb \Doctrine\Orm\QueryBuilder */
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()
            ->modifyQuery($qb)
            ->with('licence', 'l')
            ->withRefdata();

        // where licence is
        $qb->andWhere($qb->expr()->in($this->alias . '.licence', ':licences'))
            ->setParameter('licences', $licenceIds);
        // and continunation is
        $qb->andWhere($qb->expr()->eq($this->alias . '.continuation', ':continuation'))
            ->setParameter('continuation', $continuationId);

        $result = $qb->getQuery()->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
        $licenceIds = [];

        foreach ($result as $res) {
            $licenceIds[] = $res['licence']['id'];
        }

        return $licenceIds;
    }

    /**
     * Create continuation details
     *
     * @param array  $licenceIds     Licence IDs to create Continuation Details for
     * @param bool   $received       Received fag for created Continuation Details
     * @param string $status         Status for created Continuation Details
     * @param int    $continuationId Continuation ID
     *
     * @return int Number on Continuation Details created
     */
    public function createContinuationDetails($licenceIds, $received, $status, $continuationId)
    {
        return $this->getDbQueryManager()->get('Continuations\CreateContinuationDetails')
            ->executeInsert($licenceIds, $received, $status, $continuationId);
    }

    /**
     * Fetch a list of digital continuation details, which have not yet be completed and should be sent a
     * postal copy of the form
     *
     * @param int $numOfDays The number of days before the licence expires
     *
     * @return array of ContinuationDetail
     */
    public function fetchListForDigitalReminders($numOfDays)
    {
        /* @var \Doctrine\Orm\QueryBuilder $qb */
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()
            ->modifyQuery($qb)
            ->withRefdata()
            ->with('continuation', 'c')
            ->with('licence', 'l');

        // Licence status = Valid, curtailed or suspended; AND
        $qb->andWhere(
            $qb->expr()->in(
                'l.status',
                [
                    LicenceEntity::LICENCE_STATUS_VALID,
                    LicenceEntity::LICENCE_STATUS_CURTAILED,
                    LicenceEntity::LICENCE_STATUS_SUSPENDED
                ]
            )
        );

        // Continuation date is x days or less away but is not in the past; AND
        $interval = new \DateInterval('P' . $numOfDays . 'D');
        $qb->andWhere($qb->expr()->gte('l.expiryDate', ':NOW'))
            ->setParameter('NOW', (new DateTime())->format('Y-m-d'));
        $qb->andWhere($qb->expr()->lte('l.expiryDate', ':maxExpiryDate'))
            ->setParameter('maxExpiryDate', (new DateTime())->add($interval)->format('Y-m-d'));

        // Continuation status is not 'Complete'; AND
        // The month and year of the associated continuation_detail and continuation records should match the month
        // and year of licence.continuation_date
        $qb->andWhere($qb->expr()->notIn($this->alias . '.status', [Entity::STATUS_COMPLETE]));
        $qb->andWhere($qb->expr()->eq('c.month', 'MONTH(l.expiryDate)'));
        $qb->andWhere($qb->expr()->eq('c.year', 'YEAR(l.expiryDate)'));

        // An email has been sent
        $qb->andWhere($qb->expr()->eq($this->alias . '.digitalNotificationSent', '1'));

        // A reminder has not already been sent
        $qb->andWhere($qb->expr()->eq($this->alias . '.digitalReminderSent', '0'));

        return $qb->getQuery()->getResult();
    }
}
