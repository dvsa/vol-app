<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity\System\TranslationKey as Entity;
use Dvsa\Olcs\Transfer\Query\TranslationKey\GetList;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Translation Key
 */
class TranslationKey extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Apply List Filters
     *
     * @param QueryBuilder $qb Doctrine Query Builder
     * @param QueryInterface $query Http Query
     *
     * @return void
     */
    #[\Override]
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        if ($query instanceof GetList) {
            if ($query->getFormat() !== null) {
                $qb->andWhere($this->alias . '.format = :format')
                    ->setParameter('format', $query->getFormat());
            }

            if ($query->getMarkupOnly()) {
                $qb->andWhere($this->alias . '.translationKey LIKE :markupPrefix')
                    ->setParameter('markupPrefix', 'markup-%');
            }

            if ($query->getTranslationSearch() != null) {
                $qb->leftJoin($this->alias . '.translationKeyTexts', 'tkt')
                    ->andWhere($qb->expr()->orX(
                        $this->alias . '.id LIKE :translationSearch',
                        $this->alias . '.description LIKE :translationSearch',
                        $this->alias . '.translationKey LIKE :translationSearch',
                        'tkt.translatedText LIKE :translationSearch',
                    ))
                    ->setParameter('translationSearch', '%' . $query->getTranslationSearch() . '%');
            }
        }
    }
}
