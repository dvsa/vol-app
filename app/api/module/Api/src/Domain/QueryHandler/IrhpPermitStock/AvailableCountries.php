<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\IrhpPermitStock;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use DateTime;

/**
 * Available countries
 */
class AvailableCountries extends AbstractQueryHandler
{
    protected $repoServiceName = 'Country';

    /**
     * Handle query
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handleQuery(QueryInterface $query)
    {
        return [
            'countries' => $this->getRepo()->fetchAvailableCountriesForIrhpApplication(
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL,
                new DateTime()
            )
        ];
    }
}
