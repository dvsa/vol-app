<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Pi\PiSlaException as Entity;

/**
 * PI SLA Exception Repository
 */
class PiSlaException extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Fetch SLA exceptions for a specific PI
     *
     * @param int $piId PI identifier
     *
     * @return array
     */
    public function fetchByPi($piId)
    {
        $qb = $this->createQueryBuilder();
        $qb->andWhere(
            $qb->expr()->eq($this->alias . '.pi', ':piId')
        )
        ->setParameter('piId', $piId)
        ->join($this->alias . '.slaException', 'se')
        ->addSelect('se')
        ->orderBy('se.slaDescription', 'ASC')
        ->addOrderBy($this->alias . '.createdOn', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Fetch SLA exceptions for a specific case (through PI)
     *
     * @param int $caseId Case identifier
     *
     * @return array
     */
    public function fetchByCase($caseId)
    {
        $qb = $this->createQueryBuilder();
        $qb->join($this->alias . '.pi', 'p')
        ->andWhere(
            $qb->expr()->eq('p.case', ':caseId')
        )
        ->setParameter('caseId', $caseId)
        ->join($this->alias . '.slaException', 'se')
        ->addSelect('se', 'p')
        ->orderBy('se.slaDescription', 'ASC')
        ->addOrderBy($this->alias . '.createdOn', 'DESC');

        return $qb->getQuery()->getResult();
    }


    /**
     * Fetch active SLA exceptions for a PI
     *
     * @param int            $piId      PI identifier
     * @param \DateTime|null $checkDate Date to check against (default: now)
     *
     * @return array
     */
    public function fetchActiveByPi($piId, $checkDate = null)
    {
        if ($checkDate === null) {
            $checkDate = new \DateTime();
        }

        $qb = $this->createQueryBuilder();
        $qb->andWhere(
            $qb->expr()->eq($this->alias . '.pi', ':piId')
        )
        ->setParameter('piId', $piId)
        ->join($this->alias . '.slaException', 'se')
        ->andWhere(
            $qb->expr()->lte('se.effectiveFrom', ':checkDate')
        )
        ->andWhere(
            $qb->expr()->orX(
                $qb->expr()->isNull('se.effectiveTo'),
                $qb->expr()->gte('se.effectiveTo', ':checkDate')
            )
        )
        ->setParameter('checkDate', $checkDate)
        ->addSelect('se')
        ->orderBy('se.slaDescription', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
