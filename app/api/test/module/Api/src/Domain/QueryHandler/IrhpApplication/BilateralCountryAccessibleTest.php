<?php

declare(strict_types=1);

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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleQuery')]
    public function testHandleQuery(mixed $requestedCountry, mixed $expected): void
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

    public static function dpHandleQuery(): array
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

    private function createMockCountry(mixed $countryId): mixed
    {
        $country = m::mock(Country::class);
        $country->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($countryId);

        return $country;
    }
}
