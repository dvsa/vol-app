<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\System\DiscSequence as Entity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;

/**
 * Disc Sequence
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class DiscSequence extends AbstractRepository
{
    protected $entity = Entity::class;

    protected $alias = 'ds';

    public function fetchDiscPrefixes($niFlag, $operatorType)
    {
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()
            ->modifyQuery($qb)
            ->with('trafficArea', 'ta')
            ->with('goodsOrPsv', 'gp');

        if ($niFlag == 'Y') {
            $qb->andWhere(
                $qb->expr()->andX(
                    $qb->expr()->isNotNull('ta.id'),
                    $qb->expr()->eq('ta.id', ':taId')
                )
            )
            ->setParameter('taId', TrafficAreaEntity::NORTHERN_IRELAND_TRAFFIC_AREA_CODE);
        } else {
            $qb->andWhere(
                $qb->expr()->andX(
                    $qb->expr()->isNotNull('ta.id'),
                    $qb->expr()->neq('ta.id', ':taId'),
                    $qb->expr()->eq('gp.id', ':operatorType')
                )
            )
            ->setParameter('operatorType', $operatorType)
            ->setParameter('taId', TrafficAreaEntity::NORTHERN_IRELAND_TRAFFIC_AREA_CODE);
        }

        return $qb->getQuery()->getResult();
    }
}
