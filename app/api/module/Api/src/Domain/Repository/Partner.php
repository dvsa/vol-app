<?php

/**
 * Partner
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Partner
 */
class Partner extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * @param QueryBuilder $qb
     * @param int $id
     * @return \Dvsa\Olcs\Api\Domain\QueryBuilder
     */
    #[\Override]
    protected function buildDefaultQuery(QueryBuilder $qb, $id)
    {
        // limit by contact type
        $qb->andWhere($qb->expr()->eq($this->alias . '.contactType', ':contactType'));
        $qb->setParameter('contactType', Entity::CONTACT_TYPE_PARTNER);

        return $this->getQueryBuilder()->modifyQuery($qb)->withRefdata()->byId($id);
    }

    /**
     * @param QueryBuilder $qb
     * @param QueryInterface $query
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        $qb->andWhere($qb->expr()->eq($this->alias . '.contactType', ':contactType'));
        $qb->setParameter('contactType', Entity::CONTACT_TYPE_PARTNER);
    }
}
