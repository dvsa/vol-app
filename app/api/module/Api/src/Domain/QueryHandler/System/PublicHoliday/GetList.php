<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\System\PublicHoliday;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Handler for GET LIST of Public holidays
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class GetList extends AbstractQueryHandler
{
    protected $repoServiceName = 'PublicHoliday';

    public function handleQuery(QueryInterface $query)
    {
        /** @var \Dvsa\Olcs\Api\Domain\Repository\SystemInfoMessage $repo */
        $repo = $this->getRepo();

        $result = $repo->fetchList($query, Query::HYDRATE_OBJECT);

        return [
            'result' => $this->resultList($result),
            'count' => $repo->fetchCount($query),
        ];
    }
}
