<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Common;

use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as IrhpPermitStockRepository;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Service\Permits\Common\PermitTypeConfig;
use Dvsa\Olcs\Api\Service\Permits\Common\StockBasedPermitTypeConfigProvider;
use Dvsa\Olcs\Api\Service\Permits\Common\TypeBasedPermitTypeConfigProvider;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * StockBasedPermitTypeConfigProviderTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class StockBasedPermitTypeConfigProviderTest extends MockeryTestCase
{
    public function testGetPermitTypeConfig(): void
    {
        $irhpPermitStockId = 42;
        $excludedRestrictedCountryIds = [Country::ID_AUSTRIA];

        $irhpPermitTypeId = IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM;

        $irhpPermitStock = m::mock(IrhpPermitStock::class);
        $irhpPermitStock->shouldReceive('getIrhpPermitType->getId')
            ->withNoArgs()
            ->andReturn($irhpPermitTypeId);
        $irhpPermitStock->shouldReceive('getExcludedRestrictedCountryIds')
            ->withNoArgs()
            ->once()
            ->andReturn($excludedRestrictedCountryIds);

        $irhpPermitStockRepo = m::mock(IrhpPermitStockRepository::class);
        $irhpPermitStockRepo->shouldReceive('fetchById')
            ->with($irhpPermitStockId)
            ->once()
            ->andReturn($irhpPermitStock);

        $permitTypeConfig = m::mock(PermitTypeConfig::class);

        $typeBasedPermitTypeConfigProvider = m::mock(TypeBasedPermitTypeConfigProvider::class);
        $typeBasedPermitTypeConfigProvider->shouldReceive('getPermitTypeConfig')
            ->with($irhpPermitTypeId, $excludedRestrictedCountryIds)
            ->once()
            ->andReturn($permitTypeConfig);

        $stockBasedPermitTypeConfigProvider = new StockBasedPermitTypeConfigProvider(
            $irhpPermitStockRepo,
            $typeBasedPermitTypeConfigProvider
        );

        $this->assertSame(
            $permitTypeConfig,
            $stockBasedPermitTypeConfigProvider->getPermitTypeConfig($irhpPermitStockId)
        );
    }
}
