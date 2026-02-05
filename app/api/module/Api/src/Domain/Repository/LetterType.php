<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Letter\LetterType as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\Query;

/**
 * LetterType Repository
 */
class LetterType extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Fetch list with related entities
     *
     * @param QueryInterface $query Query
     * @param int $hydrateMode Hydration mode
     * @return array
     */
    #[\Override]
    public function fetchList(QueryInterface $query, $hydrateMode = Query::HYDRATE_OBJECT)
    {
        $qb = $this->createQueryBuilder();

        // Eager load the relationships
        $qb->leftJoin('m.masterTemplate', 'mt')
           ->addSelect('mt')
           ->leftJoin('m.category', 'cat')
           ->addSelect('cat')
           ->leftJoin('m.subCategory', 'subcat')
           ->addSelect('subcat')
           ->leftJoin('m.letterTestData', 'ltd')
           ->addSelect('ltd');

        // Apply any filters from the query (e.g., pagination, sorting)
        $this->applyListFilters($qb, $query);

        return $qb->getQuery()->getResult($hydrateMode);
    }

    /**
     * Apply list filters
     *
     * @param \Doctrine\ORM\QueryBuilder $qb Query builder
     * @param \Dvsa\Olcs\Transfer\Query\QueryInterface $query Query
     * @return void
     */
    protected function applyListFilters($qb, QueryInterface $query)
    {
        parent::applyListFilters($qb, $query);

        if (method_exists($query, 'getIsActive')) {
            $isActive = $query->getIsActive();
            if ($isActive !== null) {
                $qb->andWhere($qb->expr()->eq('m.isActive', ':isActive'))
                   ->setParameter('isActive', $isActive);
            }
        }
    }
}
