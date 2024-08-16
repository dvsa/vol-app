<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\DBAL\Types\Types;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Api\Entity\System\PublicHoliday as Entity;

class PublicHoliday extends AbstractRepository
{
    protected $entity = Entity::class;
    protected $alias = 'p';

    public function fetchBetweenByTa(\DateTime $startDate, \DateTime $endDate, ?TrafficAreaEntity $trafficArea = null): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('p.publicHolidayDate')->from($this->entity, $this->alias);

        $qb->where($qb->expr()->between('p.publicHolidayDate', ':start', ':end'));

        $qb->setParameter('start', $startDate, Types::DATE_MUTABLE);
        $qb->setParameter('end', $endDate, Types::DATE_MUTABLE);

        if (!is_null($trafficArea)) {
            switch (true) {
                case $trafficArea->getIsEngland():
                    $qb->andWhere($qb->expr()->eq('p.isEngland', 1));
                    break;
                case $trafficArea->getIsNi():
                    $qb->andWhere($qb->expr()->eq('p.isNi', 1));
                    break;
                case $trafficArea->getIsScotland():
                    $qb->andWhere($qb->expr()->eq('p.isScotland', 1));
                    break;
                case $trafficArea->getIsWales():
                    $qb->andWhere($qb->expr()->eq('p.isWales', 1));
                    break;
            }
        } else {
            $qb->andWhere($qb->expr()->eq('p.isEngland', 1));
        }

        return $qb->getQuery()->getArrayResult();
    }
}
