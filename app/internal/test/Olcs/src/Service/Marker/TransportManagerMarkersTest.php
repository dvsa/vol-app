<?php

/**
 * Transport Manager Markers Service Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Service\Marker;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use Olcs\Service\Marker\TransportManagerMarkers;

/**
 * Transport Manager Markers Service Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerMarkersTest extends MockeryTestCase
{
    protected $sm;
    protected $sut;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->sut = new TransportManagerMarkers();
    }

    /**
     * Test set and get transport manager
     *
     * @group transportManagerMarkersService
     */
    public function testSetAndGetTransportManager()
    {
        $input = ['foo'];
        $this->sut->setTransportManager($input);

        $this->assertEquals($this->sut->getTransportManager(), $input);
    }

    /**
     * Test set and get application transport managers
     *
     * @group transportManagerMarkersService
     */
    public function testSetAndGetApplicationTransportManagers()
    {
        $input = ['foo'];
        $this->sut->setApplicationTransportManagers($input);

        $this->assertEquals($this->sut->getApplicationTransportManagers(), $input);
    }

    /**
     * Test set and get licence transport managers
     *
     * @group transportManagerMarkersService
     */
    public function testSetAndGetLicenceTransportManagers()
    {
        $input = ['foo'];
        $this->sut->setLicenceTransportManagers($input);

        $this->assertEquals($this->sut->getLicenceTransportManagers(), $input);
    }

    /**
     * Test generate marker types for transport manager
     *
     * @group transportManagerMarkersService1
     * @dataProvider transportManagerMarkersProvider
     */
    public function testGenerateMarkerTypes($markerTypes, $data, $expected)
    {
        $result = $this->sut->generateMarkerTypes($markerTypes, $data);
        $this->assertEquals($result, $expected);
    }

    /**
     * Transport Manager Markers provider
     */
    public function transportManagerMarkersProvider()
    {
        return [
            /*
             * Markers for transport manager section
             */
            'rule 4/50 vehicle authority exceeded (licence/vehicles)' => [
                // marker type
                ['transportManager'],
                // TM data
                [
                    'transportManager' => [
                        'transportManager' => [
                            'id' => 1,
                            'tmLicences' => [
                                [
                                    'licence' => [
                                        'organisation' => ['id' => 1],
                                        // authority for licence more than 50 vehicles
                                        'totAuthVehicles' => 51,
                                        'licenceType' => ['id' => 'ltyp_si'],
                                        'status' => ['id' => 'lsts_valid'],
                                        'niFlag' => 'N'
                                    ]
                                ]
                            ],
                            'tmApplications' => [],
                            'tmType' => ['id' => 'tm_t_e'],
                            'homeCd' => [
                                'person' => [
                                    'forename' => 'John',
                                    'familyName' => 'Boo'
                                ]
                            ],
                            'qualifications' => [
                                ['qualificationType' => ['id' => 'tm_qt_cpcsi']]
                            ]
                        ]
                    ]
                ],
                // expected
                [
                    'transportManager' => [
                        [
                            'content' => "4/50 limit exceeded\n1 operators / 51 vehicles",
                            'data' => []
                        ]
                    ]
                ]
            ],
            'rule 4/50 vehicle authority exceeded (licence/operators)' => [
                // marker type
                ['transportManager'],
                // TM data
                [
                    'transportManager' => [
                        'transportManager' => [
                            'id' => 1,
                            'tmLicences' => [
                                // more than 4 different operators
                                [
                                    'licence' => [
                                        'organisation' => ['id' => 1],
                                        'totAuthVehicles' => 2,
                                        'licenceType' => ['id' => 'ltyp_si'],
                                        'status' => ['id' => 'lsts_valid'],
                                        'niFlag' => 'N'
                                    ]
                                ],
                                [
                                    'licence' => [
                                        'organisation' => ['id' => 2],
                                        'totAuthVehicles' => 2,
                                        'licenceType' => ['id' => 'ltyp_si'],
                                        'status' => ['id' => 'lsts_valid'],
                                        'niFlag' => 'N'
                                    ]
                                ],
                                [
                                    'licence' => [
                                        'organisation' => ['id' => 3],
                                        'totAuthVehicles' => 2,
                                        'licenceType' => ['id' => 'ltyp_si'],
                                        'status' => ['id' => 'lsts_valid'],
                                        'niFlag' => 'N'
                                    ]
                                ],
                                [
                                    'licence' => [
                                        'organisation' => ['id' => 4],
                                        'totAuthVehicles' => 2,
                                        'licenceType' => ['id' => 'ltyp_si'],
                                        'status' => ['id' => 'lsts_valid'],
                                        'niFlag' => 'N'
                                    ]
                                ],
                                [
                                    'licence' => [
                                        'organisation' => ['id' => 5],
                                        'totAuthVehicles' => 2,
                                        'licenceType' => ['id' => 'ltyp_si'],
                                        'status' => ['id' => 'lsts_valid'],
                                        'niFlag' => 'N'
                                    ]
                                ]
                            ],
                            'tmApplications' => [],
                            'tmType' => ['id' => 'tm_t_e'],
                            'homeCd' => [
                                'person' => [
                                    'forename' => 'John',
                                    'familyName' => 'Boo'
                                ]
                            ],
                            'qualifications' => [
                                ['qualificationType' => ['id' => 'tm_qt_cpcsi']]
                            ]
                        ]
                    ]
                ],
                // expected
                [
                    'transportManager' => [
                        [
                            'content' => "4/50 limit exceeded\n5 operators / 10 vehicles",
                            'data' => []
                        ]
                    ]
                ]
            ],
            'rule 4/50 vehicle authority exceeded (application/vehicles)' => [
                // marker type
                ['transportManager'],
                // TM data
                [
                    'transportManager' => [
                        'transportManager' => [
                            'id' => 1,
                            'tmLicences' => [],
                            'tmApplications' => [
                                [
                                    'application' => [
                                        'licenceType' => ['id' => 'ltyp_si'],
                                        'status' => ['id' => 'apsts_consideration'],
                                        'isVariation' => 0,
                                        // authority for application is more than 50 vehicles
                                        'totAuthVehicles' => 51,
                                        'niFlag' => 'N',
                                        'licence' => ['organisation' => ['id' => 1]]
                                    ]
                                ]
                            ],
                            'tmType' => ['id' => 'tm_t_e'],
                            'homeCd' => [
                                'person' => [
                                    'forename' => 'John',
                                    'familyName' => 'Boo'
                                ]
                            ],
                            'qualifications' => [
                                ['qualificationType' => ['id' => 'tm_qt_cpcsi']]
                            ]
                        ]
                    ]
                ],
                // expected
                [
                    'transportManager' => [
                        [
                            'content' => "4/50 limit exceeded\n1 operators / 51 vehicles",
                            'data' => []
                        ]
                    ]
                ]
            ],
            'rule 4/50 vehicle authority exceeded (application/operators)' => [
                // marker type
                ['transportManager'],
                // TM data
                [
                    'transportManager' => [
                        'transportManager' => [
                            'id' => 1,
                            'tmLicences' => [],
                            'tmApplications' => [
                                // more than 4 different operators
                                [
                                    'application' => [
                                        'licenceType' => ['id' => 'ltyp_si'],
                                        'status' => ['id' => 'apsts_consideration'],
                                        'isVariation' => 0,
                                        'totAuthVehicles' => 1,
                                        'niFlag' => 'N',
                                        'licence' => ['organisation' => ['id' => 1]]
                                    ]
                                ],
                                [
                                    'application' => [
                                        'licenceType' => ['id' => 'ltyp_si'],
                                        'status' => ['id' => 'apsts_consideration'],
                                        'isVariation' => 0,
                                        'totAuthVehicles' => 1,
                                        'niFlag' => 'N',
                                        'licence' => ['organisation' => ['id' => 2]]
                                    ]
                                ],
                                [
                                    'application' => [
                                        'licenceType' => ['id' => 'ltyp_si'],
                                        'status' => ['id' => 'apsts_consideration'],
                                        'isVariation' => 0,
                                        'totAuthVehicles' => 1,
                                        'niFlag' => 'N',
                                        'licence' => ['organisation' => ['id' => 3]]
                                    ]
                                ],
                                [
                                    'application' => [
                                        'licenceType' => ['id' => 'ltyp_si'],
                                        'status' => ['id' => 'apsts_consideration'],
                                        'isVariation' => 0,
                                        'totAuthVehicles' => 1,
                                        'niFlag' => 'N',
                                        'licence' => ['organisation' => ['id' => 4]]
                                    ]
                                ],
                                [
                                    'application' => [
                                        'licenceType' => ['id' => 'ltyp_si'],
                                        'status' => ['id' => 'apsts_consideration'],
                                        'isVariation' => 0,
                                        'totAuthVehicles' => 1,
                                        'niFlag' => 'N',
                                        'licence' => ['organisation' => ['id' => 5]]
                                    ]
                                ]
                            ],
                            'tmType' => ['id' => 'tm_t_e'],
                            'homeCd' => [
                                'person' => [
                                    'forename' => 'John',
                                    'familyName' => 'Boo'
                                ]
                            ],
                            'qualifications' => [
                                ['qualificationType' => ['id' => 'tm_qt_cpcsi']]
                            ]
                        ]
                    ]
                ],
                // expected
                [
                    'transportManager' => [
                        [
                            'content' => "4/50 limit exceeded\n5 operators / 5 vehicles",
                            'data' => []
                        ]
                    ]
                ]
            ],
            'qualification marker (licence/GB)' => [
                // marker type
                ['transportManager'],
                // TM data
                [
                    'transportManager' => [
                        'transportManager' => [
                            'id' => 1,
                            // test for tmLicences
                            'tmLicences' => [
                                [
                                    'licence' => [
                                        'organisation' => ['id' => 1],
                                        'totAuthVehicles' => 1,
                                        'licenceType' => ['id' => 'ltyp_si'],
                                        'status' => ['id' => 'lsts_valid'],
                                        // test for GB
                                        'niFlag' => 'N'
                                    ]
                                ]
                            ],
                            'tmApplications' => [],
                            'tmType' => ['id' => 'tm_t_e'],
                            'homeCd' => [
                                'person' => [
                                    'forename' => 'John',
                                    'familyName' => 'Boo'
                                ]
                            ],
                            'qualifications' => [
                                // no required qualification
                                ['qualificationType' => ['id' => 'tm_qt_WRONG']]
                            ]
                        ]
                    ]
                ],
                // expected
                [
                    'transportManager' => [
                        [
                            'content' => "GB SI qualification required",
                            'data' => []
                        ]
                    ]
                ]
            ],
            'qualification marker (licence/NI)' => [
                // marker type
                ['transportManager'],
                // TM data
                [
                    'transportManager' => [
                        'transportManager' => [
                            'id' => 1,
                            // test for tmLicences
                            'tmLicences' => [
                                [
                                    'licence' => [
                                        'organisation' => ['id' => 1],
                                        'totAuthVehicles' => 1,
                                        'licenceType' => ['id' => 'ltyp_si'],
                                        'status' => ['id' => 'lsts_valid'],
                                        // test for NI
                                        'niFlag' => 'Y'
                                    ]
                                ]
                            ],
                            'tmApplications' => [],
                            'tmType' => ['id' => 'tm_t_e'],
                            'homeCd' => [
                                'person' => [
                                    'forename' => 'John',
                                    'familyName' => 'Boo'
                                ]
                            ],
                            'qualifications' => [
                                // no required qualification
                                ['qualificationType' => ['id' => 'tm_qt_WRONG']]
                            ]
                        ]
                    ]
                ],
                // expected
                [
                    'transportManager' => [
                        [
                            'content' => "NI SI qualification required",
                            'data' => []
                        ]
                    ]
                ]
            ],
            'qualification marker (application/GB)' => [
                // marker type
                ['transportManager'],
                // TM data
                [
                    'transportManager' => [
                        'transportManager' => [
                            'id' => 1,
                            'tmLicences' => [],
                            // test for tmApplcations
                            'tmApplications' => [
                                [
                                    'application' => [
                                        'licenceType' => ['id' => 'ltyp_si'],
                                        'status' => ['id' => 'apsts_consideration'],
                                        'isVariation' => 0,
                                        'totAuthVehicles' => 1,
                                        // test for GB
                                        'niFlag' => 'N',
                                        'licence' => ['organisation' => ['id' => 1]]
                                    ]
                                ]
                            ],
                            'tmType' => ['id' => 'tm_t_e'],
                            'homeCd' => [
                                'person' => [
                                    'forename' => 'John',
                                    'familyName' => 'Boo'
                                ]
                            ],
                            'qualifications' => [
                                // no required qualification
                                ['qualificationType' => ['id' => 'tm_qt_WRONG']]
                            ]
                        ]
                    ]
                ],
                // expected
                [
                    'transportManager' => [
                        [
                            'content' => "GB SI qualification required",
                            'data' => []
                        ]
                    ]
                ]
            ],
            'qualification marker (application/NI)' => [
                // marker type
                ['transportManager'],
                // TM data
                [
                    'transportManager' => [
                        'transportManager' => [
                            'id' => 1,
                            'tmLicences' => [],
                            // test for tmApplcations
                            'tmApplications' => [
                                [
                                    'application' => [
                                        'licenceType' => ['id' => 'ltyp_si'],
                                        'status' => ['id' => 'apsts_consideration'],
                                        'isVariation' => 0,
                                        'totAuthVehicles' => 1,
                                        // test for NI
                                        'niFlag' => 'Y',
                                        'licence' => ['organisation' => ['id' => 1]]
                                    ]
                                ]
                            ],
                            'tmType' => ['id' => 'tm_t_e'],
                            'homeCd' => [
                                'person' => [
                                    'forename' => 'John',
                                    'familyName' => 'Boo'
                                ]
                            ],
                            'qualifications' => [
                                // no required qualification
                                ['qualificationType' => ['id' => 'tm_qt_WRONG']]
                            ]
                        ]
                    ]
                ],
                // expected
                [
                    'transportManager' => [
                        [
                            'content' => "NI SI qualification required",
                            'data' => []
                        ]
                    ]
                ]
            ],
            'no markers (type internal)' => [
                // marker type
                ['transportManager'],
                // TM data
                [
                    'transportManager' => [
                        'transportManager' => [
                            'id' => 1,
                            'tmLicences' => [],
                            'tmApplications' => [
                                [
                                    'application' => [
                                        'licenceType' => ['id' => 'ltyp_si'],
                                        'status' => ['id' => 'apsts_consideration'],
                                        'isVariation' => 0,
                                        'totAuthVehicles' => 1,
                                        'niFlag' => 'Y',
                                        'licence' => ['organisation' => ['id' => 1]]
                                    ]
                                ]
                            ],
                            // don't need to put markeres for Internal TM
                            'tmType' => ['id' => 'tm_t_i'],
                            'homeCd' => [
                                'person' => [
                                    'forename' => 'John',
                                    'familyName' => 'Boo'
                                ]
                            ],
                            'qualifications' => [
                                ['qualificationType' => ['id' => 'tm_qt_WRONG']]
                            ]
                        ]
                    ]
                ],
                // expected
                [
                    'transportManager' => []
                ]
            ],
            'no markers (licence standard national / tm application)' => [
                // marker type
                ['transportManager'],
                // TM data
                [
                    'transportManager' => [
                        'transportManager' => [
                            'id' => 1,
                            'tmLicences' => [],
                            // test for tmApplications
                            'tmApplications' => [
                                [
                                    'application' => [
                                        // don't need to put markeres for Standard National Licence
                                        'licenceType' => ['id' => 'ltyp_sn'],
                                        'status' => ['id' => 'apsts_consideration'],
                                        'isVariation' => 0,
                                        'totAuthVehicles' => 1,
                                        'niFlag' => 'Y',
                                        'licence' => ['organisation' => ['id' => 1]]
                                    ]
                                ]
                            ],
                            'tmType' => ['id' => 'tm_t_e'],
                            'homeCd' => [
                                'person' => [
                                    'forename' => 'John',
                                    'familyName' => 'Boo'
                                ]
                            ],
                            'qualifications' => [
                                ['qualificationType' => ['id' => 'tm_qt_WRONG']]
                            ]
                        ]
                    ]
                ],
                // expected
                [
                    'transportManager' => []
                ]
            ],
            'no markers (licence standard national / tm licence)' => [
                // marker type
                ['transportManager'],
                // TM data
                [
                    'transportManager' => [
                        'transportManager' => [
                            'id' => 1,
                            // test for tmLicences
                            'tmLicences' => [
                                [
                                    'licence' => [
                                        'organisation' => ['id' => 1],
                                        'totAuthVehicles' => 1,
                                        // don't need to put markeres for Standard National Licence
                                        'licenceType' => ['id' => 'ltyp_sn'],
                                        'status' => ['id' => 'lsts_valid'],
                                        'niFlag' => 'Y'
                                    ]
                                ]
                            ],
                            'tmApplications' => [],
                            'tmType' => ['id' => 'tm_t_e'],
                            'homeCd' => [
                                'person' => [
                                    'forename' => 'John',
                                    'familyName' => 'Boo'
                                ]
                            ],
                            'qualifications' => [
                                ['qualificationType' => ['id' => 'tm_qt_WRONG']]
                            ]
                        ]
                    ]
                ],
                // expected
                [
                    'transportManager' => []
                ]
            ],
            /*
             * Markers for LVA / transport manager section
             */
            'Licence page: rule 4/50 vehicle authority exceeded (licence/vehicles)' => [
                // marker type
                ['licenceTransportManagers'],
                // Licence TM data
                [
                    'licenceTransportManagers' => [
                        'licenceTransportManagers' => [
                            [
                                'licence' => [
                                    'organisation' => ['id' => 1],
                                    'totAuthVehicles' => 1,
                                    'licenceType' => ['id' => 'ltyp_si'],
                                    'status' => ['id' => 'lsts_valid'],
                                    'niFlag' => 'N'
                                ],
                                'transportManager' => [
                                    'id' => 1,
                                    'tmType' => ['id' => 'tm_t_e'],
                                    'homeCd' => [
                                        'person' => [
                                            'forename' => 'John',
                                            'familyName' => 'Boo'
                                        ]
                                    ],
                                    'qualifications' => [
                                        ['qualificationType' => ['id' => 'tm_qt_cpcsi']]
                                    ],
                                    'tmLicences' => [
                                        [
                                            'licence' => [
                                                'organisation' => ['id' => 1],
                                                // authority for licence more than 50 vehicles
                                                'totAuthVehicles' => 51,
                                                'licenceType' => ['id' => 'ltyp_si'],
                                                'status' => ['id' => 'lsts_valid'],
                                                'niFlag' => 'N'
                                            ],
                                        ]
                                    ],
                                    'tmApplications' => []
                                ]
                            ]
                        ]
                    ]
                ],
                // expected
                [
                    'licenceTransportManagers' => [
                        [
                            'content' => "John Boo\n4/50 limit exceeded\n1 operators / 51 vehicles",
                            'data' => []
                        ]
                    ]
                ]
            ],
            'Licence page: qualification marker' => [
                // marker type
                ['licenceTransportManagers'],
                // Licence TM data
                [
                    'licenceTransportManagers' => [
                        'licenceTransportManagers' => [
                            [
                                'licence' => [
                                    'organisation' => ['id' => 1],
                                    'totAuthVehicles' => 1,
                                    'licenceType' => ['id' => 'ltyp_si'],
                                    'status' => ['id' => 'lsts_valid'],
                                    'niFlag' => 'N'
                                ],
                                'transportManager' => [
                                    'id' => 1,
                                    'tmType' => ['id' => 'tm_t_e'],
                                    'homeCd' => [
                                        'person' => [
                                            'forename' => 'John',
                                            'familyName' => 'Boo'
                                        ]
                                    ],
                                    'qualifications' => [
                                        ['qualificationType' => ['id' => 'tm_qt_WRONG']]
                                    ],
                                    'tmLicences' => [
                                        [
                                            'licence' => [
                                                'organisation' => ['id' => 1],
                                                'totAuthVehicles' => 1,
                                                'licenceType' => ['id' => 'ltyp_si'],
                                                'status' => ['id' => 'lsts_valid'],
                                                'niFlag' => 'N'
                                            ],
                                        ]
                                    ],
                                    'tmApplications' => []
                                ]
                            ]
                        ]
                    ]
                ],
                // expected
                [
                    'licenceTransportManagers' => [
                        [
                            'content' => "John Boo\nGB SI qualification required",
                            'data' => []
                        ]
                    ]
                ]
            ],
            'Application page: rule 4/50 vehicle authority exceeded (licence/vehicles)' => [
                // marker type
                ['applicationTransportManagers'],
                // Applications TM data
                [
                    'applicationTransportManagers' => [
                        'applicationTransportManagers' => [
                            [
                                'application' => [
                                    'organisation' => ['id' => 1],
                                    'totAuthVehicles' => 1,
                                    'licenceType' => ['id' => 'ltyp_sn'],
                                    'status' => ['id' => 'appst_consideration'],
                                    'niFlag' => 'N',
                                    'isVariation' => 0
                                ],
                                'transportManager' => [
                                    'id' => 1,
                                    'tmType' => ['id' => 'tm_t_e'],
                                    'homeCd' => [
                                        'person' => [
                                            'forename' => 'John',
                                            'familyName' => 'Boo'
                                        ]
                                    ],
                                    'qualifications' => [
                                        ['qualificationType' => ['id' => 'tm_qt_cpcsi']]
                                    ],
                                    'tmApplications' => [
                                        [
                                            'application' => [
                                                'licenceType' => ['id' => 'ltyp_si'],
                                                'status' => ['id' => 'apsts_consideration'],
                                                'isVariation' => 0,
                                                // authority for application is more than 50 vehicles
                                                'totAuthVehicles' => 51,
                                                'niFlag' => 'N',
                                                'licence' => ['organisation' => ['id' => 1]]
                                            ]
                                        ]
                                    ],
                                    'tmLicences' => []
                                ]
                            ]
                        ]
                    ]
                ],
                // expected
                [
                    'applicationTransportManagers' => [
                        [
                            'content' => "John Boo\n4/50 limit exceeded\n1 operators / 51 vehicles",
                            'data' => []
                        ]
                    ]
                ]
            ],
            'Application page: qualification marker' => [
                // marker type
                ['applicationTransportManagers'],
                // Application TM data
                [
                    'applicationTransportManagers' => [
                        'applicationTransportManagers' => [
                            [
                                'application' => [
                                    'organisation' => ['id' => 1],
                                    'totAuthVehicles' => 1,
                                    'licenceType' => ['id' => 'ltyp_si'],
                                    'status' => ['id' => 'apsts_consideration'],
                                    'niFlag' => 'Y',
                                    'isVariation' => 0
                                ],
                                'transportManager' => [
                                    'id' => 1,
                                    'tmType' => ['id' => 'tm_t_e'],
                                    'homeCd' => [
                                        'person' => [
                                            'forename' => 'John',
                                            'familyName' => 'Boo'
                                        ]
                                    ],
                                    'qualifications' => [
                                        ['qualificationType' => ['id' => 'tm_qt_WRONG']]
                                    ],
                                    'tmLicences' => [],
                                    'tmApplications' => []
                                ]
                            ]
                        ]
                    ]
                ],
                // expected
                [
                    'applicationTransportManagers' => [
                        [
                            'content' => "John Boo\nNI SI qualification required",
                            'data' => []
                        ]
                    ]
                ]
            ],
            'Application page: no markers, type is internal' => [
                // marker type
                ['applicationTransportManagers'],
                // Application TM data
                [
                    'applicationTransportManagers' => [
                        'applicationTransportManagers' => [
                            [
                                'application' => [
                                    'organisation' => ['id' => 1],
                                    'totAuthVehicles' => 1,
                                    'licenceType' => ['id' => 'ltyp_si'],
                                    'status' => ['id' => 'apsts_consideration'],
                                    'niFlag' => 'Y',
                                    'isVariation' => 0
                                ],
                                'transportManager' => [
                                    'id' => 1,
                                    'tmType' => ['id' => 'tm_t_i'],
                                    'homeCd' => [
                                        'person' => [
                                            'forename' => 'John',
                                            'familyName' => 'Boo'
                                        ]
                                    ],
                                    'qualifications' => [
                                        ['qualificationType' => ['id' => 'tm_qt_WRONG']]
                                    ],
                                    'tmLicences' => [],
                                    'tmApplications' => []
                                ]
                            ]
                        ]
                    ]
                ],
                // expected
                [
                    'applicationTransportManagers' => []
                ]
            ],
        ];
    }
}
