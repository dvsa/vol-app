<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity as Entities;
use Dvsa\Olcs\Api\Entity\System\Category as Entity;
use Dvsa\Olcs\Transfer\Query as TransferQry;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Category
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class Category extends AbstractRepository
{
    protected $entity = Entity::class;

    /** @var  \Dvsa\Olcs\Transfer\Query\Category\GetList */
    protected $query;

    /**
     * Attach filters to query
     *
     * @param QueryBuilder                               $qb    Query Builder
     * @param \Dvsa\Olcs\Transfer\Query\Category\GetList $query Http query
     *
     * @return void
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        if (method_exists($query, 'getIsTaskCategory') && !empty($query->getIsTaskCategory())) {
            $qb->andWhere($qb->expr()->eq('m.isTaskCategory', ':isTaskCategory'))
                ->setParameter('isTaskCategory', $query->getIsTaskCategory() === 'Y');
        }

        if (method_exists($query, 'getIsDocCategory') && !empty($query->getIsDocCategory())) {
            $qb->andWhere($qb->expr()->eq('m.isDocCategory', ':isDocCategory'))
                ->setParameter('isDocCategory', $query->getIsDocCategory() === 'Y');
        }

        if (method_exists($query, 'getIsScanCategory') && !empty($query->getIsScanCategory())) {
            $qb->andWhere($qb->expr()->eq('m.isScanCategory', ':isScanCategory'))
                ->setParameter('isScanCategory', $query->getIsScanCategory() === 'Y');
        }
    }

    /**
     * Join table to query by conditions
     *
     * @param QueryBuilder $qb Query Builder
     *
     * @return void
     */
    protected function applyListJoins(QueryBuilder $qb)
    {
        $expr = $qb->expr();

        if (
            method_exists($this->query, 'getIsDocCategory')
            && $this->query->getIsDocCategory() === 'Y'
            && method_exists($this->query, 'getIsOnlyWithItems')
            && $this->query->getIsOnlyWithItems() === 'Y'
        ) {
            $qb
                ->select('DISTINCT ' . $this->alias)
                ->join(
                    Entities\Doc\DocTemplate::class,
                    'dct',
                    Query\Expr\Join::WITH,
                    $expr->eq('dct.category', $this->alias . '.id')
                )
                ->join(Entities\Doc\Document::class, 'dc', Query\Expr\Join::WITH, $expr->eq('dc.id', 'dct.document'));

            $this->getQueryBuilder()->modifyQuery($qb);
        }
    }
}
