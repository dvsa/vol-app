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
use Dvsa\Olcs\Api\Entity\System\RefData;
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

    public function fetchType(string $id): RefData
    {
        $type = $this->getEntityManager()
            ->getRepository(RefData::class)
            ->findOneBy(['id' => $id, 'refDataCategoryId' => Entity::TYPE_CATEGORY]);

        if ($type === null) {
            throw new Exception\NotFoundException('Grace period type not found');
        }

        return $type;
    }

    #[\Override]
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        $qb->where($qb->expr()->eq($this->alias . '.licence', ':licenceId'));
        $qb->setParameter(':licenceId', $query->getLicence());
    }
}
