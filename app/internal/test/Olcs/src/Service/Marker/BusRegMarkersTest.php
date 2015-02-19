<?php

namespace OlcsTest\Service\Marker;

use Olcs\Service\Marker\BusRegMarkers;

/**
 * Class BusRegMarkersTest
 * @package OlcsTest\Service\Marker
 */
class BusRegMarkersTest extends \PHPUnit_Framework_TestCase
{
    protected $sut;

    public function setUp()
    {
        $this->sut = new BusRegMarkers();
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

    public function testSetAndGetBusReg()
    {
        $input = ['foo'];
        $this->sut->setBusReg($input);

        $this->assertEquals($this->sut->getBusReg(), $input);
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
     * Data provider for marker data.
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
                ['markerTypes' => ['sometypethatdoesntexist'], 'data' => ['busReg' => ['id' => 1]]],
                ['typeCount' => 0]
            ],
            [
                // input no data
                [
                    'markerTypes' => ['busReg'],
                    'data' => []
                ],
                // expected one type count and no markers as BusReg not set
                ['typeCount' => 1, 'markerCount' => []]
            ],
            [
                // input BusReg without shortNoticeRefused data no markers expected
                [
                    'markerTypes' => ['busReg'],
                    'data' => [
                        'busReg' => [
                            'id' => 1
                        ]
                    ]
                ],
                // expected one type count and no markers as shortNoticeRefused not set
                ['typeCount' => 1, 'markerCount' => []]
            ],
            [
                // input BusReg with shortNoticeRefused set to 'N' data no markers expected
                [
                    'markerTypes' => ['busReg'],
                    'data' => [
                        'busReg' => [
                            'id' => 1,
                            'shortNoticeRefused' => 'N'
                        ]
                    ]
                ],
                // expected
                ['typeCount' => 1, 'markerCount' => []]
            ],
            [
                // input BusReg with shortNoticeRefused set to 'Y' data 1 marker expected
                [
                    'markerTypes' => ['busReg'],
                    'data' => [
                        'busReg' => [
                            'id' => 1,
                            'shortNoticeRefused' => 'Y'
                        ]
                    ]
                ],
                // expected
                ['typeCount' => 1, 'markerCount' => ['busReg' => 1]]
            ]
        ];
    }
}
