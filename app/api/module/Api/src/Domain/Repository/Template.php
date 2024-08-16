<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity\Template\Template as Entity;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Doctrine\ORM\NoResultException;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Template
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class Template extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Attach filters to query
     *
     * @param QueryBuilder                               $qb    Query Builder
     * @param \Dvsa\Olcs\Transfer\Query\Template\AvailableTemplates $query Http query
     *
     * @return void
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        if (method_exists($query, 'getEmailTemplateCategory') && is_numeric($query->getEmailTemplateCategory())) {
            // Category ID can be a real category int, or 0 to indicate the dummy Header/Footer category for email templates.
            $categoryId = $query->getEmailTemplateCategory();
            if ((int) $categoryId === 0) {
                $qb->andWhere($qb->expr()->isNull('m.category'));
            } else {
                $qb->andWhere($qb->expr()->eq('m.category', ':categoryId'))
                    ->setParameter('categoryId', $categoryId);
            }
        }
    }

    /**
     * Fetch by locale, format and name
     *
     * @param string $locale
     * @param string $format
     * @param string $name
     *
     * @return Entity
     */
    public function fetchByLocaleFormatName($locale, $format, $name)
    {
        try {
            return $this->getEntityManager()
                ->createQueryBuilder()
                ->select('t')
                ->from(Entity::class, 't')
                ->where('t.locale = ?1')
                ->andWhere('t.format = ?2')
                ->andWhere('t.name = ?3')
                ->setParameter(1, $locale)
                ->setParameter(2, $format)
                ->setParameter(3, $name)
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException) {
            throw new NotFoundException('Resource not found');
        }
    }

    /**
     * Fetch distinct categories from template rows.
     *
     * @return array
     */
    public function fetchDistinctCategories()
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('cat.description', 'cat.id')
            ->from(Entity::class, 't')
            ->distinct()
            ->innerJoin('t.category', 'cat')
            ->where('t.category IS NOT NULL')
            ->getQuery()->getResult();
    }
}
