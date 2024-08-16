<?php

/**
 *BusReg Search View List
 *
 * @author Craig R <uk@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Bus;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\BusRegSearchView as Repository;
use Dvsa\Olcs\Transfer\Query\Bus\SearchViewList as ListQueryObject;
use Doctrine\ORM\Query as DoctrineQuery;

/**
 * BusReg Search View List
 *
 * @author Craig R <uk@valtech.co.uk>
 */
class SearchViewList extends AbstractQueryHandler
{
    protected $repoServiceName = 'BusRegSearchView';

    /**
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var ListQueryObject $query */
        /** @var Repository $repo */
        $repo = $this->getRepo();

        return [
            'result' => $this->resultList(
                $repo->fetchList($query, DoctrineQuery::HYDRATE_OBJECT)
            ),
            'count' => $repo->fetchCount($query)
        ];
    }
}
