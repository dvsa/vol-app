<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\IrhpApplication;

use DateTime;
use Dvsa\Olcs\Api\Domain\QueryHandler\IrhpApplication\BilateralMetadata;
use Dvsa\Olcs\Api\Domain\Repository\Country as CountryRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country as CountryEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType as IrhpPermitTypeEntity;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Metadata\CountryGenerator;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\BilateralMetadata as BilateralMetadataQry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

class BilateralMetadataTest extends QueryHandlerTestCase
{
    private $irhpApplicationId;

    private $country1Response;

    private $country2Response;

    private $country1Entity;

    private $country2Entity;

    private $expectedResponse;

    public function setUp(): void
    {
        $this->sut = new BilateralMetadata();

        $this->irhpApplicationId = 207;

        $this->country1Response = [
            'country1Key1' => 'country1Value1',
            'country1Key2' => 'country1Value2'
        ];
        $this->country2Response = [
            'country2Key1' => 'country2Value1',
            'country2Key2' => 'country2Value2'
        ];

        $this->country1Entity = m::mock(CountryEntity::class);
        $this->country2Entity = m::mock(CountryEntity::class);
        $availableCountries = [$this->country1Entity, $this->country2Entity];

        $this->expectedResponse = [
            'countries' => [
                $this->country1Response,
                $this->country2Response
            ]
        ];

        $this->mockRepo('Country', CountryRepo::class);
        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);

        $this->mockedSmServices = [
            'PermitsBilateralMetadataCountryGenerator' => m::mock(CountryGenerator::class),
        ];

        parent::setUp();

        $this->repoMap['Country']->shouldReceive('fetchAvailableCountriesForIrhpApplication')
            ->with(IrhpPermitTypeEntity::IRHP_PERMIT_TYPE_ID_BILATERAL, m::type(DateTime::class))
            ->andReturn($availableCountries);
    }

    public function testHandleQuery(): void
    {
        $irhpApplicationEntity = m::mock(IrhpApplicationEntity::class);

        $query = BilateralMetadataQry::create(
            [
                'irhpApplication' => $this->irhpApplicationId,
            ]
        );

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($this->irhpApplicationId)
            ->andReturn($irhpApplicationEntity);

        $this->mockedSmServices['PermitsBilateralMetadataCountryGenerator']->shouldReceive('generate')
            ->with($this->country1Entity, $irhpApplicationEntity)
            ->once()
            ->andReturn($this->country1Response);
        $this->mockedSmServices['PermitsBilateralMetadataCountryGenerator']->shouldReceive('generate')
            ->with($this->country2Entity, $irhpApplicationEntity)
            ->once()
            ->andReturn($this->country2Response);

        $this->assertEquals(
            $this->expectedResponse,
            $this->sut->handleQuery($query)
        );
    }

    public function testHandleQueryApplicationIdNotSpecified(): void
    {
        $query = BilateralMetadataQry::create([]);

        $this->mockedSmServices['PermitsBilateralMetadataCountryGenerator']->shouldReceive('generate')
            ->with($this->country1Entity, m::type(IrhpApplicationEntity::class))
            ->once()
            ->andReturn($this->country1Response);
        $this->mockedSmServices['PermitsBilateralMetadataCountryGenerator']->shouldReceive('generate')
            ->with($this->country2Entity, m::type(IrhpApplicationEntity::class))
            ->once()
            ->andReturn($this->country2Response);

        $this->assertEquals(
            $this->expectedResponse,
            $this->sut->handleQuery($query)
        );
    }
}
