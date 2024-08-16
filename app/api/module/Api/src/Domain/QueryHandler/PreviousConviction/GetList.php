<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\PreviousConviction;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\Query;

/**
 * Get a list of PreviousConviction
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class GetList extends AbstractQueryHandler
{
    protected $repoServiceName = 'PreviousConviction';
    protected $extraRepos = ['TransportManager'];

    public function handleQuery(QueryInterface $query)
    {
        /** @var \Dvsa\Olcs\Api\Domain\Repository\PreviousConviction $repo */
        $repo = $this->getRepo();
        $transportManager = $this->getRepo('TransportManager')->fetchById($query->getTransportManager());

        $count = $repo->fetchCount($query);
        return [
            'result' => $this->resultList(
                $repo->fetchList($query, Query::HYDRATE_OBJECT)
            ),
            'count' => $count,
            'count-unfiltered' => $count,
            'transportManager' => $transportManager->serialize()
        ];
    }
}
