<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\System\SubCategoryDescription as Entity;

/**
 * SubCategoryDescription
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class SubCategoryDescription extends AbstractRepository
{
    protected $entity = Entity::class;

    protected function applyListFilters(\Doctrine\ORM\QueryBuilder $qb, \Dvsa\Olcs\Transfer\Query\QueryInterface $query)
    {
        if (method_exists($query, 'getSubCategory') && !empty($query->getSubCategory())) {
            $qb->andWhere($qb->expr()->eq('m.subCategory', ':subCategory'))
                ->setParameter('subCategory', $query->getSubCategory());
        }
    }
}
