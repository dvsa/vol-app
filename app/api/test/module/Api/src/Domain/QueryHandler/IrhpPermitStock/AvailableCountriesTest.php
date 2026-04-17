<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\IrhpPermitStock;

use DateTime;
use Dvsa\Olcs\Api\Domain\QueryHandler\IrhpPermitStock\AvailableCountries;
use Dvsa\Olcs\Api\Domain\Repository\Country as CountryRepo;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Transfer\Query\IrhpPermitStock\AvailableCountries as AvailableCountriesQuery;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

class AvailableCountriesTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new AvailableCountries();
        $this->mockRepo('Country', CountryRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $countries = [
            m::mock(Country::class),
            m::mock(Country::class),
            m::mock(Country::class)
        ];

        $query = AvailableCountriesQuery::create([]);

        $this->repoMap['Country']->shouldReceive('fetchAvailableCountriesForIrhpApplication')
            ->with(IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL, m::type(DateTime::class))
            ->andReturn($countries);

        $this->assertEquals(
            ['countries' => $countries],
            $this->sut->handleQuery($query)
        );
    }
}
