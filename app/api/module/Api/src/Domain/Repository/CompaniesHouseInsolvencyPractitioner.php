<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseInsolvencyPractitioner as Entity;

class CompaniesHouseInsolvencyPractitioner extends AbstractRepository
{
    protected $entity = Entity::class;

    public function fetchByCompany($companyId, $hydrateMode = Query::HYDRATE_OBJECT)
    {
        return parent::fetchByX('companiesHouseCompany', [$companyId, $hydrateMode]);
    }
}
