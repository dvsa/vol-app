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

    #[\Override]
    public function handleQuery(QueryInterface $query)
    {
        /** @var LocalAuthorityRepo $repo */
        /** @var LocalAuthorityRepo $repo */
        $repo = $this->getRepo();

        $authorities = $this->resultList(
            $repo->fetchList($query, Query::HYDRATE_OBJECT),
            ['trafficArea']
        );

        // Flag the authorities another has replaced, so lists can offer only current ones
        $supersededIds = array_flip($repo->fetchSupersededIds());
        foreach ($authorities as $key => $authority) {
            $authorities[$key]['isSuperseded'] = isset($supersededIds[$authority['id']]);
        }

        return [
            'result' => $authorities,
            'count' => $repo->fetchCount($query)
        ];
    }
}
