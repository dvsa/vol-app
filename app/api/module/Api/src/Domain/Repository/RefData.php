<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\System\RefData as Entity;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * class RefData
 */
class RefData extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Filter list
     *
     * @param QueryBuilder   $qb    Doctrine Query builder
     * @param QueryInterface $query Http Query
     *
     * @return void
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        $qb->andWhere($qb->expr()->eq($this->alias . '.refDataCategoryId', ':category'))
            ->setParameter('category', $query->getRefDataCategory());
        $qb->orderBy($this->alias . '.displayOrder');
        $qb->addOrderBy($this->alias . '.description');

        $q = $qb->getQuery();
        $q->setHint(
            \Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER,
            \Gedmo\Translatable\Query\TreeWalker\TranslationWalker::class
        );
        $q->setHint(\Gedmo\Translatable\TranslatableListener::HINT_FALLBACK, 1);
        $q->setHint(\Gedmo\Translatable\TranslatableListener::HINT_TRANSLATABLE_LOCALE, $query->getLanguage());
    }

    /**
     * Apply List Joins
     *
     * @param QueryBuilder $qb Doctrine Query builder
     *
     * @return void
     */
    protected function applyListJoins(QueryBuilder $qb)
    {
        $this->getQueryBuilder()->modifyQuery($qb)->with('parent', 'p');
    }

    /**
     * Fetch ref data by category id ordered by display order
     *
     * @param string $categoryId
     *
     * @return array
     */
    public function fetchByCategoryId($categoryId)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('r')
            ->from(Entity::class, 'r')
            ->where('r.refDataCategoryId = ?1')
            ->orderBy('r.displayOrder', 'ASC')
            ->setParameter(1, $categoryId)
            ->getQuery()
            ->getResult();
    }
}
