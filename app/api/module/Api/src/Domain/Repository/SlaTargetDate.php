<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\System\SlaTargetDate as Entity;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * SLA Target Date
 */
class SlaTargetDate extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Fetches SLA target date by Entity ID and Entity Type
     * Uses this as the Add command requires an entity type + an entity Id, hence this provides a composite key to
     * uniquely identify a row. Used for editing to be consistent, rather than using the primary key.
     *
     * @param $entityType
     * @param $entityId
     * @return array
     */
    public function fetchUsingEntityIdAndType(
        $entityType,
        $entityId
    ) {
        $qb = $this->createQueryBuilder();

        $qb->andWhere($qb->expr()->eq($this->alias . '.' . $entityType, ':byEntityId'))
            ->setParameter('byEntityId', $entityId);

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * Apply List Filters
     * @param QueryBuilder   $qb    Query Builder
     * @param QueryInterface $query Query
     * @return QueryBuilder|void
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        $entityType = $query->getEntityType();
        $qb->andWhere($qb->expr()->eq($this->alias . '.' . $entityType, ':by' . ucfirst($entityType)))
            ->setParameter('by' . ucfirst($entityType), $query->getEntityId());

        return $qb;
    }

    /**
     * Fetch by document id
     *
     * @param int $documentId document id
     *
     * @return array
     */
    public function fetchByDocumentId($documentId)
    {
        $qb = $this->createQueryBuilder();
        $qb->andWhere($qb->expr()->eq($this->alias . '.document', ':documentId'));
        $qb->setParameter('documentId', $documentId);

        return $qb->getQuery()->getResult();
    }
}
