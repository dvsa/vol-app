<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Letter\LetterType;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Get List of Letter Types
 */
class GetList extends AbstractQueryHandler
{
    protected $repoServiceName = 'LetterType';
    protected $bundle = ['masterTemplate', 'category', 'subCategory'];

    /**
     * Handle query
     *
     * @param QueryInterface $query Query
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        $list = $this->getRepo()->fetchList($query);

        return [
            'result' => $this->resultList($list, $this->bundle),
            'count' => count($list),
        ];
    }
}
