<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity\Pi\Decision as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Decision
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class Decision extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Apply list filters
     *
     * @param QueryBuilder   $qb    Query builder
     * @param QueryInterface $query Query
     *
     * @return void
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        if (method_exists($query, 'getIsNi') && !empty($query->getIsNi())) {
            $qb->andWhere($qb->expr()->eq('m.isNi', ':isNi'))
                ->setParameter('isNi', $query->getIsNi() === 'Y');
        }

        if (method_exists($query, 'getGoodsOrPsv') && !empty($query->getGoodsOrPsv())) {
            if ($query->getGoodsOrPsv() === 'NULL') {
                $qb->andWhere($qb->expr()->isNull('m.goodsOrPsv'));
            } else {
                $qb->andWhere($qb->expr()->eq('m.goodsOrPsv', ':goodsOrPsv'))
                    ->setParameter('goodsOrPsv', $query->getGoodsOrPsv());
            }
        }
    }
}
