<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Category;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\Query;

/**
 * Get a list of categories
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class GetList extends AbstractQueryHandler
{
    protected $repoServiceName = 'Category';

    public function handleQuery(QueryInterface $query)
    {
        $list = $this->getRepo()->fetchList($query, Query::HYDRATE_OBJECT);
        return [
            'result' => $this->resultList($list),
            'count' => count($list),
        ];
    }
}
