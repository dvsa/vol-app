<?php

/**
 * Abstract Read Audit
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Repository\ReadAudit\ReadAuditRepositoryInterface;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Abstract Read Audit
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractReadAudit extends AbstractRepository implements ReadAuditRepositoryInterface
{
    protected $entityProperty;

    /**
     * @inheritdoc
     */
    public function deleteOlderThan($oldestDate)
    {
        $query = $this->getEntityManager()->createQuery(
            'DELETE FROM ' . $this->entity . ' e WHERE e.createdOn <= :oldestDate'
        );

        $query->setParameter('oldestDate', $oldestDate);

        return $query->execute();
    }

    /**
     * Returns one or more record for specified user, object and date
     */
    public function fetchOneOrMore($userId, $entityId, \DateTime $date)
    {
        $qb = $this->createQueryBuilder();

        $dateTo = clone $date;

        $qb->andWhere($qb->expr()->eq($this->alias . '.user', ':user'));
        $qb->andWhere($qb->expr()->eq($this->alias . '.' . $this->entityProperty, ':entityId'));
        $qb->andWhere($qb->expr()->gte($this->alias . '.createdOn', ':dateFrom'));
        $qb->andWhere($qb->expr()->lte($this->alias . '.createdOn', ':dateTo'));

        $qb->setParameter('user', $userId);
        $qb->setParameter('entityId', $entityId);
        $qb->setParameter('dateFrom', $date->setTime(0, 0, 0));
        $qb->setParameter('dateTo', $dateTo->setTime(23, 59, 59));

        return $qb->getQuery()->getResult();
    }

    /**
     * Override to add additional data to the default fetchList() method
     * @param QueryBuilder $qb
     * @inheritdoc
     */
    protected function applyListJoins(QueryBuilder $qb)
    {
        $qb->innerJoin($this->alias . '.user', 'u');
        $qb->innerJoin('u.contactDetails', 'cd');
        $qb->innerJoin('cd.person', 'p');
    }

    /**
     * Applies filters
     * @param QueryBuilder $qb
     * @param QueryInterface $query
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        $qb->andWhere($qb->expr()->eq($this->alias . '.' . $this->entityProperty, ':byEntity'))
            ->setParameter('byEntity', $query->getId())
            ->orderBy($this->alias . '.createdOn', 'DESC');
    }
}
