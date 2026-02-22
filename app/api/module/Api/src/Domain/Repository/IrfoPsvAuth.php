<?php

/**
 * Irfo Psv Auth
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuth as Entity;
use Dvsa\Olcs\Api\Domain\Exception;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Transfer\Query\Irfo\IrfoPsvAuthContinuationList;

/**
 * Irfo Psv Auth
 */
class IrfoPsvAuth extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Fetch the default record by it's id
     *
     * @param int $id
     * @param int $hydrateMode
     * @param int|null $version
     * @return mixed
     * @throws Exception\NotFoundException
     * @throws Exception\VersionConflictException
     */
    #[\Override]
    public function fetchById($id, $hydrateMode = Query::HYDRATE_OBJECT, $version = null)
    {
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()->modifyQuery($qb)
            ->withRefdata()
            ->with('irfoPsvAuthType')
            ->with('irfoPsvAuthNumbers')
            ->with('countrys')
            ->byId($id);

        $results = $qb->getQuery()->getResult($hydrateMode);

        if (empty($results)) {
            throw new Exception\NotFoundException('Resource not found');
        }

        if ($hydrateMode === Query::HYDRATE_OBJECT && $version !== null) {
            $this->lock($results[0], $version);
        }

        return $results[0];
    }

    /**
     * Fetch a list for an organisation
     *
     * @param int|\Dvsa\Olcs\Api\Entity\Organisation\Organisation $organisation
     *
     * @return array
     */
    public function fetchByOrganisation($organisation)
    {
        $doctrineQb = $this->createQueryBuilder();

        $doctrineQb->andWhere($doctrineQb->expr()->eq($this->alias . '.organisation', ':organisaion'))
            ->setParameter('organisaion', $organisation);

        return $doctrineQb->getQuery()->getResult();
    }

    /**
     * @param QueryBuilder $qb
     * @param QueryInterface $query
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        if ($query instanceof IrfoPsvAuthContinuationList) {
            // apply different filters for continuation
            $this->applyListFiltersForContinuation($qb, $query);
        } else {
            $qb->andWhere($qb->expr()->eq($this->alias . '.organisation', ':byOrganisation'));
            $qb->setParameter('byOrganisation', $query->getOrganisation());

            $this->getQueryBuilder()->modifyQuery($qb)->with('irfoPsvAuthType');
        }
    }

    /**
     * Applies list filters for continuation
     *
     * @return void
     */
    private function applyListFiltersForContinuation(QueryBuilder $qb, QueryInterface $query)
    {
        $year = $query->getYear();
        $month = $query->getMonth();

        $this->getQueryBuilder()->modifyQuery($qb)
            ->with('organisation', 'o');

        $startDate = new \DateTime($year . '-' . $month . '-01');
        $qb->andWhere($qb->expr()->gte($this->alias . '.expiryDate', ':expiryFrom'))
            ->setParameter('expiryFrom', $startDate);

        $endDate = clone $startDate;
        $endDate->add(new \DateInterval('P1M'));
        $qb->andWhere($qb->expr()->lt($this->alias . '.expiryDate', ':expiryTo'))
            ->setParameter('expiryTo', $endDate);

        $qb->andWhere(
            $qb->expr()->in(
                $this->alias . '.status',
                [
                    Entity::STATUS_APPROVED,
                    Entity::STATUS_GRANTED,
                    Entity::STATUS_PENDING,
                    Entity::STATUS_RENEW,
                ]
            )
        );

        $qb->andWhere($qb->expr()->eq('o.isIrfo', ':isIrfo'))
            ->setParameter('isIrfo', true);
    }
}
