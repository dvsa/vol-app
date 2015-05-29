<?php
/**
 * Irfo Permit Stock
 */

namespace Admin\Service\Data;

use Common\Service\Data\AbstractData;

/**
 * Irfo Permit Stock
 *
 * @package Admin\Service
 */
class IrfoPermitStock extends AbstractData
{
    /**
     * @var string
     */
    protected $serviceName = 'IrfoPermitStock';

    /**
     * Fetches the list of filtered results
     *
     * @param array $filters Filters
     * @param array $bundle Bundle
     *
     * @return array|false
     */
    public function fetchIrfoPermitStockList(array $filters, array $bundle = [])
    {
        // remove all empty filters
        $query = array_filter($filters);

        $results = $this->fetchList($query, $bundle);

        // there should only every be one ongoing continuation
        if ($results['Count'] === 0) {
            return false;
        }
        return $results;
    }
}
