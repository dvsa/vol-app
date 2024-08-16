<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\MaxStockPermits as MaxStockPermitsQuery;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Get maximum permitted permits by stock id
 */
final class MaxStockPermits extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrhpPermit';

    protected $extraRepos = ['Licence', 'IrhpPermitStock'];

    /**
     * Handle query
     *
     * @param QueryInterface|MaxStockPermitsQuery $query query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        $licenceId = $query->getLicence();

        $licence = $this->getRepo('Licence')->fetchById($query->getLicence());
        $totAuthVehicles = $licence->getTotAuthVehicles();

        $stockIds = [];
        $stocks = $this->getRepo('IrhpPermitStock')->fetchAll();
        foreach ($stocks as $stock) {
            $stockIds[] = $stock->getId();
        }

        $livePermitCounts = $this->getRepo()->getLivePermitCountsGroupedByStock($licenceId);
        foreach ($livePermitCounts as $row) {
            $livePermitsByStockId[$row['irhpPermitStockId']] = $row['irhpPermitCount'];
        }

        $response = [];
        foreach ($stockIds as $stockId) {
            if (isset($livePermitsByStockId[$stockId])) {
                $maxPermits = $totAuthVehicles - $livePermitsByStockId[$stockId];
                if ($maxPermits < 0) {
                    $maxPermits = 0;
                }
            } else {
                $maxPermits = $totAuthVehicles;
            }
            $response[$stockId] = $maxPermits;
        }

        return ['result' => $response];
    }
}
