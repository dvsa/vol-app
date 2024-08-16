<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Pi\PiDefinition as Entity;

/**
 * PiDefinition
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class PiDefinition extends AbstractRepository
{
    protected $entity = Entity::class;

    protected function applyListFilters(\Doctrine\ORM\QueryBuilder $qb, \Dvsa\Olcs\Transfer\Query\QueryInterface $query)
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
