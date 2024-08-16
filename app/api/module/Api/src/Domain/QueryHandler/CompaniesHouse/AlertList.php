<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\CompaniesHouse;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseAlert;
use Dvsa\Olcs\Transfer\Query\CompaniesHouse\AlertList as AlertListQuery;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class AlertList extends AbstractQueryHandler
{
    protected $repoServiceName = 'CompaniesHouseAlert';

    /**
     * Handle query
     *
     * @param QueryInterface|AlertListQuery $query Query
     *
     * @return array
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var Repository\CompaniesHouseAlert $repo */
        $repo = $this->getRepo();

        $query = $this->modifyTrafficAreaQueryBasedOnUser($query);

        /** @var CompaniesHouseAlert[] $companiesHouseAlerts */
        $companiesHouseAlerts = $repo->fetchList($query, Query::HYDRATE_OBJECT);

        $results = [];

        foreach ($companiesHouseAlerts as $companiesHouseAlert) {
            foreach ($companiesHouseAlert->getOrganisation()->getLicences() as $licence) {
                $resultList = $this->resultList([$companiesHouseAlert], ['reasons' => ['reasonType']])[0];
                $resultList['licence'] = $this->resultList([$licence], ['licenceType' => ['description']])[0];
                $resultList['organisation'] = $this->resultList([$companiesHouseAlert->getOrganisation()])[0];
                $results[] = $resultList;
            }
        }

        return [
            'result' => $results,
            'count' => $repo->fetchCount($query),
            'valueOptions' => ['companiesHouseAlertReason' => $repo->getReasonValueOptions(),]
        ];
    }
}
