<?php

/**
 * LocalAuthority
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\LocalAuthority;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\LocalAuthority as LocalAuthorityRepo;

/**
 * LocalAuthority
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class LocalAuthorityList extends AbstractQueryHandler
{
    protected $repoServiceName = 'LocalAuthority';

    public function handleQuery(QueryInterface $query)
    {
        /** @var LocalAuthorityRepo $repo */
        $repo = $this->getRepo();
        return [
            'result' => $this->resultList(
                $repo->fetchList($query, Query::HYDRATE_OBJECT),
                ['trafficArea']
            ),
            'count' => $repo->fetchCount($query)
        ];
    }
}
