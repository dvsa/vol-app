<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\Cases\ProposeToRevoke as Entity;
use Laminas\Stdlib\ArraySerializableInterface as QryCmd;

/**
 * ProposeToRevoke
 */
class ProposeToRevoke extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * FetchProposeToRevokeUsingCase
     *
     * @param \Dvsa\Olcs\Transfer\Query\Cases\ProposeToRevoke\ProposeToRevokeByCase $query       Query
     * @param int                                                                   $hydrateMode Histration mode
     *
     * @return null|Entity
     */
    public function fetchProposeToRevokeUsingCase(QryCmd $query, $hydrateMode = Query::HYDRATE_OBJECT)
    {
        /* @var \Doctrine\Orm\QueryBuilder $qb*/
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()->modifyQuery($qb);

        $qb->andWhere($qb->expr()->eq($this->alias . '.case', ':byCase'))
            ->setParameter('byCase', $query->getCase());

        return $qb->getQuery()->getOneOrNullResult($hydrateMode);
    }
}
