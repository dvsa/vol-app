<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\CompaniesHouseCompanyBundle as Qry;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class CompanyStatus extends DynamicBookmark
{
    /**
     * Get the data required for the bookmark
     */
    public function getQuery(array $data): QueryInterface
    {
        return Qry::create(['id' => $data['licence'], 'bundle' => []]);
    }

    /**
     * Renders the data for the bookmark
     */
    public function render(): ?string
    {
        return $this->formatCompanyStatus($this->data['companyStatus']);
    }

    private function formatCompanyStatus($companyStatus): ?string
    {
        $statuses = [
            'administration' => 'Administration',
            'insolvency-proceedings' => 'Insolvency Proceedings',
            'liquidation' => 'Liquidation',
            'receivership' => 'Receivership',
            'voluntary-arrangement' => 'Voluntary Arrangement',
        ];

        return strtr($companyStatus ?? '', $statuses);
    }
}
