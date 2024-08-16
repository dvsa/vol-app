<?php

/**
 * Publication
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\Publication\Publication as Entity;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Publication
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Publication extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Fetch latest publications of a given type for a given traffic area
     *
     * @param mixed $trafficArea the traffic area
     * @param mixed $pubType     the publication type
     *
     * @return mixed
     *
     * @throws NotFoundException
     */
    public function fetchLatestForTrafficAreaAndType(mixed $trafficArea, mixed $pubType)
    {
        $qb = $this->createQueryBuilder();

        $qb->andWhere(
            $qb->expr()->eq($this->alias . '.trafficArea', ':trafficArea')
        )->setParameter('trafficArea', $trafficArea);

        $qb->andWhere(
            $qb->expr()->eq($this->alias . '.pubType', ':pubType')
        )->setParameter('pubType', $pubType);

        $qb->andWhere(
            $qb->expr()->eq($this->alias . '.pubStatus', ':pubStatus')
        )->setParameter('pubStatus', Entity::PUB_NEW_STATUS);

        $this->getQueryBuilder()->modifyQuery($qb);

        $result = $qb->getQuery()->getResult(Query::HYDRATE_OBJECT);

        if (empty($result)) {
            throw new NotFoundException('Resource not found');
        }

        return $result[0];
    }

    /**
     * Fetch Pending Publications
     *
     * @param QueryInterface $query query object
     *
     * @return array
     */
    public function fetchPendingList(QueryInterface $query)
    {
        $qb = $this->createQueryBuilder();

        $qb->andWhere(
            $qb->expr()->in($this->alias . '.pubStatus', ':pubStatus')
        )->setParameter('pubStatus', [Entity::PUB_NEW_STATUS, Entity::PUB_GENERATED_STATUS]);

        $this->buildDefaultListQuery($qb, $query);

        $this->getQueryBuilder()->modifyQuery($qb);

        return [
            'results' => $qb->getQuery()->getResult(),
            'count' => $this->fetchPaginatedCount($qb)
        ];
    }

    /**
     * Fetch Published Publications
     *
     * @param QueryInterface $query         query object
     * @param string|null    $pubType       pubType
     * @param string         $pubDateFrom   pubDateFrom (inclusive)
     * @param string         $pubDateTo     pubDateTo (not inclusive)
     * @param string|int     $trafficAreaId traffic area id
     *
     * @return array
     */
    public function fetchPublishedList(QueryInterface $query, $pubType, $pubDateFrom, $pubDateTo, $trafficAreaId)
    {
        $qb = $this->createQueryBuilder();

        $qb->andWhere($qb->expr()->eq($this->alias . '.pubStatus', ':pubStatus'))
            ->setParameter('pubStatus', Entity::PUB_PRINTED_STATUS);

        $qb->andWhere($qb->expr()->gte($this->alias . '.pubDate', ':pubDateFrom'))
            ->setParameter('pubDateFrom', $pubDateFrom);

        $qb->andWhere($qb->expr()->lt($this->alias . '.pubDate', ':pubDateTo'))
            ->setParameter('pubDateTo', $pubDateTo);

        if ($pubType) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.pubType', ':pubType'))
                ->setParameter('pubType', $pubType);
        }

        if ($trafficAreaId) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.trafficArea', ':trafficArea'))
                ->setParameter('trafficArea', $trafficAreaId);
        }

        $this->buildDefaultListQuery($qb, $query);

        $this->getQueryBuilder()->modifyQuery($qb);

        return [
            'results' => $qb->getQuery()->getResult(),
            'count' => $this->fetchPaginatedCount($qb)
        ];
    }
}
