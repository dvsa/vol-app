<?php

/**
 * Bus Registration History List
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Bus;

use Dvsa\Olcs\Api\Domain\QueryHandler\Bus\RegistrationHistoryList as RegistrationHistoryListQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Query\Bus\ByLicenceRoute as ByLicenceRouteQry;

/**
 * Paginated Bus Registration History List
 */
class PaginatedRegistrationHistoryList extends RegistrationHistoryListQueryHandler
{
    protected $repoServiceName = 'Bus';

    /**
     * @param QueryInterface $query
     * @return mixed
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    #[\Override]
    public function handleQuery(QueryInterface $query)
    {
        $busReg = $this->getRepo()->fetchUsingId($query);

        $routeNoQuery = [
            'sort' => $query->getSort(),
            'order' => $query->getOrder(),
            'page' => $query->getPage(),
            'limit' => $query->getLimit(),
            'routeNo' => $busReg->getRouteNo(),
            'licenceId' => $busReg->getLicence()->getId(),
        ];

        return $this->getQueryHandler()->handleQuery(ByLicenceRouteQry::create($routeNoQuery), false);
    }
}
