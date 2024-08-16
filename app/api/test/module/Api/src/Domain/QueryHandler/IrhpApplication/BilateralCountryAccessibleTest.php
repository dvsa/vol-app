<?php

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\QueryHandler\IrhpApplication\BilateralCountryAccessible;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\BilateralCountryAccessible as BilateralCountryAccessibleQry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

class BilateralCountryAccessibleTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new BilateralCountryAccessible();

        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);

        parent::setUp();
    }

    /**
     * @dataProvider dpHandleQuery
     */
    public function testHandleQuery($requestedCountry, $expected)
    {
        $irhpApplicationId = 462;

        $countries = [
            $this->createMockCountry('DE'),
            $this->createMockCountry('SE'),
            $this->createMockCountry('NO'),
        ];

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('getCountrys')
            ->withNoArgs()
            ->andReturn($countries);

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($irhpApplicationId)
            ->andReturn($irhpApplication);

        $query = BilateralCountryAccessibleQry::create(
            [
                'id' => $irhpApplicationId,
                'country' => $requestedCountry
            ]
        );

        $result = $this->sut->handleQuery($query);

        $expected = ['isAccessible' => $expected];

        $this->assertEquals($expected, $result);
    }

    public function dpHandleQuery()
    {
        return [
            'country accessible' => [
                'DE',
                true,
            ],
            'country not accessible' => [
                'FR',
                false,
            ],
        ];
    }

    private function createMockCountry($countryId)
    {
        $country = m::mock(Country::class);
        $country->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($countryId);

        return $country;
    }
}
