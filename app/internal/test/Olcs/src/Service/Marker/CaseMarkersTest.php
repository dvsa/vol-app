<?php

namespace OlcsTest\Service\Marker;

use Olcs\Service\Marker\CaseMarkers;

/**
 * Class CaseMarkersTest
 * @package OlcsTest\Service\Data
 */
class CaseMarkersTest extends \PHPUnit_Framework_TestCase
{
    protected $sut;

    public function setUp()
    {
        $this->sut = new CaseMarkers();
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
            foreach ($expected['markerCount'] as $type => $count) {
                $this->assertArrayHasKey($type, $result);
                $this->assertEquals($expected['markerCount'][$type], count($result[$type]));
            }
        }
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
                            'licence' => 'foo'
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
                            'licence' => 'foo'
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
                            'licence' => 'foo'
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
                            'licence' => 'foo'
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
                            'licence' => 'foo'
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
                            ]
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
                            'licence' => 'foo'
                        ]
                    ]
                ],
                // expected no markers because appeal decision date is set
                ['typeCount' => 1, 'markerCount' => ['appeal' => 0]]
            ]
        ];
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
}
