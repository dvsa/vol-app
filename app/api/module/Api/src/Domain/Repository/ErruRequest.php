<?php

/**
 * Erru Request
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Exception;
use Dvsa\Olcs\Api\Entity\Si\ErruRequest as Entity;

/**
 * Erru Request
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ErruRequest extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Returns whether erru requests exists in the database for this workflow id
     *
     * @param string $workflowId
     * @return bool
     */
    public function existsByWorkflowId($workflowId)
    {
        $qb = $this->createQueryBuilder();

        $qb->where($qb->expr()->eq($this->alias . '.workflowId', ':workflowId'));
        $qb->setParameter('workflowId', $workflowId);
        $qb->setMaxResults(1);

        return count($qb->getQuery()->getResult()) === 1;
    }
}
