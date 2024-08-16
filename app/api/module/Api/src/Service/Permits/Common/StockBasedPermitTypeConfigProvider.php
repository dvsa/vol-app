<?php

namespace Dvsa\Olcs\Api\Service\Permits\Common;

use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as IrhpPermitStockRepository;

class StockBasedPermitTypeConfigProvider
{
    /**
     * Create service instance
     *
     *
     * @return StockBasedPermitTypeConfigProvider
     */
    public function __construct(private readonly IrhpPermitStockRepository $irhpPermitStockRepo, private readonly TypeBasedPermitTypeConfigProvider $typeBasedPermitTypeConfigProvider)
    {
    }

    /**
     * Return the permit type config corresponding to the specified stock id
     *
     * @param int $stockId
     *
     * @return PermitTypeConfig
     */
    public function getPermitTypeConfig($stockId)
    {
        $irhpPermitStock = $this->irhpPermitStockRepo->fetchById($stockId);
        $irhpPermitTypeId = $irhpPermitStock->getIrhpPermitType()->getId();

        $excludedRestrictedCountryIds = $irhpPermitStock->getExcludedRestrictedCountryIds();

        $restrictedCountryIds = $this->typeBasedPermitTypeConfigProvider->getPermitTypeConfig($irhpPermitTypeId, $excludedRestrictedCountryIds);

        return $restrictedCountryIds;
    }
}
