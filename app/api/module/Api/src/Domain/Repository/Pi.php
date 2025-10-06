<?php

/**
 * Cases
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Exception;
use Laminas\Stdlib\ArraySerializableInterface as QryCmd;
use Dvsa\Olcs\Api\Entity\Pi\Pi as Entity;

/**
 * Pi
 */
class Pi extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Fetch the pi for a case id
     *
     * @param Query|QryCmd $query
     * @param int $hydrateMode
     * @return mixed
     * @throws Exception\NotFoundException
     * @throws Exception\VersionConflictException
     */
    public function fetchUsingCase(QryCmd $query, $hydrateMode = Query::HYDRATE_OBJECT)
    {
        /* @var \Doctrine\Orm\QueryBuilder $qb*/
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()->modifyQuery($qb)
            ->withRefdata()
            ->with('agreedByTc')
            ->with('assignedTo')
            ->with('decidedByTc')
            ->with('reasons')
            ->with('decisions')
            ->with('tmDecisions')
            ->with('piHearings')
            ->with('case', 'c')
            ->with('c.transportManager')
            ->with('piSlaExceptions', 's')
            ->withCreatedBy('createdBy', 's');

        $qb->andWhere($qb->expr()->eq($this->alias . '.case', ':byId'))
            ->setParameter('byId', $query->getId());

        $pi = $qb->getQuery()->getResult($hydrateMode);

        return ($pi[0] ?? null);
    }
}
