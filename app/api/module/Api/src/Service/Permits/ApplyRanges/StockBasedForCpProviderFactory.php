<?php

namespace Dvsa\Olcs\Api\Service\Permits\ApplyRanges;

use Dvsa\Olcs\Api\Service\Permits\Common\StockBasedRestrictedCountryIdsProvider;

class StockBasedForCpProviderFactory
{
    /**
     * Create service instance
     *
     *
     * @return StockBasedForCpProviderFactory
     */
    public function __construct(private readonly StockBasedRestrictedCountryIdsProvider $stockBasedRestrictedCountryIdsProvider, private readonly ForCpProviderFactory $forCpProviderFactory)
    {
    }

    /**
     * Create instance of ForCpProvider corresponding to the specified stock id
     *
     * @param int $irhpPermitStockId
     *
     * @return ForCpProvider
     */
    public function create($irhpPermitStockId)
    {
        return $this->forCpProviderFactory->create(
            $this->stockBasedRestrictedCountryIdsProvider->getIds($irhpPermitStockId)
        );
    }
}
