<?php

/**
 * TmQualification
 *
 * @author Alex Peshkov  <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Entity\Tm\TmQualification as Entity;
use Dvsa\Olcs\Transfer\Query\TmQualification\TmQualificationsList;

/**
 * TmQualification
 *
 * @author Alex Peshkov  <alex.peshkov@valtech.co.uk>
 */
class TmQualification extends AbstractRepository
{
    protected $entity = Entity::class;
    protected $alias = 'tq';

    /**
     * Add list filters
     *
     * @param QueryBuilder $qb
     * @param TmQualificationsList $query
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        $qb->andWhere($qb->expr()->eq($this->alias . '.transportManager', ':transportManager'));
        $qb->setParameter('transportManager', $query->getTransportManager());
    }

    /**
     * Add joins
     *
     * @param \Doctrine\ORM\QueryBuilder $qb
     */
    protected function applyListJoins(\Doctrine\ORM\QueryBuilder $qb)
    {
        $this->getQueryBuilder()
            ->with('countryCode', 'cc')
            ->with('qualificationType', 'qt');

        $qb->orderBy('qt.displayOrder', 'ASC');
    }
}
