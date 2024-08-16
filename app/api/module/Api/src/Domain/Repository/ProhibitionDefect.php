<?php

/**
 * ProhibitionDefect Repo
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Prohibition\ProhibitionDefect as ProhibitionDefectEntity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * ProhibitionDefect Repo
 */
class ProhibitionDefect extends AbstractRepository
{
    /**
     * @var ProhibitionDefectEntity
     */
    protected $entity = ProhibitionDefectEntity::class;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        $qb->andWhere($qb->expr()->eq($this->alias . '.prohibition', ':byProhibition'))
            ->setParameter('byProhibition', $query->getProhibition());
    }

    /**
     *
     * @param QueryBuilder $qb
     */
    protected function applyListJoins(QueryBuilder $qb)
    {
        $this->getQueryBuilder()->modifyQuery($qb)
            ->withRefdata()
            ->with('prohibition');
    }
}
