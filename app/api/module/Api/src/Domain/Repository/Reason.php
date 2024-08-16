<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Pi\Reason as Entity;

/**
 * Reason
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class Reason extends AbstractRepository
{
    protected $entity = Entity::class;

    protected function applyListFilters(\Doctrine\ORM\QueryBuilder $qb, \Dvsa\Olcs\Transfer\Query\QueryInterface $query)
    {
        if (method_exists($query, 'getIsNi') && !empty($query->getIsNi())) {
            $qb->andWhere($qb->expr()->eq('m.isNi', ':isNi'))
                ->setParameter('isNi', $query->getIsNi() === 'Y');
        }

        if (method_exists($query, 'getGoodsOrPsv') && !empty($query->getGoodsOrPsv())) {
            // goodsOrPsv has the string value NULL to ensure the filter is applied (and not considered empty)
            // This is for TM reasons where we wish to remove all reasons that apply to goods and psv licences
            if ($query->getGoodsOrPsv() === 'NULL') {
                $qb->andWhere($qb->expr()->isNull('m.goodsOrPsv'));
            } else {
                $qb->andWhere($qb->expr()->eq('m.goodsOrPsv', ':goodsOrPsv'))
                    ->setParameter('goodsOrPsv', $query->getGoodsOrPsv());
            }
        }

        if (method_exists($query, 'getIsProposeToRevoke') && !empty($query->getIsProposeToRevoke())) {
            $qb->andWhere($qb->expr()->eq('m.isProposeToRevoke', ':isProposeToRevoke'))
                ->setParameter('isProposeToRevoke', $query->getIsProposeToRevoke() === 'Y');
        }
    }
}
