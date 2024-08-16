<?php

/**
 * TmCaseDecision
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Exception;
use Laminas\Stdlib\ArraySerializableInterface as QryCmd;
use Dvsa\Olcs\Api\Entity\Tm\TmCaseDecision as Entity;

/**
 * TmCaseDecision
 */
class TmCaseDecision extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Fetch latest decision for a case
     *
     * @param QryCmd $query       Query DTO
     * @param int    $hydrateMode Hydrate home Query::HYDRATE_* constant
     *
     * @return \Dvsa\Olcs\Api\Entity\Tm\TmCaseDecision|false
     */
    public function fetchLatestUsingCase(QryCmd $query, $hydrateMode = Query::HYDRATE_OBJECT)
    {
        /* @var \Doctrine\Orm\QueryBuilder $qb*/
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()->modifyQuery($qb)->withRefdata();

        $qb->andWhere($qb->expr()->eq($this->alias . '.case', ':byCase'))
            ->setParameter('byCase', $query->getCase());

        // there should be only one active but just in case order by id to get the latest
        $qb->orderBy($this->alias . '.id', 'DESC');

        $results = $qb->getQuery()->getResult($hydrateMode);

        if (empty($results)) {
            return false;
        }

        return $results[0];
    }
}
