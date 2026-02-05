<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Permits;

use Dvsa\Olcs\Api\Domain\QueryHandler\Permits\ReadyToPrintCountry;
use Dvsa\Olcs\Api\Domain\Repository\Country as CountryRepo;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrintCountry as ReadyToPrintCountryQuery;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

class ReadyToPrintCountryTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ReadyToPrintCountry();
        $this->mockRepo('Country', CountryRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $irhpPermitType = 1;

        $countries = [
            m::mock(Country::class),
            m::mock(Country::class),
            m::mock(Country::class)
        ];

        $query = ReadyToPrintCountryQuery::create(
            [
                'irhpPermitType' => $irhpPermitType,
            ]
        );

        $this->repoMap['Country']->shouldReceive('fetchReadyToPrint')
            ->with($irhpPermitType)
            ->andReturn($countries);

        $this->assertEquals(
            ['results' => $countries],
            $this->sut->handleQuery($query)
        );
    }
}
