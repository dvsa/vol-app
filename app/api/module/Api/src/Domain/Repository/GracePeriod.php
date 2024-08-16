<?php

/**
 * GracePeriod.php
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Exception;
use Dvsa\Olcs\Api\Entity\Licence\GracePeriod as Entity;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Grace Period
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class GracePeriod extends AbstractRepository
{
    protected $entity = Entity::class;

    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        $qb->where($qb->expr()->eq($this->alias . '.licence', ':licenceId'));
        $qb->setParameter(':licenceId', $query->getLicence());
    }
}
