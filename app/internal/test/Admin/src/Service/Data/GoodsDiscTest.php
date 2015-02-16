<?php
/**
 * Goods Disc Service Unit Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace AdminTest\Service\Data;

/**
 * Goods Sequence Service Unit Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

/**
 * Class Test
 * @package AdminTest\Service\Data
 */
class GoodsDiscTest extends AbstractDataServiceTest
{

    public $serviceName = '\Admin\Service\Data\GoodsDisc';

    public $niFlag = 'N';

    public $goodsOrPsv = 'lcat_gv';

    public $licenceType = 'ltyp_r';

    public $trafficArea = 'K';

    public $deletedDate = '2014-01-01';

    /**
     * Test get bundle method
     * @group goodsDisc
     */
    public function testGetBundle()
    {
        $bundle = $this->service->getBundle();
        $this->assertInternalType('array', $bundle);
        $this->assertEquals(count($bundle) > 0, true);
    }

    /**
     * Test get discs to print with bad params
     * @expectedException \Exception
     * @dataProvider getDiscsToPrintWithBadParamsProvider
     * @group goodsDisc
     */
    public function testGetDiscsToPrintWithBadParams($niFlag, $operatorType, $licenceType, $discPrefix)
    {
        $this->service->getDiscsToPrint($niFlag, $operatorType, $licenceType, $discPrefix);
    }

    /**
     * Data provider for testGetDiscsToPrintWithBadParams
     * @group goodsDisc
     */
    public function getDiscsToPrintWithBadParamsProvider()
    {
        return [
            [null, null, null, null],
            ['N', null, null, null],
            ['N', 'lcat_gv', null, null],
            ['N', 'lcat_gv', 'ltyp_r', null],
            ['N', 'lcat_gv', 'ltyp_r', 'X']
        ];
    }

    /**
     * Test get discs to print
     * @dataProvider getDiscsToPrintWithResultsProvider
     * @group goodsDisc
     */
    public function testGetDiscsToPrint($niFlag, $operatorType, $licenceType, $discPrefix, $expected)
    {
        $this->niFlag = $expected['niFlag'];
        $this->licenceType = $expected['licenceType'];
        $this->trafficArea = $expected['trafficArea'];
        $discsToPrint = $this->service->getDiscsToPrint($niFlag, $operatorType, $licenceType, $discPrefix);
        $this->assertEquals(is_array($discsToPrint), true);
        $this->assertEquals(count($discsToPrint), 1);
        $this->assertEquals(isset($discsToPrint[0]['id']), true);
    }

    /**
     * Data provider for testGetDiscsToPrint
     * @group goodsDisc
     */
    public function getDiscsToPrintWithResultsProvider()
    {
        return [
            ['Y', null, 'ltyp_r', 'OK', ['niFlag' => 'Y', 'licenceType' => 'ltyp_r', 'trafficArea' => 'N']],
            ['N', 'lcat_gv', 'ltyp_r', 'OK', ['niFlag' => 'N', 'goodsOrPsv' => 'lcat_gv','licenceType' => 'ltyp_r',
            'trafficArea' => 'K']],
        ];
    }

    /**
     * Test testSetIsPrintingOn
     * @group goodsDisc
     */
    public function testSetIsPrintingOn()
    {
        $this->mockRestClient->expects($this->once())
                ->method('put')
                ->with($this->equalTo('/1'), $this->equalTo(['data' => '{"isPrinting":"Y","version":1}']))
                ->will($this->returnValue([]));
        $this->service->setIsPrintingOn([['id' => 1, 'version' => 1]]);
    }

    /**
     * Test testSetIsPrintingOff
     * @group goodsDisc
     */
    public function testSetIsPrintingOff()
    {
        $this->mockRestClient->expects($this->once())
                ->method('put')
                ->with($this->equalTo('/1'), $this->equalTo(['data' => '{"isPrinting":"N","version":1}']))
                ->will($this->returnValue([]));
        $this->service->setIsPrintingOff([['id' => 1, 'version' => 1]]);
    }

    /**
     * Test testSetIsPrintingOffAndAssignNumber
     * @group goodsDisc
     */
    public function testSetIsPrintingOffAndAssignNumber()
    {
        $this->mockRestClient->expects($this->once())
                ->method('put')
                ->with(
                    $this->equalTo('/1'),
                    $this->equalTo(
                        ['data' => '{"isPrinting":"N","issuedDate":"' .
                                strftime("%Y-%m-%d %H:%M:%S") . '","version":1,"discNo":1}']
                    )
                )
                ->will($this->returnValue([]));
        $this->service->setIsPrintingOffAndAssignNumber([['id' => 1, 'version' => 1]], 1);
    }

    /**
     * Mock rest call get method
     * 
     * @param string|array $path
     * @param array $data
     * @return array
     */
    public function mockRestCallGet($path, $data = [])
    {
        $retv = [];

        $bundle = json_encode(
            [
                'properties' => ['id', 'version'],
                'children' => [
                    'licenceVehicle' => [
                        'properties' => ['id'],
                        'children' => [
                            'licence' => [
                                'properties' => ['id', 'niFlag'],
                                'children' => [
                                    'goodsOrPsv' => [
                                        'properties' => ['id']
                                    ],
                                    'licenceType' => [
                                        'properties' => ['id']
                                    ],
                                    'trafficArea' => [
                                        'properties' => ['id']
                                    ],
                                ]
                            ],
                            'vehicle' => [
                                'properties' => [
                                    'id',
                                    'deletedDate'
                                ]
                            ]
                        ]
                    ],
                ]
            ]
        );
        // get discs to print, 1st page
        if (isset($data['bundle']) && $data['bundle'] == $bundle && $path == '' && $data['page'] == 1) {
            $retv = [
                'Count' => 1,
                'Results' => [[
                    'id' => 1,
                    'version' => 1,
                    'licenceVehicle' => [
                        'id' => 1,
                        'licence' => [
                            'id' => 1,
                            'niFlag' => $this->niFlag,
                            'goodsOrPsv' => [
                                'id' => $this->goodsOrPsv
                            ],
                            'licenceType' => [
                                'id' => $this->licenceType
                            ],
                            'trafficArea' => [
                                'id' => $this->trafficArea
                            ]
                        ],
                        'vehicle' => [
                            'id' => 1,
                            'deletedDate' => $this->deletedDate
                        ]
                    ],
                ]]
            ];
        }
        // get discs to print, 2nd empty page
        if (isset($data['bundle']) && $data['bundle'] == $bundle && $path == '' && $data['page'] == 2) {
            $retv = [
                'Count' => 1,
                'Results' => []
            ];
        }

        return $retv;
    }

    /**
     * Mock rest call put method
     * 
     * @param string|array $path
     * @param array $data
     * @return array
     */
    public function mockRestCallPut($path, $data = [])
    {
        $retv = [];
        return $retv;
    }
}
