<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Bookmark;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseCompany;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class CompaniesHouseCompanyBundle extends AbstractQueryHandler
{
    protected $repoServiceName = 'CompaniesHouseCompany';

    protected $extraRepos = [
        'Licence'
    ];


    /**
     * @param QueryInterface $query
     *
     * @return array
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query): array
    {
        /** @var Licence $licenceEntity */
        $licenceEntity = $this->getRepo('Licence')->fetchById($query->getId());
        $companyOrLlpNo = $licenceEntity->getOrganisation()->getCompanyOrLlpNo();

        /** @var CompaniesHouseCompany $companiesHouseCompany */
        $companiesHouseCompany = $this->getRepo()->getLatestByCompanyNumber(
            $companyOrLlpNo
        );

        return $this->result(
            $companiesHouseCompany,
            $query->getBundle()
        )->serialize();
    }
}
