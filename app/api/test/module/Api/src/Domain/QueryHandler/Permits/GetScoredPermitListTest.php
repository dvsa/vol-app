<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Permits;

use Dvsa\Olcs\Api\Domain\QueryHandler\Permits\GetScoredPermitList as GetScoredPermitListHandler;
use Dvsa\Olcs\Api\Domain\Repository\Country as CountryRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitRange as IrhpPermitRangeRepo;
use Dvsa\Olcs\Api\Domain\Query\Permits\GetScoredPermitList as QryClass;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * GetScoredPermitList Test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class GetScoredPermitListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new GetScoredPermitListHandler();
        $this->mockRepo('Country', CountryRepo::class);
        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);
        $this->mockRepo('IrhpPermitRange', IrhpPermitRangeRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $stockId = 8;

        $scoringReport = [
            [
                'candidatePermitId' => 100,
                'applicationId' => 200,
                'organisationName' => 'British Steel',
                'candidatePermitApplicationScore' => 0.123456,
                'candidatePermitIntensityOfUse' => 0.5,
                'candidatePermitRandomFactor' => 0.2,
                'candidatePermitRandomizedScore' => 0.823322,
                'candidatePermitRequestedEmissionsCategory' => 'emissions_cat_euro6',
                'candidatePermitAssignedEmissionsCategory' => 'emissions_cat_euro5',
                'applicationInternationalJourneys' => RefData::INTER_JOURNEY_LESS_60,
                'applicationSectorName' => 'Coke and refined petroleum products',
                'licenceNo' => 'OB4234565',
                'trafficAreaId' => 'K',
                'trafficAreaName' => 'London and the South East of England',
                'candidatePermitSuccessful' => 1,
                'candidatePermitRangeId' => 4
            ],
            [
                'candidatePermitId' => 101,
                'applicationId' => 201,
                'organisationName' => 'Howdens',
                'candidatePermitApplicationScore' => 0.654321,
                'candidatePermitIntensityOfUse' => 0.25,
                'candidatePermitRandomFactor' => 0.1,
                'candidatePermitRandomizedScore' => 0.223338,
                'candidatePermitRequestedEmissionsCategory' => 'emissions_cat_euro6',
                'candidatePermitAssignedEmissionsCategory' => 'emissions_cat_euro6',
                'applicationInternationalJourneys' => RefData::INTER_JOURNEY_60_90,
                'applicationSectorName' => 'None/More than one of these sectors',
                'licenceNo' => 'TS1234568',
                'trafficAreaId' => 'M',
                'trafficAreaName' => 'Scotland',
                'candidatePermitSuccessful' => 0,
                'candidatePermitRangeId' => 5
            ],
            [
                'candidatePermitId' => 102,
                'applicationId' => 202,
                'organisationName' => 'Top Haulage',
                'candidatePermitApplicationScore' => 0.700201,
                'candidatePermitIntensityOfUse' => 0.3,
                'candidatePermitRandomFactor' => 0.05,
                'candidatePermitRandomizedScore' => 0.102045,
                'candidatePermitRequestedEmissionsCategory' => 'emissions_cat_euro5',
                'candidatePermitAssignedEmissionsCategory' => 'emissions_cat_euro5',
                'applicationInternationalJourneys' => RefData::INTER_JOURNEY_MORE_90,
                'applicationSectorName' => 'Municipal wastes and other wastes',
                'licenceNo' => 'OG4567723',
                'trafficAreaId' => 'G',
                'trafficAreaName' => 'Wales',
                'candidatePermitSuccessful' => 1,
                'candidatePermitRangeId' => 6
            ],
        ];

        $this->repoMap['IrhpApplication']->shouldReceive('fetchScoringReport')
            ->with($stockId)
            ->andReturn($scoringReport);

        $rangeIdToCountryIdAssociations = [
            [
                'rangeId' => 4,
                'countryId' => 'RU'
            ],
            [
                'rangeId' => 4,
                'countryId' => 'AT'
            ],
            [
                'rangeId' => 4,
                'countryId' => 'HU'
            ],
            [
                'rangeId' => 5,
                'countryId' => 'HU'
            ],
            [
                'rangeId' => 5,
                'countryId' => 'RU'
            ]
        ];

        $this->repoMap['IrhpPermitRange']->shouldReceive('fetchRangeIdToCountryIdAssociations')
            ->with($stockId)
            ->andReturn($rangeIdToCountryIdAssociations);

        $applicationIdToCountryIdAssociations = [
            [
                'applicationId' => 200,
                'countryId' => 'IT'
            ],
            [
                'applicationId' => 202,
                'countryId' => 'GR'
            ],
            [
                'applicationId' => 202,
                'countryId' => 'HU'
            ],
            [
                'applicationId' => 202,
                'countryId' => 'IT'
            ]
        ];

        $this->repoMap['IrhpApplication']->shouldReceive('fetchApplicationIdToCountryIdAssociations')
            ->with($stockId)
            ->andReturn($applicationIdToCountryIdAssociations);

        $countryIdsAndDescriptions = [
            [
                'countryId' => 'AT',
                'description' => 'Austria'
            ],
            [
                'countryId' => 'GR',
                'description' => 'Greece'
            ],
            [
                'countryId' => 'HU',
                'description' => 'Hungary'
            ],
            [
                'countryId' => 'IT',
                'description' => 'Italy'
            ],
            [
                'countryId' => 'RU',
                'description' => 'Russia'
            ],
        ];

        $this->repoMap['Country']->shouldReceive('fetchIdsAndDescriptions')
            ->andReturn($countryIdsAndDescriptions);

        $expected = [
            'result' => [
                [
                    'Permit Ref' => 'OB4234565 / 200 / 100',
                    'Operator' => 'British Steel',
                    'Application Score' => 0.123456,
                    'Permit Intensity of Use' => 0.5,
                    'Random Factor' => 0.2,
                    'Randomised Permit Score' => 0.823322,
                    'Requested Emissions Category' => 'emissions_cat_euro6',
                    'Assigned Emissions Category' => 'emissions_cat_euro5',
                    'Percentage International' => 0.3,
                    'Sector' => 'Coke and refined petroleum products',
                    'Devolved Administration' => 'N/A',
                    'Result' => 'Successful',
                    'Restricted Countries - Requested' => 'Italy',
                    'Restricted Countries - Offered' => 'Russia; Austria; Hungary'
                ],
                [
                    'Permit Ref' => 'TS1234568 / 201 / 101',
                    'Operator' => 'Howdens',
                    'Application Score' => 0.654321,
                    'Permit Intensity of Use' => 0.25,
                    'Random Factor' => 0.1,
                    'Randomised Permit Score' => 0.223338,
                    'Requested Emissions Category' => 'emissions_cat_euro6',
                    'Assigned Emissions Category' => 'emissions_cat_euro6',
                    'Percentage International' => 0.75,
                    'Sector' => 'N/A',
                    'Devolved Administration' => 'Scotland',
                    'Result' => 'Unsuccessful',
                    'Restricted Countries - Requested' => '',
                    'Restricted Countries - Offered' => 'Hungary; Russia'
                ],
                [
                    'Permit Ref' => 'OG4567723 / 202 / 102',
                    'Operator' => 'Top Haulage',
                    'Application Score' => 0.700201,
                    'Permit Intensity of Use' => 0.3,
                    'Random Factor' => 0.05,
                    'Randomised Permit Score' => 0.102045,
                    'Requested Emissions Category' => 'emissions_cat_euro5',
                    'Assigned Emissions Category' => 'emissions_cat_euro5',
                    'Percentage International' => 1,
                    'Sector' => 'Municipal wastes and other wastes',
                    'Devolved Administration' => 'Wales',
                    'Result' => 'Successful',
                    'Restricted Countries - Requested' => 'Greece; Hungary; Italy',
                    'Restricted Countries - Offered' => ''
                ]
            ]
        ];

        $query = m::mock(QryClass::class);
        $query->shouldReceive('getStockId')
            ->andReturn($stockId);

        $this->assertEquals(
            $expected,
            $this->sut->handleQuery($query)
        );
    }
}
