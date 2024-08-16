<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\System\Replacement as Entity;

/**
 * Replacement
 */
class Replacement extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Fetch all translation replacements
     *
     * @param int $hydrationMode
     *
     * @return mixed
     */
    public function fetchAll($hydrationMode = Query::HYDRATE_OBJECT)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('r')
            ->from(Entity::class, 'r')
            ->getQuery()->getResult($hydrationMode);
    }
}
