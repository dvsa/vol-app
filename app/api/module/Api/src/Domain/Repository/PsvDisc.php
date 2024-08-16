<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Licence\PsvDisc as Entity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;

/**
 * Psv Disc
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PsvDisc extends AbstractRepository
{
    protected $entity = Entity::class;

    protected $alias = 'psv';

    /**
     * Fetch discs to print
     *
     * @param int $licenceType licence type
     * @param int|null $maxResults
     *
     * @return array
     */
    public function fetchDiscsToPrint($licenceType, $maxResults)
    {
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()
            ->modifyQuery($qb)
            ->withRefdata()
            ->with('licence', 'l')
            ->with('l.trafficArea', 'lta')
            ->with('l.licenceType', 'llt')
            ->with('l.goodsOrPsv', 'lgp')
            ->order('l.licNo', 'ASC');

        $qb->setMaxResults($maxResults);

        $this->addFilteringConditions($qb, $licenceType);

        return $qb->getQuery()->getResult(Query::HYDRATE_ARRAY);
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param string $licenceType
     */
    protected function addFilteringConditions($qb, $licenceType)
    {
        $activeStatuses = [
            LicenceEntity::LICENCE_STATUS_UNDER_CONSIDERATION,
            LicenceEntity::LICENCE_STATUS_GRANTED,
            LicenceEntity::LICENCE_STATUS_VALID,
            LicenceEntity::LICENCE_STATUS_CURTAILED,
            LicenceEntity::LICENCE_STATUS_SUSPENDED
        ];
        $qb->andWhere(
            $qb->expr()->andX(
                $qb->expr()->eq('lta.isNi', 0),
                $qb->expr()->eq('llt.id', ':licenceType'),
                $qb->expr()->neq('lta.id', ':licenceTrafficAreaId'),
                $qb->expr()->eq('lgp.id', ':goodsOrPsv')
            )
        );
        $qb->andWhere($qb->expr()->isNull('psv.issuedDate'));
        $qb->andWhere($qb->expr()->isNull('psv.ceasedDate'));
        $qb->setParameter('licenceType', $licenceType);
        $qb->setParameter('licenceTrafficAreaId', TrafficAreaEntity::NORTHERN_IRELAND_TRAFFIC_AREA_CODE);
        $qb->setParameter('goodsOrPsv', LicenceEntity::LICENCE_CATEGORY_PSV);

        $qb->andWhere($qb->expr()->in('l.status', ':activeStatuses'));
        $qb->setParameter('activeStatuses', $activeStatuses);
    }

    public function setIsPrintingOn($discIds)
    {
        $this->setIsPrinting(1, $discIds);
    }

    public function setIsPrintingOff($discIds)
    {
        $this->setIsPrinting(0, $discIds);
    }

    protected function setIsPrinting($type, $discIds)
    {
        return $this->getDbQueryManager()->get('Discs\PsvDiscsSetIsPrinting')
            ->execute(
                ['isPrinting' => $type, 'ids' => $discIds],
                ['isPrinting' => \PDO::PARAM_INT, 'ids' => Connection::PARAM_INT_ARRAY]
            );
    }

    /**
     * Issue the disc and assign the diesc number
     *
     * @param array $discIds     List of disc Ids to issue
     * @param int   $startNumber The starting disc number
     *
     * @return void
     */
    public function setIsPrintingOffAndAssignNumbers($discIds, $startNumber)
    {
        $discNo = $startNumber;
        foreach ($discIds as $discId) {
            $this->getDbQueryManager()->get('Discs\PsvDiscsSetIsPrintingOffAndDiscNo')
                ->execute(['id' => $discId, 'discNo' => $discNo]);

            $discNo++;
        }
    }

    protected function applyListFilters(\Doctrine\ORM\QueryBuilder $qb, \Dvsa\Olcs\Transfer\Query\QueryInterface $query)
    {
        if (method_exists($query, 'getIncludeCeased')) {
            if ($query->getIncludeCeased() === false) {
                $qb->andWhere($qb->expr()->isNull($this->alias . '.ceasedDate'));
            }
        }

        $qb->andWhere($qb->expr()->eq($this->alias . '.licence', ':licence'))
            ->setParameter('licence', $query->getId())
            ->addSelect($this->alias . '.discNo+0 as HIDDEN intDiscNo')
            ->orderBy('intDiscNo', 'ASC');
    }

    /**
     * Cease all PSV discs attached to a licence
     *
     * @param int $licenceId
     *
     * @return int number of discs ceased
     */
    public function ceaseDiscsForLicence($licenceId)
    {
        return $this->getDbQueryManager()->get('Discs\CeaseDiscsForLicence')
            ->execute(['licence' => $licenceId])
            ->rowCount();
    }

    public function fetchDiscsToPrintMin($licenceType)
    {
        $qb = $this->createQueryBuilder();

        $qb->leftJoin('psv.licence', 'l')
            ->leftJoin('l.trafficArea', 'lta')
            ->leftJoin('l.licenceType', 'llt')
            ->leftJoin('l.goodsOrPsv', 'lgp');

        $this->addFilteringConditions($qb, $licenceType);

        return $qb->getQuery()->getResult(Query::HYDRATE_ARRAY);
    }

    /**
     * Create PSV discs
     *
     * @param int  $licenceId
     * @param int  $howMany   How many to create
     * @param bool $isCopy    Set created discs as copies
     *
     * @return int number of discs created
     */
    public function createPsvDiscs($licenceId, $howMany, $isCopy = false)
    {
        /** @var \Dvsa\Olcs\Api\Domain\Repository\Query\Discs\CreatePsvDiscs $rawQuery */
        $rawQuery = $this->getDbQueryManager()->get('Discs\CreatePsvDiscs');

        return $rawQuery->executeInsert($licenceId, $howMany, $isCopy);
    }

    public function countForLicence($id)
    {
        $qb = $this->createQueryBuilder();

        $qb->select('count(psv)')
            ->where($qb->expr()->eq($this->alias . '.licence', ':id'))
            ->andWhere($qb->expr()->isNull($this->alias . '.ceasedDate'))
            ->groupBy('psv.licence')
            ->setParameter('id', $id)
            ->setMaxResults(1);

        try {
            $count = $qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException) {
            $count = 0;
        } catch (\Exception $exception) {
            throw $exception;
        }
        $discCount = (int) $count ?? 0;
        return ['discCount' => $discCount];
    }
}
