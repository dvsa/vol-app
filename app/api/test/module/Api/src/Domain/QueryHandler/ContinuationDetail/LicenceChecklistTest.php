<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\ContinuationDetail;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Domain\QueryHandler\ContinuationDetail\LicenceChecklist;
use Dvsa\Olcs\Api\Domain\Repository\ContinuationDetail as ContinuationDetailRepo;
use Dvsa\Olcs\Api\Domain\Repository\ConditionUndertaking as ConditionUndertakingRepo;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail as ContinuationDetailEntity;
use Dvsa\Olcs\Transfer\Query\ContinuationDetail\LicenceChecklist as LicenceChecklistQry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Service\Lva\SectionAccessService;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;
use Dvsa\Olcs\Api\Entity\Vehicle\Vehicle;
use Mockery as m;

final class LicenceChecklistTest extends QueryHandlerTestCase
{
    /** @var  LicenceChecklist */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new LicenceChecklist();

        $this->mockRepo('ContinuationDetail', ContinuationDetailRepo::class);
        $this->mockRepo('ConditionUndertaking', ConditionUndertakingRepo::class);
        $this->mockedSmServices = [
            'SectionAccessService' => m::mock(SectionAccessService::class),
        ];

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $applicableAuthProperties = [
            'totAuthHgvVehicles',
            'totAuthLgvVehicles',
            'totAuthTrailers',
        ];

        $mockLicence = m::mock(LicenceEntity::class)
            ->shouldReceive('getConditionUndertakings')
            ->andReturn([])
            ->once()
            ->shouldReceive('getOcPendingChanges')
            ->andReturn(1)
            ->once()
            ->shouldReceive('getTmPendingChanges')
            ->andReturn(2)
            ->once()
            ->shouldReceive('getId')
            ->andReturn(1)
            ->once()
            ->shouldReceive('canHaveTrailer')
            ->andReturn(true)
            ->once()
            ->withNoArgs()
            ->shouldReceive('getApplicableAuthProperties')
            ->andReturn($applicableAuthProperties)
            ->once()
            ->withNoArgs()
            ->shouldReceive('isVehicleTypeMixedWithLgv')
            ->andReturn(true)
            ->once()
            ->withNoArgs()
            ->getMock();

        /** @var ContinuationDetailEntity $continuationDetail */
        $mockContinuationDetail = m::mock(ContinuationDetailEntity::class)
            ->shouldReceive('getLicence')
            ->andReturn($mockLicence)
            ->once()
            ->shouldReceive('serialize')
            ->andReturn(
                [
                    'licence' => [
                        'licenceType' => 'expected',
                        'status' => 'expected',
                        'goodsOrPsv' => 'expected',
                        'trafficArea' => 'expected',
                        'organisation' => [
                            'type' => 'expected',
                            'organisationPersons' => [
                                'person' => [
                                    'title' => 'expected'
                                ]
                            ],
                            'organisationUsers' => [
                                'user' => [
                                    'contactDetails' => [
                                        'person'
                                    ],
                                    'roles'
                                ],
                            ]
                        ],
                        'tradingNames',
                        'licenceVehicles' => [
                            'vehicle' => 'expected'
                        ],
                        'correspondenceCd' => [
                            'address',
                            'phoneContacts' => [
                                'phoneContactType',
                            ],
                        ],
                        'establishmentCd' => [
                            'address',
                        ],
                        'operatingCentres' => [
                            'operatingCentre' => [
                                'address'
                            ]
                        ],
                        'tmLicences' => [
                            'transportManager' => [
                                'homeCd' => [
                                    'person' => [
                                        'title'
                                    ]
                                ]
                            ]
                        ],
                        'workshops' => [
                            'contactDetails' => [
                                'person' => [
                                    'title'
                                ],
                                'address'
                            ]
                        ],
                        'tachographIns'
                    ],
                    'sections' => [
                        'fooBar',
                    ],
                    'ocChanges' => 1,
                    'tmChanges' => 2,
                ]
            )
            ->getMock();

        $this->mockedSmServices['SectionAccessService']
            ->shouldReceive('getAccessibleSectionsForLicenceContinuation')
            ->with($mockLicence)
            ->andReturn(['foo_bar' => 'cake', 'conditions_undertakings' => 'cake'])
            ->once()
            ->getMock();

        $query = LicenceChecklistQry::create(['id' => 999]);

        $this->repoMap['ContinuationDetail']->shouldReceive('fetchWithLicence')
            ->with(999)
            ->once()
            ->andReturn($mockContinuationDetail);

        $this->repoMap['ConditionUndertaking']
            ->shouldReceive('fetchListForLicenceReadOnly')
            ->with(1)
            ->andReturn(['foo'])
            ->once()
            ->getMock();

        $expected = [
            'licence' => [
                'licenceType' => 'expected',
                'status' => 'expected',
                'goodsOrPsv' => 'expected',
                'trafficArea' => 'expected',
                'organisation' => [
                    'type' => 'expected',
                    'organisationPersons' => [
                        'person' => [
                            'title' => 'expected'
                        ]
                    ],
                    'organisationUsers' => [
                        'user' => [
                            'contactDetails' => [
                                'person'
                            ],
                            'roles'
                        ],
                    ]
                ],
                'tradingNames',
                'licenceVehicles' => [
                    'vehicle' => 'expected'
                ],
                'correspondenceCd' => [
                    'address',
                    'phoneContacts' => [
                        'phoneContactType',
                    ],
                ],
                'establishmentCd' => [
                    'address',
                ],
                'operatingCentres' => [
                    'operatingCentre' => [
                        'address'
                    ]
                ],
                'tmLicences' => [
                    'transportManager' => [
                        'homeCd' => [
                            'person' => [
                                'title'
                            ]
                        ]
                    ]
                ],
                'workshops' => [
                    'contactDetails' => [
                        'person' => [
                            'title'
                        ],
                        'address'
                    ]
                ],
                'tachographIns'
            ],
            'sections' => [
                'fooBar',
            ],
            'ocChanges' => 1,
            'tmChanges' => 2,
            'hasConditionsUndertakings' => 1,
            'canHaveTrailers' => true,
            'applicableAuthProperties' => $applicableAuthProperties,
            'isMixedWithLgv' => true,
        ];
        $this->assertEquals($expected, $this->sut->handleQuery($query)->serialize());
    }

    public function testHandleQueryOnlyIncludesSpecifiedVehicles(): void
    {
        $mockLicence = m::mock(LicenceEntity::class);
        $mockLicence->shouldReceive('getConditionUndertakings')->once()->andReturn([]);
        $mockLicence->shouldReceive('getOcPendingChanges')->once()->andReturn(1);
        $mockLicence->shouldReceive('getTmPendingChanges')->once()->andReturn(2);
        $mockLicence->shouldReceive('getId')->once()->andReturn(1);
        $mockLicence->shouldReceive('canHaveTrailer')->once()->andReturn(true);
        $mockLicence->shouldReceive('getApplicableAuthProperties')->once()->andReturn([]);
        $mockLicence->shouldReceive('isVehicleTypeMixedWithLgv')->once()->andReturn(false);

        $specified = new LicenceVehicle($mockLicence, new Vehicle());
        $specified->setSpecifiedDate('2020-01-01');

        $unspecified = new LicenceVehicle($mockLicence, new Vehicle());

        $removed = new LicenceVehicle($mockLicence, new Vehicle());
        $removed->setSpecifiedDate('2020-01-01');
        $removed->setRemovalDate('2021-01-01');

        $matching = null;
        $mockContinuationDetail = m::mock(ContinuationDetailEntity::class);
        $mockContinuationDetail->shouldReceive('getLicence')->once()->andReturn($mockLicence);
        $mockContinuationDetail->shouldReceive('serialize')->once()->andReturnUsing(
            function (array $bundle) use ($specified, $unspecified, $removed, &$matching) {
                $this->assertInstanceOf(Criteria::class, $bundle['licence']['licenceVehicles']['criteria']);

                $matching = (new ArrayCollection([$specified, $unspecified, $removed]))
                    ->matching($bundle['licence']['licenceVehicles']['criteria'])
                    ->getValues();

                return [];
            }
        );

        $this->mockedSmServices['SectionAccessService']
            ->shouldReceive('getAccessibleSectionsForLicenceContinuation')
            ->with($mockLicence)
            ->once()
            ->andReturn([]);

        $this->repoMap['ContinuationDetail']->shouldReceive('fetchWithLicence')
            ->with(999)
            ->once()
            ->andReturn($mockContinuationDetail);

        $this->repoMap['ConditionUndertaking']
            ->shouldReceive('fetchListForLicenceReadOnly')
            ->with(1)
            ->once()
            ->andReturn([]);

        $this->sut->handleQuery(LicenceChecklistQry::create(['id' => 999]))->serialize();

        $this->assertCount(1, $matching);
        $this->assertSame($specified, $matching[0]);
    }
}
