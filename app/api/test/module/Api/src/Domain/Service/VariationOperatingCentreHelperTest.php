<?php

declare(strict_types=1);

/**
 * Variation Operating Centre Helper Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Service;

use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Api\Domain\Service\VariationOperatingCentreHelper;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Api\Domain\Repository\ApplicationOperatingCentre;
use Dvsa\Olcs\Api\Domain\Repository\LicenceOperatingCentre;
use Dvsa\Olcs\Transfer\Query\Application\OperatingCentres as Qry;

/**
 * Variation Operating Centre Helper Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationOperatingCentreHelperTest extends MockeryTestCase
{
    /**
     * @var VariationOperatingCentreHelper
     */
    protected $sut;

    /**
     * @var ApplicationOperatingCentre
     */
    protected $aocRepo;

    /**
     * @var LicenceOperatingCentre
     */
    protected $locRepo;

    public function setUp(): void
    {
        $this->aocRepo = m::mock();
        $this->locRepo = m::mock();

        $repoSm = m::mock(ServiceLocatorInterface::class);
        $repoSm->shouldReceive('get')
            ->with('ApplicationOperatingCentre')
            ->andReturn($this->aocRepo)
            ->shouldReceive('get')
            ->with('LicenceOperatingCentre')
            ->andReturn($this->locRepo);

        $sm = m::mock(ServiceLocatorInterface::class);
        $sm->shouldReceive('get')
            ->with('RepositoryServiceManager')
            ->andReturn($repoSm);

        $this->sut = new VariationOperatingCentreHelper();

        $this->sut->__invoke($sm, VariationOperatingCentreHelper::class);
    }

    /**
     * @param $aocData
     * @param $locData
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('ocProvider')]
    public function testGetListDataForApplication(mixed $aocData, mixed $locData, mixed $expected, mixed $params): void
    {
        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(222);

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->setId(111);
        $application->setLicence($licence);

        $this->aocRepo->shouldReceive('fetchByApplicationIdForOperatingCentres')
            ->with(111)
            ->andReturn($aocData);

        $this->locRepo->shouldReceive('fetchByLicenceIdForOperatingCentres')
            ->with(222)
            ->andReturn($locData);

        $query = Qry::create($params);
        $this->assertEquals($expected, $this->sut->getListDataForApplication($application, $query));
    }

    public static function ocProvider(): array
    {
        return [
            'noOfVehiclesRequired' => [
                // aoc data
                [
                    [
                        'id' => 321,
                        'action' => 'U',
                        'operatingCentre' => [
                            'id' => 123
                        ],
                        'noOfVehiclesRequired' => 2
                    ]
                ],
                // loc data
                [
                    [
                        'id' => 654,
                        'operatingCentre' => [
                            'id' => 123
                        ],
                        'noOfVehiclesRequired' => 1
                    ],
                    [
                        'id' => 655,
                        'operatingCentre' => [
                            'id' => 999
                        ],
                        'noOfVehiclesRequired' => 3
                    ]
                ],
                // expected
                [
                    [
                        'id' => 'L654',
                        'operatingCentre' => [
                            'id' => 123
                        ],
                        'source' => 'L',
                        'action' => 'C',
                        'sort' => 1,
                        'noOfVehiclesRequired' => 1
                    ],
                    [
                        'id' => 'A321',
                        'action' => 'U',
                        'operatingCentre' => [
                            'id' => 123
                        ],
                        'source' => 'A',
                        'sort' => 2,
                        'noOfVehiclesRequired' => 2
                    ],
                    [
                        'id' => 'L655',
                        'operatingCentre' => [
                            'id' => 999
                        ],
                        'source' => 'L',
                        'action' => 'E',
                        'sort' => 3,
                        'noOfVehiclesRequired' => 3
                    ]
                ],
                // query params
                [
                    'sort' => 'noOfVehiclesRequired',
                    'order' => 'ASC'
                ]
            ],
            'createdOn' => [
                // aoc data
                [
                    [
                        'id' => 321,
                        'action' => 'U',
                        'operatingCentre' => [
                            'id' => 123
                        ],
                        'createdOn' => '2015-01-01'
                    ]
                ],
                // loc data
                [
                    [
                        'id' => 654,
                        'operatingCentre' => [
                            'id' => 123
                        ],
                        'createdOn' => '2016-01-01'
                    ],
                    [
                        'id' => 655,
                        'operatingCentre' => [
                            'id' => 999
                        ],
                        'createdOn' => '2017-01-01'
                    ]
                ],
                // expected
                [
                    [
                        'id' => 'L655',
                        'operatingCentre' => [
                            'id' => 999
                        ],
                        'source' => 'L',
                        'action' => 'E',
                        'sort' => '2017-01-01',
                        'createdOn' => '2017-01-01'
                    ],
                    [
                        'id' => 'L654',
                        'operatingCentre' => [
                            'id' => 123
                        ],
                        'source' => 'L',
                        'action' => 'C',
                        'sort' => '2016-01-01',
                        'createdOn' => '2016-01-01'
                    ],
                    [
                        'id' => 'A321',
                        'action' => 'U',
                        'operatingCentre' => [
                            'id' => 123
                        ],
                        'source' => 'A',
                        'sort' => '2015-01-01',
                        'createdOn' => '2015-01-01'
                    ]
                ],
                // query params
                [
                    'sort' => 'createdOn',
                    'order' => 'DESC'
                ]
            ],
            'address' => [
                // aoc data
                [
                    [
                        'id' => 321,
                        'action' => 'U',
                        'operatingCentre' => [
                            'id' => 123,
                            'address' => [
                                'addressLine1' => 'aaa',
                                'addressLine2' => '',
                                'addressLine3' => '',
                                'addressLine4' => '',
                                'town' => ''
                            ]
                        ],
                    ]
                ],
                // loc data
                [
                    [
                        'id' => 654,
                        'operatingCentre' => [
                            'id' => 123,
                            'address' => [
                                'addressLine1' => 'ccc',
                                'addressLine2' => '',
                                'addressLine3' => '',
                                'addressLine4' => '',
                                'town' => ''
                            ]
                        ],
                    ],
                    [
                        'id' => 655,
                        'operatingCentre' => [
                            'id' => 999,
                            'address' => [
                                'addressLine1' => 'bbb',
                                'addressLine2' => '',
                                'addressLine3' => '',
                                'addressLine4' => '',
                                'town' => ''
                            ]
                        ],
                    ]
                ],
                // expected
                [
                    [
                        'id' => 'A321',
                        'action' => 'U',
                        'operatingCentre' => [
                            'id' => 123,
                            'address' => [
                                'addressLine1' => 'aaa',
                                'addressLine2' => '',
                                'addressLine3' => '',
                                'addressLine4' => '',
                                'town' => ''
                            ]
                        ],
                        'source' => 'A',
                        'sort' => 'aaa',
                    ],
                    [
                        'id' => 'L655',
                        'operatingCentre' => [
                            'id' => 999,
                            'address' => [
                                'addressLine1' => 'bbb',
                                'addressLine2' => '',
                                'addressLine3' => '',
                                'addressLine4' => '',
                                'town' => ''
                            ]
                        ],
                        'source' => 'L',
                        'action' => 'E',
                        'sort' => 'bbb',
                    ],
                    [
                        'id' => 'L654',
                        'operatingCentre' => [
                            'id' => 123,
                            'address' => [
                                'addressLine1' => 'ccc',
                                'addressLine2' => '',
                                'addressLine3' => '',
                                'addressLine4' => '',
                                'town' => ''
                            ]
                        ],
                        'source' => 'L',
                        'action' => 'C',
                        'sort' => 'ccc',
                    ],
                ],
                // query params
                [
                    'sort' => 'adr',
                    'order' => 'ASC'
                ]
            ],
            'lastModifiedOn' => [
                // aoc data
                [
                    [
                        'id' => 321,
                        'action' => 'U',
                        'operatingCentre' => [
                            'id' => 123
                        ],
                        'lastModifiedOn' => '2015-01-01'
                    ]
                ],
                // loc data
                [
                    [
                        'id' => 654,
                        'operatingCentre' => [
                            'id' => 123
                        ],
                        'lastModifiedOn' => '2016-01-01'
                    ],
                    [
                        'id' => 655,
                        'operatingCentre' => [
                            'id' => 999
                        ],
                        'lastModifiedOn' => '2017-01-01'
                    ]
                ],
                // expected
                [
                    [
                        'id' => 'L655',
                        'operatingCentre' => [
                            'id' => 999
                        ],
                        'source' => 'L',
                        'action' => 'E',
                        'sort' => '2017-01-01',
                        'lastModifiedOn' => '2017-01-01'
                    ],
                    [
                        'id' => 'L654',
                        'operatingCentre' => [
                            'id' => 123
                        ],
                        'source' => 'L',
                        'action' => 'C',
                        'sort' => '2016-01-01',
                        'lastModifiedOn' => '2016-01-01'
                    ],
                    [
                        'id' => 'A321',
                        'action' => 'U',
                        'operatingCentre' => [
                            'id' => 123
                        ],
                        'source' => 'A',
                        'sort' => '2015-01-01',
                        'lastModifiedOn' => '2015-01-01'
                    ]
                ],
                // query params
                [
                    'sort' => 'lastModifiedOn',
                    'order' => 'DESC'
                ]
            ],
            'default' => [
                // aoc data
                [
                    [
                        'id' => 321,
                        'action' => 'U',
                        'operatingCentre' => [
                            'id' => 123
                        ],
                    ]
                ],
                // loc data
                [
                    [
                        'id' => 654,
                        'operatingCentre' => [
                            'id' => 123
                        ],
                    ],
                    [
                        'id' => 655,
                        'operatingCentre' => [
                            'id' => 999
                        ],
                    ]
                ],
                // expected
                [
                    [
                        'id' => 'L654',
                        'operatingCentre' => [
                            'id' => 123
                        ],
                        'source' => 'L',
                        'action' => 'C',
                        'sort' => 123
                    ],
                    [
                        'id' => 'A321',
                        'action' => 'U',
                        'operatingCentre' => [
                            'id' => 123
                        ],
                        'source' => 'A',
                        'sort' => 123
                    ],
                    [
                        'id' => 'L655',
                        'operatingCentre' => [
                            'id' => 999
                        ],
                        'source' => 'L',
                        'action' => 'E',
                        'sort' => 999
                    ]
                ],
                // query params
                [
                    'sort' => 'foo',
                    'order' => 'ASC'
                ]
            ],
        ];
    }
}
