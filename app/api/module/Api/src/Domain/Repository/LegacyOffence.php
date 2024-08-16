<?php

/**
 * LegacyOffence
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Exception;
use Laminas\Stdlib\ArraySerializableInterface as QryCmd;
use Dvsa\Olcs\Api\Entity\Legacy\LegacyOffence as Entity;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * LegacyOffence
 */
class LegacyOffence extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Fetch the default record by it's id
     *
     * @param Query|QryCmd $query
     * @param int $hydrateMode

     * @return mixed
     * @throws Exception\NotFoundException
     * @throws Exception\VersionConflictException
     */
    public function fetchCaseLegacyOffenceUsingId(QryCmd $query, $hydrateMode = Query::HYDRATE_OBJECT)
    {
        /* @var \Doctrine\Orm\QueryBuilder $qb*/
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()->modifyQuery($qb)
            ->withRefdata()
            ->with('case')
            ->with('createdBy')
            ->with('lastModifiedBy')
            ->byId($query->getId());

        $qb->andWhere($qb->expr()->eq($this->alias . '.case', ':byCase'))
            ->setParameter('byCase', $query->getCase());

        $result = $qb->getQuery()->getResult($hydrateMode);
        return $result[0];
    }

    /**
     *
     * @param QueryBuilder $qb
     * @param QueryInterface $query
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        $qb->andWhere($qb->expr()->eq($this->alias . '.case', ':byCase'))
            ->setParameter('byCase', $query->getCase());
    }
}
