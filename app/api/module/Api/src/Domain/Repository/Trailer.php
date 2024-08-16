<?php

/**
 * Trailer
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Exception;
use Dvsa\Olcs\Api\Entity\Licence\Trailer as Entity;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Trailer
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class Trailer extends AbstractRepository
{
    protected $entity = Entity::class;

    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        $qb->where($qb->expr()->eq($this->alias . '.licence', ':licenceId'));
        $qb->setParameter(':licenceId', $query->getId());
    }
}
