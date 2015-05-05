<?php

namespace OlcsTest\Service\Marker;

use Olcs\Service\Marker\LicenceMarkers;
use Common\Service\Entity\LicenceEntityService;

/**
 * Class LicenceMarkersTest
 * @package OlcsTest\Service\Data
 */
class LicenceMarkersTest extends \PHPUnit_Framework_TestCase
{
    protected $sut;

    public function setUp()
    {
        $this->sut = new LicenceMarkers();
    }

    public function testSetTypeMarkers()
    {
        $this->sut->setTypeMarkers('foo', ['bar']);

        $markers = $this->sut->getTypeMarkers('foo');

        $this->assertCount(1, $markers);
        $this->assertArrayHasKey(0, $markers);
        $this->assertEquals($markers[0], 'bar');
    }

    public function testGetTypeMarkers()
    {
        $markers = [0 => 'bar'];
        $this->sut->setTypeMarkers('foo', $markers);
        $this->assertEquals($markers, $this->sut->getTypeMarkers('foo'));
    }

    public function testGetTypeMarkersNotArray()
    {
        $markers = 'bar';
        $this->sut->setTypeMarkers('foo', $markers);
        $this->assertEquals([], $this->sut->getTypeMarkers('foo'));
    }

    public function testSetAndGetMarkers()
    {
        $input = ['bar'];
        $this->sut->setMarkers($input);

        $this->assertEquals($this->sut->getMarkers(), $input);
    }

    public function testSetAndGetCase()
    {
        $input = ['foo'];
        $this->sut->setCase($input);

        $this->assertEquals($this->sut->getCase(), $input);
    }

    public function testSetAndGetLicence()
    {
        $input = ['foo'];
        $this->sut->setLicence($input);

        $this->assertEquals($this->sut->getLicence(), $input);
    }

    public function testSetAndGetLicenceStatusRule()
    {
        $input = ['foo'];
        $this->sut->setLicenceStatusRule($input);

        $this->assertEquals($this->sut->getLicenceStatusRule(), $input);
    }

    /**
     * Test generate markers
     *
     * @dataProvider providerMarkerData
     * @param $input
     * @param $expected
     */
    public function testGenerateMarkerTypes($input, $expected)
    {
        $result = $this->sut->generateMarkerTypes($input['markerTypes'], $input['data']);
        $this->assertEquals($expected['typeCount'], count($result));
        // check markers generated
        if (isset($expected['markerCount'])) {
            foreach (array_keys($expected['markerCount']) as $type) {
                $this->assertArrayHasKey($type, $result);
                $this->assertEquals($expected['markerCount'][$type], count($result[$type]));
            }
        }

        // check reset behaviour
        $this->assertEquals([], $this->sut->getCase());
        $this->assertEquals([], $this->sut->getLicence());
        $this->assertEquals([], $this->sut->getBusReg());
        $this->assertEquals([], $this->sut->getLicenceStatusRule());
    }

    private function generateStayData($howMany, $override = array())
    {
        $stayData = [];
        for ($i=0; $i<$howMany; $i++) {
            $stayData[] = [
                'withdrawnDate' => isset($override['withdrawnDate']) ? $override['withdrawnDate'] : '',
                'outcome' => isset($override['outcome']) ? $override['outcome'] : '',
                'stayType' => [
                    'id' => 'stay_t_ut'
                ],
                'requestDate' => '2010-01-01 00:00:00'
            ];
        }
        return $stayData;
    }

    private function getAppealData($override = array())
    {
        return [
            'withdrawnDate' => isset($override['withdrawnDate']) ? $override['withdrawnDate'] : '',
            'appealDate' => isset($override['appealDate']) ? $override['appealDate'] : '',
            'outcome' => isset($override['outcome']) ? $override['outcome'] : '',
            'decisionDate' =>  isset($override['decisionDate']) ? $override['decisionDate'] : ''
        ];
    }

    /**
     * Data provider for marker data. The stay data and appeal data fields can be overridden to test business logic
     *
     * @return array
     */
    public function providerMarkerData()
    {
        return [
            [
                ['markerTypes' => false, 'data' => []],
                ['typeCount' => 0]
            ],
            [
                ['markerTypes' => [], 'data' => []],
                ['typeCount' => 0]
            ],
            [
                ['markerTypes' => ['sometypethatdoesntexist'], 'data' => ['case' => ['id' => 1]]],
                ['typeCount' => 0]
            ],
            [
                // input no data
                [
                    'markerTypes' => ['stay'],
                    'data' => [
                        'case' => [
                            'id' => 1,
                            'stays' => [],
                            'appeals' => [],
                            'licence' => 'foo',
                            'ecmsNo' => '12345'
                        ]
                    ]
                ],
                // expected one type count and no markers as appeals not set
                ['typeCount' => 1, 'markerCount' => []]
            ],
            [
                // input all valid stay data 3 markers expected
                [
                    'markerTypes' => ['stay'],
                    'data' => [
                        'case' => [
                            'id' => 1,
                            'stays' => $this->generateStayData(3),
                            'appeals' => [0 => $this->getAppealData()],
                            'licence' => 'foo',
                            'ecmsNo' => '12345'
                        ]
                    ]
                ],
                // expected
                ['typeCount' => 1, 'markerCount' => ['stay' => 3]]
            ],
            [
                // input all valid stay data 0 markers expected as appeal withdrawn date is set
                [
                    'markerTypes' => ['stay'],
                    'data' => [
                        'case' => [
                            'id' => 1,
                            'stays' => $this->generateStayData(3),
                            'appeals' => [
                                0 => $this->getAppealData(
                                    ['withdrawnDate' => '2000-01-01 00:00:00']
                                )
                            ],
                            'licence' => 'foo',
                            'ecmsNo' => '12345'
                        ]
                    ]
                ],
                // expected no markers because appeal decision date is set
                ['typeCount' => 1, 'markerCount' => ['stay' => 0]]
            ],
            [
                // input all valid stay data 0 markers expected as appeal outcome and decision date is set
                [
                    'markerTypes' => ['stay'],
                    'data' => [
                        'case' => [
                            'id' => 1,
                            'stays' => $this->generateStayData(3),
                            'appeals' => [0 => $this->getAppealData(
                                [
                                    'outcome' => 'test',
                                    'decisionDate' => 'test'
                                ]
                            )
                            ],
                            'licence' => 'foo',
                            'ecmsNo' => '12345'
                        ]
                    ]
                ],
                // expected no markers because appeal decision date is set
                ['typeCount' => 1, 'markerCount' => ['stay' => 0]]
            ],
            // appeal markers
            [
                // input all valid data 1 marker expected
                [
                    'markerTypes' => ['appeal'],
                    'data' => [
                        'case' => [
                            'id' => 1,
                            'appeals' => [0 => $this->getAppealData()],
                            'licence' => 'foo',
                            'ecmsNo' => '12345'
                        ]
                    ]
                ],
                // expected
                ['typeCount' => 1, 'markerCount' => ['appeal' => 1]]
            ],
            [
                // input all valid stay data 0 markers expected as appeal withdrawn date is set
                [
                    'markerTypes' => ['appeal'],
                    'data' => [
                        'case' => [
                            'id' => 1,
                            'appeals' => [
                                0 => $this->getAppealData(
                                    ['withdrawnDate' => '2000-01-01 00:00:00']
                                )
                            ],
                            'ecmsNo' => '12345'
                        ]
                    ]
                ],
                // expected no markers because appeal decision date is set
                ['typeCount' => 1, 'markerCount' => ['appeal' => 0]]
            ],
            [
                // input all valid stay data 0 markers expected as appeal outcome and decision date is set
                [
                    'markerTypes' => ['appeal'],
                    'data' => [
                        'case' => [
                            'id' => 1,
                            'appeals' => [
                                0 => $this->getAppealData(
                                    [
                                        'outcome' => 'test',
                                        'decisionDate' => 'test'
                                    ]
                                )
                            ],
                            'ecmsNo' => '12345',
                            'licence' => 'foo'
                        ]
                    ]
                ],
                // expected no markers because appeal decision date is set
                ['typeCount' => 1, 'markerCount' => ['appeal' => 0]]
            ],
            'revoked status' => [
                [
                    'markerTypes' => ['status'],
                    'data' => [
                        'licence' => [
                            'id' => 1,
                            'status' => [
                                'id' => LicenceEntityService::LICENCE_STATUS_REVOKED,
                                'description' => 'Revoked',
                            ],
                        ],
                        'licenceStatusRule' => [
                            'id' => 99,
                            'licenceStatus' => [
                                'id' => LicenceEntityService::LICENCE_STATUS_REVOKED,
                                'description' => 'Revoked',
                            ],
                            'startDate' => '2015-03-23 12:34:56',
                        ],
                    ],
                ],
                ['typeCount' => 1, 'markerCount' => ['status' => 1]],
            ],
            'curtailed status' => [
                [
                    'markerTypes' => ['status'],
                    'data' => [
                        'licence' => [
                            'id' => 1,
                            'status' => [
                                'id' => LicenceEntityService::LICENCE_STATUS_CURTAILED,
                                'description' => 'Curtailed',
                            ],
                        ],
                        'licenceStatusRule' => [
                            'id' => 99,
                            'licenceStatus' => [
                                'id' => LicenceEntityService::LICENCE_STATUS_CURTAILED,
                                'description' => 'Curtailed',
                            ],
                            'startDate' => '2015-03-23 12:34:56',
                            'endDate' => '2015-04-23 12:34:56',
                        ],
                    ],
                ],
                ['typeCount' => 1, 'markerCount' => ['status' => 1]],
            ],
            'suspended status' => [
                [
                    'markerTypes' => ['status'],
                    'data' => [
                        'licence' => [
                            'id' => 1,
                            'status' => [
                                'id' => LicenceEntityService::LICENCE_STATUS_SUSPENDED,
                                'description' => 'Suspended',
                            ],
                        ],
                        'licenceStatusRule' => [
                            'id' => 99,
                            'licenceStatus' => [
                                'id' => LicenceEntityService::LICENCE_STATUS_SUSPENDED,
                                'description' => 'Suspended',
                            ],
                            'startDate' => '2015-03-23 12:34:56',
                            'endDate' => '2015-04-23 12:34:56',
                        ],
                    ],
                ],
                ['typeCount' => 1, 'markerCount' => ['status' => 1]],
            ],
            'suspended missing dates' => [
                [
                    'markerTypes' => ['status'],
                    'data' => [
                        'licence' => [
                            'id' => 1,
                            'status' => [
                                'id' => LicenceEntityService::LICENCE_STATUS_SUSPENDED,
                                'description' => 'Suspended',
                            ],
                        ],
                        'licenceStatusRule' => [
                            'id' => 99,
                            'licenceStatus' => [
                                'id' => LicenceEntityService::LICENCE_STATUS_SUSPENDED,
                                'description' => 'Suspended',
                            ],
                        ],
                    ],
                ],
                ['typeCount' => 1, 'markerCount' => ['status' => 1]],
            ],
            'suspended mow' => [
                [
                    'markerTypes' => ['status'],
                    'data' => [
                        'licence' => [
                            'id' => 1,
                            'suspendedDate' => '2015-1-1 12:00',
                            'status' => [
                                'id' => LicenceEntityService::LICENCE_STATUS_SUSPENDED,
                                'description' => 'Suspended',
                            ],
                        ],
                        'licenceStatusRule' => [],
                    ],
                ],
                ['typeCount' => 1, 'markerCount' => ['status' => 1]],
            ],
            'queued revocation' => [
                [
                    'markerTypes' => ['statusRule'],
                    'data' => [
                        'licence' => [
                            'id' => 1,
                            'status' => [
                                'id' => LicenceEntityService::LICENCE_STATUS_VALID,
                                'description' => 'Valid',
                            ],
                        ],
                        'licenceStatusRule' => [
                            'id' => 99,
                            'licenceStatus' => [
                                'id' => LicenceEntityService::LICENCE_STATUS_REVOKED,
                                'description' => 'Revoked',
                            ],
                            'startDate' => '2015-03-23 12:34:56',
                        ],
                    ],
                ],
                ['typeCount' => 1, 'markerCount' => ['statusRule' => 1]],
            ],
            'queued curtailment' => [
                [
                    'markerTypes' => ['statusRule'],
                    'data' => [
                        'licence' => [
                            'id' => 1,
                            'status' => [
                                'id' => LicenceEntityService::LICENCE_STATUS_VALID,
                                'description' => 'Valid',
                            ],
                        ],
                        'licenceStatusRule' => [
                            'id' => 99,
                            'licenceStatus' => [
                                'id' => LicenceEntityService::LICENCE_STATUS_CURTAILED,
                                'description' => 'Curtailed',
                            ],
                            'startDate' => '2015-03-23 12:34:56',
                            'endDate' => '2015-04-23 12:34:56',
                        ],
                    ],
                ],
                ['typeCount' => 1, 'markerCount' => ['statusRule' => 1]],
            ],
            'queued suspension' => [
                [
                    'markerTypes' => ['statusRule'],
                    'data' => [
                        'licence' => [
                            'id' => 1,
                            'status' => [
                                'id' => LicenceEntityService::LICENCE_STATUS_VALID,
                                'description' => 'Valid',
                            ],
                        ],
                        'licenceStatusRule' => [
                            'id' => 99,
                            'licenceStatus' => [
                                'id' => LicenceEntityService::LICENCE_STATUS_SUSPENDED,
                                'description' => 'Suspended',
                            ],
                            'startDate' => '2015-03-23 12:34:56',
                            'endDate' => '2015-04-23 12:34:56',
                        ],
                    ],
                ],
                ['typeCount' => 1, 'markerCount' => ['statusRule' => 1]],
            ],
            'queued suspension missing data' => [
                [
                    'markerTypes' => ['statusRule'],
                    'data' => [
                        'licence' => [
                            'id' => 1,
                            'status' => [
                                'id' => LicenceEntityService::LICENCE_STATUS_VALID,
                                'description' => 'Valid',
                            ],
                        ],
                        'licenceStatusRule' => [],
                    ],
                ],
                ['typeCount' => 1, 'markerCount' => ['statusRule' => 0]],
            ],
            'other status' => [
                [
                    'markerTypes' => ['statusRule'],
                    'data' => [
                        'licence' => [
                            'id' => 1,
                            'status' => [
                                'id' => LicenceEntityService::LICENCE_STATUS_WITHDRAWN,
                                'description' => 'withdrawn',
                            ],
                        ],
                        'licenceStatusRule' => [],
                    ],
                ],
                ['typeCount' => 1, 'markerCount' => ['statusRule' => 0]],
            ],
        ];
    }

    /**
     *
     */
    public function testContinuationMarkerNotExists()
    {
        $result = $this->sut->generateMarkerTypes(['continuation'], []);

        $this->assertEquals(['continuation' => null], $result);
    }

    public function testContinuationMarker()
    {
        $data = [
            'continuationDetails' => [
                'continuation' => [
                    'year' => 2016,
                    'month' => 4,
                ]

            ]
        ];

        $expected = [
            [
                'content' => "Licence continuation\nApr 2016\n%s",
                'style' => 'danger',
                'data' => [
                    [
                        'type' => 'url',
                        'route' => 'dashboard',
                        'params' => [],
                        'linkText' => 'Update details',
                        'class' => 'js-modal-ajax'
                    ]
                ]
            ]
        ];

        $result = $this->sut->generateMarkerTypes(['continuation'], $data);

        $this->assertEquals(['continuation' => $expected], $result);
    }
}
