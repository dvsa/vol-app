<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\SubCategory;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\Query;

/**
 * Get a list of sub category's
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class GetList extends AbstractQueryHandler
{
    protected $repoServiceName = 'SubCategory';

    public function handleQuery(QueryInterface $query)
    {
        $list = $this->getRepo()->fetchList($query, Query::HYDRATE_OBJECT);
        return [
            'result' => $this->resultList($list),
            'count' => count($list),
        ];
    }
}
