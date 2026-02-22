<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\IrhpPermit;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\IrhpPermit\GetListByLicence as Handler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermit as IrhpPermitRepo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Service\Permits\Common\RangeBasedRestrictedCountriesProvider;
use Dvsa\Olcs\Transfer\Query\IrhpPermit\GetListByLicence as GetListByLicenceQuery;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * GetListByLicence Test
 */
class GetListByLicenceTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = m::mock(Handler::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->mockRepo('IrhpPermit', IrhpPermitRepo::class);

        $this->mockedSmServices['PermitsCommonRangeBasedRestrictedCountriesProvider']
            = m::mock(RangeBasedRestrictedCountriesProvider::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = GetListByLicenceQuery::create(
            [
                'licence' => 10,
                'page' => 1,
                'limit' => 25,
            ]
        );

        $irhpPermits = [
            m::mock(IrhpPermit::class),
            m::mock(IrhpPermit::class),
            m::mock(IrhpPermit::class),
            m::mock(IrhpPermit::class),
            m::mock(IrhpPermit::class),
            m::mock(IrhpPermit::class),
            m::mock(IrhpPermit::class),
        ];
        $irhpPermitsCount = 7;

        $this->repoMap['IrhpPermit']->shouldReceive('fetchList')
            ->with($query, Query::HYDRATE_OBJECT)
            ->andReturn($irhpPermits);
        $this->repoMap['IrhpPermit']->shouldReceive('fetchCount')
            ->andReturn($irhpPermitsCount);

        $expectedBundle = [
            'irhpPermitApplication',
            'irhpPermitRange' => [
                'journey',
                'irhpPermitStock' => [
                    'irhpPermitType' => ['name'],
                    'country',
                    'permitCategory',
                ],
                'emissionsCategory',
            ]
        ];

        $bundledIrhpPermits = [
            [
                'id' => 463,
                'irhpPermitRange' => [
                    'id' => 101,
                    'irhpPermitStock' => [
                        'irhpPermitType' => [
                            'id' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT
                        ]
                    ]
                ]
            ],
            [
                'id' => 464,
                'irhpPermitRange' => [
                    'id' => 102,
                    'irhpPermitStock' => [
                        'irhpPermitType' => [
                            'id' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM
                        ]
                    ]
                ]
            ],
            [
                'id' => 465,
                'irhpPermitRange' => [
                    'id' => 103,
                    'irhpPermitStock' => [
                        'irhpPermitType' => [
                            'id' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL
                        ]
                    ]
                ]
            ],
            [
                'id' => 466,
                'irhpPermitRange' => [
                    'id' => 104,
                    'irhpPermitStock' => [
                        'irhpPermitType' => [
                            'id' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL
                        ]
                    ]
                ]
            ],
            [
                'id' => 467,
                'irhpPermitRange' => [
                    'id' => 105,
                    'irhpPermitStock' => [
                        'irhpPermitType' => [
                            'id' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL
                        ]
                    ]
                ]
            ],
            [
                'id' => 468,
                'irhpPermitRange' => [
                    'id' => 106,
                    'irhpPermitStock' => [
                        'irhpPermitType' => [
                            'id' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE
                        ]
                    ]
                ]
            ],
            [
                'id' => 469,
                'irhpPermitRange' => [
                    'id' => 107,
                    'irhpPermitStock' => [
                        'irhpPermitType' => [
                            'id' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER
                        ]
                    ]
                ]
            ],
        ];

        $this->sut->shouldReceive('resultList')
            ->with($irhpPermits, $expectedBundle)
            ->andReturn($bundledIrhpPermits);

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
            );

        $expectedResult = [
            'results' => [
                [
                    'id' => 463,
                    'irhpPermitRange' => [
                        'id' => 101,
                        'irhpPermitStock' => [
                            'irhpPermitType' => [
                                'id' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT
                            ]
                        ]
                    ],
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
                        'irhpPermitStock' => [
                            'irhpPermitType' => [
                                'id' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM
                            ]
                        ]
                    ],
                    'constrainedCountries' => [
                        ['id' => 'IT', 'countryDesc' => 'Italy'],
                        ['id' => 'RU', 'countryDesc' => 'Russia']
                    ]
                ],
                [
                    'id' => 465,
                    'irhpPermitRange' => [
                        'id' => 103,
                        'irhpPermitStock' => [
                            'irhpPermitType' => [
                                'id' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL
                            ]
                        ]
                    ]
                ],
                [
                    'id' => 466,
                    'irhpPermitRange' => [
                        'id' => 104,
                        'irhpPermitStock' => [
                            'irhpPermitType' => [
                                'id' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL
                            ]
                        ]
                    ]
                ],
                [
                    'id' => 467,
                    'irhpPermitRange' => [
                        'id' => 105,
                        'irhpPermitStock' => [
                            'irhpPermitType' => [
                                'id' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL
                            ]
                        ]
                    ]
                ],
                [
                    'id' => 468,
                    'irhpPermitRange' => [
                        'id' => 106,
                        'irhpPermitStock' => [
                            'irhpPermitType' => [
                                'id' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE
                            ]
                        ]
                    ]
                ],
                [
                    'id' => 469,
                    'irhpPermitRange' => [
                        'id' => 107,
                        'irhpPermitStock' => [
                            'irhpPermitType' => [
                                'id' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER
                            ]
                        ]
                    ]
                ],
            ],
            'count' => 7
        ];

        $result = $this->sut->handleQuery($query);
        $this->assertEquals($expectedResult, $result);
    }

    public function testHandleQueryWithEmptyList(): void
    {
        $query = GetListByLicenceQuery::create(
            [
                'licence' => 10,
                'page' => 1,
                'limit' => 25,
            ]
        );

        $irhpPermits = [];
        $irhpPermitsCount = 0;

        $this->repoMap['IrhpPermit']->shouldReceive('fetchList')
            ->with($query, Query::HYDRATE_OBJECT)
            ->andReturn($irhpPermits);
        $this->repoMap['IrhpPermit']->shouldReceive('fetchCount')
            ->andReturn($irhpPermitsCount);

        $expectedBundle = [
            'irhpPermitApplication',
            'irhpPermitRange' => [
                'journey',
                'irhpPermitStock' => [
                    'irhpPermitType',
                    'country',
                    'permitCategory',
                ],
                'emissionsCategory',
            ]
        ];

        $bundledIrhpPermits = [];

        $this->sut->shouldReceive('resultList')
            ->with($irhpPermits, $expectedBundle)
            ->andReturn($bundledIrhpPermits);

        $this->mockedSmServices['PermitsCommonRangeBasedRestrictedCountriesProvider']
            ->shouldReceive('getList')
            ->never();

        $expectedResult = [
            'results' => [],
            'count' => 0
        ];

        $result = $this->sut->handleQuery($query);
        $this->assertEquals($expectedResult, $result);
    }
}
