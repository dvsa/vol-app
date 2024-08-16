<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Permits;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use DateTime;

/**
 * Emissions By Year
 */
class EmissionsByYear extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrhpPermitWindow';

    /**
     * Handle query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        $permitType = $query->getIrhpPermitType();
        if ($permitType != IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT) {
            return [
                'yearEmissions' => []
            ];
        }

        $openWindows = $this->getRepo()->fetchOpenWindowsByTypeYear(
            $permitType,
            new DateTime(),
            $query->getYear()
        );

        $yearEmissions = [];
        foreach ($openWindows as $window) {
            $irhpStock = $window->getIrhpPermitStock();
            $year = $irhpStock->getValidTo(true)->format('Y');
            $yearEmissions[$year]['euro5'] = $irhpStock->hasEuro5Range();
            $yearEmissions[$year]['euro6'] = $irhpStock->hasEuro6Range();
        }

        return ['yearEmissions' => $yearEmissions];
    }
}
