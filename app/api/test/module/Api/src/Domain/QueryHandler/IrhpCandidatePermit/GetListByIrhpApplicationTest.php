<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\IrhpCandidatePermit;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\IrhpCandidatePermit\GetListByIrhpApplication as GetListByIrhpApplicationHandler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpCandidatePermit as IrhpCandidatePermitRepo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit;
use Dvsa\Olcs\Api\Service\Permits\Common\RangeBasedRestrictedCountriesProvider;
use Dvsa\Olcs\Transfer\Query\IrhpCandidatePermit\GetListByIrhpApplication as GetListByIrhpApplicationQry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

class GetListByIrhpApplicationTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = m::mock(GetListByIrhpApplicationHandler::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->mockRepo('IrhpCandidatePermit', IrhpCandidatePermitRepo::class);

        $this->mockedSmServices['PermitsCommonRangeBasedRestrictedCountriesProvider']
            = m::mock(RangeBasedRestrictedCountriesProvider::class);

        parent::setUp();
    }

    public function testHandleQueryWithEmptyList(): void
    {
        $irhpApplicationId = 10;
        $page = 1;
        $limit = 25;

        $query = GetListByIrhpApplicationQry::create(
            [
                'irhpApplication' => $irhpApplicationId,
                'page' => $page,
                'limit' => $limit,
                'order' => 'id',
                'sort' => 'ASC',
            ]
        );

        $irhpCandidatePermits = [];
        $irhpCandidatePermitsCount = 0;

        $this->repoMap['IrhpCandidatePermit']->shouldReceive('fetchList')
            ->with($query, Query::HYDRATE_OBJECT)
            ->andReturn($irhpCandidatePermits);
        $this->repoMap['IrhpCandidatePermit']->shouldReceive('fetchCount')
            ->andReturn($irhpCandidatePermitsCount);

        $expectedBundle = [
            'irhpPermitRange' => [
                'countrys' => [
                    'country'
                ],
                'emissionsCategory',
            ],
            'irhpPermitApplication'
        ];

        $bundledIrhpCandidatePermits = [];

        $this->sut->shouldReceive('resultList')
            ->with($irhpCandidatePermits, $expectedBundle)
            ->andReturn($bundledIrhpCandidatePermits);

        $this->mockedSmServices['PermitsCommonRangeBasedRestrictedCountriesProvider']
            ->shouldReceive('getList')
            ->never();

        $expectedResult = [
            'results' => [],
            'count' => 0,
        ];

        $result = $this->sut->handleQuery($query);
        $this->assertEquals($expectedResult, $result);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestHandleQuery')]
    public function testHandleQuery(
        mixed $queryData,
        mixed $bundledIrhpCandidatePermits,
        mixed $expectedResult
    ): void {
        $query = GetListByIrhpApplicationQry::create($queryData);

        $irhpCandidatePermits = [
            m::mock(IrhpCandidatePermit::class),
            m::mock(IrhpCandidatePermit::class),
            m::mock(IrhpCandidatePermit::class)
        ];
        $irhpCandidatePermitsCount = 3;

        $this->repoMap['IrhpCandidatePermit']->shouldReceive('fetchList')
            ->with($query, Query::HYDRATE_OBJECT)
            ->andReturn($irhpCandidatePermits);
        $this->repoMap['IrhpCandidatePermit']->shouldReceive('fetchCount')
            ->andReturn($irhpCandidatePermitsCount);

        $expectedBundle = [
            'irhpPermitRange' => [
                'countrys' => [
                    'country'
                ],
                'emissionsCategory',
            ],
            'irhpPermitApplication'
        ];

        $this->sut->shouldReceive('resultList')
            ->with($irhpCandidatePermits, $expectedBundle)
            ->andReturn($bundledIrhpCandidatePermits);

        $this->mockedSmServices['PermitsCommonRangeBasedRestrictedCountriesProvider']
            ->shouldReceive('getList')
            ->with(101)
            ->andReturn(
                [
                    ['id' => 'AT', 'countryDesc' => 'Austria'],
                    ['id' => 'GR', 'countryDesc' => 'Greece'],
                    ['id' => 'HU', 'countryDesc' => 'Hungary'],
                    ['id' => 'IT', 'countryDesc' => 'Italy'],
                    ['id' => 'RU', 'countryDesc' => 'Russia'],
                ]
            )
            ->shouldReceive('getList')
            ->with(102)
            ->andReturn(
                [
                    ['id' => 'IT', 'countryDesc' => 'Italy'],
                    ['id' => 'RU', 'countryDesc' => 'Russia'],
                ]
            )
            ->shouldReceive('getList')
            ->with(103)
            ->andReturn(
                [
                    ['id' => 'RU', 'countryDesc' => 'Russia'],
                ]
            );

        $result = $this->sut->handleQuery($query);
        $this->assertEquals($expectedResult, $result);
    }

    public static function dpTestHandleQuery(): array
    {
        return [
            'page 1' => [
                'queryData' => [
                    'irhpApplication' => 10,
                    'page' => 1,
                    'limit' => 25,
                    'order' => 'id',
                    'sort' => 'ASC',
                ],
                'bundledIrhpCandidatePermits' => [
                    [
                        'id' => 463,
                        'irhpPermitRange' => [
                            'id' => 101,
                        ]
                    ],
                    [
                        'id' => 464,
                        'irhpPermitRange' => [
                            'id' => 102,
                        ]
                    ],
                    [
                        'id' => 465,
                        'irhpPermitRange' => [
                            'id' => 103,
                        ]
                    ],
                ],
                'expectedResult' => [
                    'results' => [
                        [
                            'id' => 463,
                            'irhpPermitRange' => [
                                'id' => 101,
                            ],
                            'permitNumber' => 1,
                            'constrainedCountries' => [
                                ['id' => 'AT', 'countryDesc' => 'Austria'],
                                ['id' => 'GR', 'countryDesc' => 'Greece'],
                                ['id' => 'HU', 'countryDesc' => 'Hungary'],
                                ['id' => 'IT', 'countryDesc' => 'Italy'],
                                ['id' => 'RU', 'countryDesc' => 'Russia']
                            ]
                        ],
                        [
                            'id' => 464,
                            'irhpPermitRange' => [
                                'id' => 102,
                            ],
                            'permitNumber' => 2,
                            'constrainedCountries' => [
                                ['id' => 'IT', 'countryDesc' => 'Italy'],
                                ['id' => 'RU', 'countryDesc' => 'Russia']
                            ]
                        ],
                        [
                            'id' => 465,
                            'irhpPermitRange' => [
                                'id' => 103,
                            ],
                            'permitNumber' => 3,
                            'constrainedCountries' => [
                                ['id' => 'RU', 'countryDesc' => 'Russia']
                            ]
                        ]
                    ],
                    'count' => 3
                ],
            ],
            'page 3' => [
                'queryData' => [
                    'irhpApplication' => 10,
                    'page' => 3,
                    'limit' => 25,
                    'order' => 'id',
                    'sort' => 'ASC',
                ],
                'bundledIrhpCandidatePermits' => [
                    [
                        'id' => 463,
                        'irhpPermitRange' => [
                            'id' => 101,
                        ]
                    ],
                    [
                        'id' => 464,
                        'irhpPermitRange' => [
                            'id' => 102,
                        ]
                    ],
                    [
                        'id' => 465,
                        'irhpPermitRange' => [
                            'id' => 103,
                        ]
                    ],
                ],
                'expectedResult' => [
                    'results' => [
                        [
                            'id' => 463,
                            'irhpPermitRange' => [
                                'id' => 101,
                            ],
                            'permitNumber' => 51,
                            'constrainedCountries' => [
                                ['id' => 'AT', 'countryDesc' => 'Austria'],
                                ['id' => 'GR', 'countryDesc' => 'Greece'],
                                ['id' => 'HU', 'countryDesc' => 'Hungary'],
                                ['id' => 'IT', 'countryDesc' => 'Italy'],
                                ['id' => 'RU', 'countryDesc' => 'Russia']
                            ]
                        ],
                        [
                            'id' => 464,
                            'irhpPermitRange' => [
                                'id' => 102,
                            ],
                            'permitNumber' => 52,
                            'constrainedCountries' => [
                                ['id' => 'IT', 'countryDesc' => 'Italy'],
                                ['id' => 'RU', 'countryDesc' => 'Russia']
                            ]
                        ],
                        [
                            'id' => 465,
                            'irhpPermitRange' => [
                                'id' => 103,
                            ],
                            'permitNumber' => 53,
                            'constrainedCountries' => [
                                ['id' => 'RU', 'countryDesc' => 'Russia']
                            ]
                        ]
                    ],
                    'count' => 3
                ],
            ],
        ];
    }
}
