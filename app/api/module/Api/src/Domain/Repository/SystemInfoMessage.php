<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\System\SystemInfoMessage as SystemInfoMessageEntity;

/**
 * System Info Message Repository
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class SystemInfoMessage extends AbstractRepository
{
    protected $entity = SystemInfoMessageEntity::class;

    /**
     * @param \Dvsa\Olcs\Transfer\Query\System\InfoMessage\GetListActive $query
     *
     * @return array
     */
    public function fetchListActive($query)
    {
        $now = new DateTime();

        $qb = $this->createDefaultListQuery($query);
        $qb
            ->select('partial ' . $this->alias . '.{id, description}')
            ->andWhere($qb->expr()->eq($this->alias . '.isInternal', ':IS_INTERNAL'))
            ->andWhere($qb->expr()->lte($this->alias . '.startDate', ':NOW'))
            ->andWhere($qb->expr()->gte($this->alias . '.endDate', ':NOW'))
            ->setParameter('IS_INTERNAL', (int) $query->isInternal())
            ->setParameter('NOW', $now);

        return $qb->getQuery()->getResult(Query::HYDRATE_ARRAY);
    }
}
