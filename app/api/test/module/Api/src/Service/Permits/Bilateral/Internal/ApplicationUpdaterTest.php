<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Bilateral\Internal;

use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal\ApplicationCountryUpdater;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal\ApplicationUpdater;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * ApplicationUpdaterTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class ApplicationUpdaterTest extends MockeryTestCase
{
    public function testUpdate(): void
    {
        $irhpApplication = m::mock(IrhpApplication::class);

        $country1Id = 'FR';
        $country1StockId = 42;
        $country1PermitsRequired = [
            'country1PermitsRequiredKey1' => 'country1PermitsRequiredValue1',
            'country1PermitsRequiredKey2' => 'country1PermitsRequiredValue2'
        ];

        $country2Id = 'NO';
        $country2StockId = 44;
        $country2PermitsRequired = [
            'country2PermitsRequiredKey1' => 'country2PermitsRequiredValue1',
            'country2PermitsRequiredKey2' => 'country2PermitsRequiredValue2'
        ];

        $countries = [
            $country1Id => [
                'periodId' => $country1StockId,
                'permitsRequired' => $country1PermitsRequired
            ],
            $country2Id => [
                'periodId' => $country2StockId,
                'permitsRequired' => $country2PermitsRequired
            ]
        ];

        $applicationCountryUpdater = m::mock(ApplicationCountryUpdater::class);
        $applicationCountryUpdater->shouldReceive('update')
            ->with($irhpApplication, $country1Id, $country1StockId, $country1PermitsRequired)
            ->once();
        $applicationCountryUpdater->shouldReceive('update')
            ->with($irhpApplication, $country2Id, $country2StockId, $country2PermitsRequired)
            ->once();

        $applicationUpdater = new ApplicationUpdater($applicationCountryUpdater);
        $applicationUpdater->update($irhpApplication, $countries);
    }
}
