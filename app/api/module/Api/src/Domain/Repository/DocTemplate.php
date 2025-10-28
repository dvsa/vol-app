<?php

/**
 * DocTemplate
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Doc\DocTemplate as Entity;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;

/**
 * DocTemplate
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DocTemplate extends AbstractRepository
{
    protected $entity = Entity::class;

    protected function applyListFilters(\Doctrine\ORM\QueryBuilder $qb, \Dvsa\Olcs\Transfer\Query\QueryInterface $query)
    {
        if (method_exists($query, 'getCategory') && !empty($query->getCategory())) {
            $qb->andWhere($qb->expr()->eq('m.category', ':category'))
                ->setParameter('category', $query->getCategory());
        }
        if (method_exists($query, 'getSubCategory') && !empty($query->getSubCategory())) {
            $qb->andWhere($qb->expr()->eq('m.subCategory', ':subCategory'))
                ->setParameter('subCategory', $query->getSubCategory());
        }
        if (method_exists($query, 'getNewTemplatesFirst') && $query->getNewTemplatesFirst() === true) {
            $qb->addOrderBy('m.letterType', 'DESC');
        }
    }

    /**
     * Fetch by template slug
     *
     * @param string $slug Template Slug String
     *
     * @return \Dvsa\Olcs\Api\Entity\Doc\DocTemplate
     * @throws NotFoundException
     */
    public function fetchByTemplateSlug(string $slug)
    {
        return $this->fetchOneByX('templateSlug', [$slug]);
    }
}
