<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity\Organisation\CorrespondenceInbox as Entity;
use Dvsa\Olcs\Transfer\Query\Correspondence\Correspondences;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Class Correspondence
 *
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */
class Correspondence extends AbstractRepository
{
    protected $entity = Entity::class;

    protected $alias = 'co';

    /**
     * Apply joins
     *
     * @param QueryBuilder $qb Query Builder
     *
     * @return void
     */
    protected function applyListJoins(QueryBuilder $qb)
    {
        $qb
            ->addSelect('l, d')
            ->join($this->alias . '.licence', 'l')
            ->join($this->alias . '.document', 'd');
    }

    /**
     * Apply filters
     *
     * @param QueryBuilder    $qb    Query builder
     * @param Correspondences $query Http query
     *
     * @return void
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        $qb
            ->where($qb->expr()->eq('l.organisation', ':ORG_ID'))
            ->setParameter('ORG_ID', $query->getOrganisation());
    }
}
