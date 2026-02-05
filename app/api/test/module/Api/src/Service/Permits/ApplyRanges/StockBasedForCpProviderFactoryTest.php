<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\ApplyRanges;

use Dvsa\Olcs\Api\Service\Permits\ApplyRanges\ForCpProvider;
use Dvsa\Olcs\Api\Service\Permits\ApplyRanges\ForCpProviderFactory;
use Dvsa\Olcs\Api\Service\Permits\ApplyRanges\StockBasedForCpProviderFactory;
use Dvsa\Olcs\Api\Service\Permits\Common\StockBasedRestrictedCountryIdsProvider;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * StockBasedForCpProviderFactoryTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class StockBasedForCpProviderFactoryTest extends MockeryTestCase
{
    public function testCreate(): void
    {
        $irhpPermitStockId = 53;

        $restrictedCountryIds = ['HU' ,'RU', 'IT'];

        $forCpProvider = m::mock(ForCpProvider::class);

        $stockBasedRestrictedCountryIdsProvider = m::mock(StockBasedRestrictedCountryIdsProvider::class);
        $stockBasedRestrictedCountryIdsProvider->shouldReceive('getIds')
            ->with($irhpPermitStockId)
            ->andReturn($restrictedCountryIds);

        $forCpProviderFactory = m::mock(ForCpProviderFactory::class);
        $forCpProviderFactory->shouldReceive('create')
            ->once()
            ->with($restrictedCountryIds)
            ->andReturn($forCpProvider);

        $stockBasedForCpProviderFactory = new StockBasedForCpProviderFactory(
            $stockBasedRestrictedCountryIdsProvider,
            $forCpProviderFactory
        );

        $this->assertSame(
            $forCpProvider,
            $stockBasedForCpProviderFactory->create($irhpPermitStockId)
        );
    }
}
