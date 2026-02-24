<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Common;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\Country as CountryRepository;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitRange as IrhpPermitRangeRepository;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Service\Permits\Common\PermitTypeConfig;
use Dvsa\Olcs\Api\Service\Permits\Common\RangeBasedRestrictedCountriesProvider;
use Dvsa\Olcs\Api\Service\Permits\Common\TypeBasedPermitTypeConfigProvider;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * RangeBasedRestrictedCountriesProviderTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class RangeBasedRestrictedCountriesProviderTest extends MockeryTestCase
{
    public function testGetList(): void
    {
        $irhpPermitRangeId = 42;

        $irhpPermitRange = m::mock(IrhpPermitRange::class);

        $restrictedCountryIds = ['AT', 'GR', 'HU', 'IT', 'RU'];
        $rangeIncludedCountries = [
            m::mock(Country::class)->shouldReceive('getId')->andReturn('GR')->getMock(),
            m::mock(Country::class)->shouldReceive('getId')->andReturn('RU')->getMock(),
        ];

        $constrainedCountryIds = [0 => 'AT', 2 => 'HU', 3 => 'IT'];
        $constrainedCountries = [
            m::mock(Country::class),
            m::mock(Country::class),
            m::mock(Country::class),
        ];

        $irhpPermitTypeId = IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM;

        $irhpPermitRange->shouldReceive('getIrhpPermitStock->getIrhpPermitType->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($irhpPermitTypeId);
        $irhpPermitRange->shouldReceive('getCountrys')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection($rangeIncludedCountries));

        $irhpPermitRangeRepo = m::mock(IrhpPermitRangeRepository::class);
        $irhpPermitRangeRepo->shouldReceive('fetchById')
            ->with($irhpPermitRangeId)
            ->once()
            ->andReturn($irhpPermitRange);

        $permitTypeConfig = m::mock(PermitTypeConfig::class);
        $permitTypeConfig->shouldReceive('getRestrictedCountryIds')
            ->withNoArgs()
            ->andReturn($restrictedCountryIds);

        $typeBasedPermitTypeConfigProvider = m::mock(TypeBasedPermitTypeConfigProvider::class);
        $typeBasedPermitTypeConfigProvider->shouldReceive('getPermitTypeConfig')
            ->with($irhpPermitTypeId)
            ->once()
            ->andReturn($permitTypeConfig);

        $countryRepo = m::mock(CountryRepository::class);
        $countryRepo->shouldReceive('fetchByIds')
            ->with($constrainedCountryIds, Query::HYDRATE_ARRAY)
            ->once()
            ->andReturn($constrainedCountries);

        $rangeBasedRestrictedCountriesProvider = new RangeBasedRestrictedCountriesProvider(
            $irhpPermitRangeRepo,
            $typeBasedPermitTypeConfigProvider,
            $countryRepo
        );

        $this->assertEquals(
            $constrainedCountries,
            $rangeBasedRestrictedCountriesProvider->getList($irhpPermitRangeId)
        );

        // 2nd call for the same range should return results already calculated
        $this->assertEquals(
            $constrainedCountries,
            $rangeBasedRestrictedCountriesProvider->getList($irhpPermitRangeId)
        );
    }
}
