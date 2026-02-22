<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Pi\SlaException as Entity;

/**
 * SLA Exception Repository
 */
class SlaException extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Fetch active SLA exceptions
     *
     * @param \DateTime|null $checkDate Date to check against (default: now)
     *
     * @return array
     */
    public function fetchActive($checkDate = null)
    {
        if ($checkDate === null) {
            $checkDate = new \DateTime();
        }

        $qb = $this->createQueryBuilder();
        $qb->andWhere(
            $qb->expr()->lte($this->alias . '.effectiveFrom', ':checkDate')
        )
        ->andWhere(
            $qb->expr()->orX(
                $qb->expr()->isNull($this->alias . '.effectiveTo'),
                $qb->expr()->gte($this->alias . '.effectiveTo', ':checkDate')
            )
        )
        ->setParameter('checkDate', $checkDate)
        ->orderBy($this->alias . '.slaDescription', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Fetch SLA exceptions by description pattern
     *
     * @param string $pattern Search pattern
     *
     * @return array
     */
    public function fetchByDescriptionPattern($pattern)
    {
        $qb = $this->createQueryBuilder();
        $qb->andWhere(
            $qb->expr()->orX(
                $qb->expr()->like($this->alias . '.slaDescription', ':pattern'),
                $qb->expr()->like($this->alias . '.slaExceptionDescription', ':pattern')
            )
        )
        ->setParameter('pattern', '%' . $pattern . '%')
        ->orderBy($this->alias . '.slaDescription', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Fetch all SLA exceptions ordered by description
     *
     * @return array
     */
    public function fetchAllOrdered()
    {
        $qb = $this->createQueryBuilder();
        $qb->orderBy($this->alias . '.slaDescription', 'ASC')
           ->addOrderBy($this->alias . '.slaExceptionDescription', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
