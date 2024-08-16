<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity\View\DocTemplateSearchView as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Transfer\Query as TransferQry;

/**
 * Doc Template Search View
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class DocTemplateSearchView extends AbstractReadonlyRepository
{
    /**
     * Setting to false removes the unnecessary DISTINCT clause from pagination queries
     * @see http://doctrine-orm.readthedocs.io/projects/doctrine-orm/en/latest/tutorials/pagination.html
     *
     * @var bool
     */
    protected $fetchJoinCollection = false;

    protected $entity = Entity::class;

    /**
     * Apply filters
     *
     * @param QueryBuilder                      $qb    Query Builder
     * @param TransferQry\DocTemplate\FullList $query Api Query
     *
     * @return void
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        if ($query->getCategory() !== null) {
            $qb->andWhere(
                $qb->expr()->eq('m.category', $query->getCategory())
            );
        }
    }
}
