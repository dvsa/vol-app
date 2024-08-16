<?php

namespace Dvsa\Olcs\Api\Service\Permits\Common;

class StockBasedRestrictedCountryIdsProvider
{
    /**
     * Create service instance
     *
     *
     * @return StockBasedRestrictedCountryIdsProvider
     */
    public function __construct(private readonly StockBasedPermitTypeConfigProvider $stockBasedPermitTypeConfigProvider)
    {
    }

    /**
     * Return the restricted country codes corresponding to the specified stock id
     *
     * @param int $stockId
     *
     * @return array
     */
    public function getIds($stockId)
    {
        $permitTypeConfig = $this->stockBasedPermitTypeConfigProvider->getPermitTypeConfig($stockId);
        return $permitTypeConfig->getRestrictedCountryIds();
    }
}
