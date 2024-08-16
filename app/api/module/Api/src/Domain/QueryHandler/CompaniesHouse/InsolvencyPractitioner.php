<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\CompaniesHouse;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class InsolvencyPractitioner extends AbstractQueryHandler
{
    protected $repoServiceName = 'CompaniesHouseInsolvencyPractitioner';

    public function handleQuery(QueryInterface $query)
    {
        $companyId = $query->getId();
        $repo = $this->getRepo('CompaniesHouseInsolvencyPractitioner');
        return [
            'result' => $repo->fetchByCompany($companyId)
        ];
    }
}
