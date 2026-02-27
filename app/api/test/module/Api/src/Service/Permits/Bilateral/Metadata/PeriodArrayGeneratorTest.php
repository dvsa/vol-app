<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Bilateral\Metadata;

use DateTime;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as IrhpPermitStockRepository;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Service\Common\CurrentDateTimeFactory;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Metadata\PeriodArrayGenerator;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Metadata\PeriodGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * PeriodArrayGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class PeriodArrayGeneratorTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpGenerate')]
    public function testGenerate(mixed $irhpPermitApplication): void
    {
        $behaviour = 'behaviour';

        $countryId = 'FR';

        $stock1Id = 47;
        $stock2Id = 62;

        $stocks = [
            ['id' => $stock1Id],
            ['id' => $stock2Id],
        ];

        $responsePeriod1 = [
            'responsePeriod1Key1' => 'responsePeriod1Value1',
            'responsePeriod1Key2' => 'responsePeriod1Value2'
        ];

        $responsePeriod2 = [
            'responsePeriod2Key1' => 'responsePeriod2Value1',
            'responsePeriod2Key2' => 'responsePeriod2Value2'
        ];

        $country = m::mock(Country::class);
        $country->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($countryId);

        $currentDateTime = m::mock(DateTime::class);

        $irhpPermitStockRepo = m::mock(IrhpPermitStockRepository::class);
        $irhpPermitStockRepo->shouldReceive('fetchOpenBilateralStocksByCountry')
            ->with($countryId, $currentDateTime)
            ->andReturn($stocks);

        $periodGenerator = m::mock(PeriodGenerator::class);
        $periodGenerator->shouldReceive('generate')
            ->with($stock1Id, $behaviour, $irhpPermitApplication)
            ->andReturn($responsePeriod1);
        $periodGenerator->shouldReceive('generate')
            ->with($stock2Id, $behaviour, $irhpPermitApplication)
            ->andReturn($responsePeriod2);

        $currentDateTimeFactory = m::mock(CurrentDateTimeFactory::class);
        $currentDateTimeFactory->shouldReceive('create')
            ->withNoArgs()
            ->andReturn($currentDateTime);

        $periodArrayGenerator = new PeriodArrayGenerator(
            $irhpPermitStockRepo,
            $periodGenerator,
            $currentDateTimeFactory
        );

        $expected = [
            $responsePeriod1,
            $responsePeriod2
        ];

        $this->assertEquals(
            $expected,
            $periodArrayGenerator->generate($behaviour, $country, $irhpPermitApplication)
        );
    }

    public static function dpGenerate(): array
    {
        return [
            [m::mock(IrhpPermitApplication::class)],
            [null]
        ];
    }
}
