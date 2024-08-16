<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Exception;
use Dvsa\Olcs\Api\Entity\System\Sla as Entity;

/**
 * SLA
 */
class Sla extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Fetches SLAs by array of category names
     * @param $category
     * @return array
     */
    public function fetchByCategories($categories)
    {
        $qb = $this->createQueryBuilder();
        $qb->andWhere(
            $qb->expr()->in($this->alias . '.category', ':category')
        )->setParameter('category', $categories);

        return $qb->getQuery()->getResult();
    }

    public function fetchByCategoryFieldAndCompareTo(string $category, string $field, string $compareTo): Entity
    {
        $qb = $this->createQueryBuilder();
        $qb->andWhere(
            $qb->expr()->eq($this->alias . '.category', ':category'),
            $qb->expr()->eq($this->alias . '.field', ':field'),
            $qb->expr()->eq($this->alias . '.compareTo', ':compareTo')
        );
        $qb->setParameters(compact('category', 'field', 'compareTo'));

        return $qb->getQuery()->getSingleResult();
    }
}
