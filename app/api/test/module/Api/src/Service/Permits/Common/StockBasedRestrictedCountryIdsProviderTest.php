<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Common;

use Dvsa\Olcs\Api\Service\Permits\Common\PermitTypeConfig;
use Dvsa\Olcs\Api\Service\Permits\Common\StockBasedPermitTypeConfigProvider;
use Dvsa\Olcs\Api\Service\Permits\Common\StockBasedRestrictedCountryIdsProvider;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * StockBasedRestrictedCountryIdsProviderTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class StockBasedRestrictedCountryIdsProviderTest extends MockeryTestCase
{
    public function testGetIds(): void
    {
        $irhpPermitStockId = 67;

        $restrictedCountryIds = ['ES', 'FR', 'DE'];

        $permitTypeConfig = m::mock(PermitTypeConfig::class);
        $permitTypeConfig->shouldReceive('getRestrictedCountryIds')
            ->andReturn($restrictedCountryIds);

        $stockBasedPermitTypeConfigProvider = m::mock(StockBasedPermitTypeConfigProvider::class);
        $stockBasedPermitTypeConfigProvider->shouldReceive('getPermitTypeConfig')
            ->with($irhpPermitStockId)
            ->andReturn($permitTypeConfig);

        $stockBasedRestrictedCountryIdsProvider = new StockBasedRestrictedCountryIdsProvider(
            $stockBasedPermitTypeConfigProvider
        );

        $this->assertEquals(
            $restrictedCountryIds,
            $stockBasedRestrictedCountryIdsProvider->getIds($irhpPermitStockId)
        );
    }
}
