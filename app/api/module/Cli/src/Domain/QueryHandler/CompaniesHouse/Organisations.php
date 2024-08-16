<?php

namespace Dvsa\Olcs\Cli\Domain\QueryHandler\CompaniesHouse;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class Organisations extends AbstractQueryHandler
{
    protected $repoServiceName = 'Organisation';

    public function handleQuery(QueryInterface $query)
    {
        return $this->getRepo()->getAllOrganisationsForCompaniesHouse();
    }
}
