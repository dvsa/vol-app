<?php
/**
 * PSV Disc Service Unit Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace AdminTest\Service\Data;

/**
 * PSV Sequence Service Unit Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

/**
 * Class Test
 * @package AdminTest\Service\Data
 */
class PsvDiscTest extends AbstractDataServiceTest
{

    public $serviceName = '\Admin\Service\Data\PsvDisc';

    public $niFlag = 'N';

    public $goodsOrPsv = 'lcat_gv';

    public $licenceType = 'ltyp_r';

    public $trafficArea = 'K';

    public $deletedDate = '2014-01-01';

    /**
     * Test get bundle method
     * @group psvDisc
     */
    public function testGetBundle()
    {
        $bundle = $this->service->getBundle();
        $this->assertInternalType('array', $bundle);
        $this->assertEquals(count($bundle) > 0, true);
    }

    /**
     * Test get discs to print
     * @dataProvider getDiscsToPrintWithResultsProvider
     * @group psvDisc
     */
    public function testGetDiscsToPrint($licenceType, $discPrefix, $expected)
    {
        $this->niFlag = 'N';
        $this->licenceType = $expected['licenceType'];
        $this->trafficArea = $expected['trafficArea'];
        $this->goodsOrPsv = $expected['goodsOrPsv'];
        $discsToPrint = $this->service->getDiscsToPrint($licenceType, $discPrefix);
        $this->assertEquals(is_array($discsToPrint), true);
        $this->assertEquals(count($discsToPrint), 1);
        $this->assertEquals(isset($discsToPrint[0]['id']), true);
    }

    /**
     * Data provider for testGetDiscsToPrint
     * @group psvDisc
     */
    public function getDiscsToPrintWithResultsProvider()
    {
        return [
            ['ltyp_r', 'OB', ['licenceType' => 'ltyp_r', 'trafficArea' => 'B', 'goodsOrPsv' => 'lcat_psv']],
            ['ltyp_sn', 'OK', ['licenceType' => 'ltyp_sn', 'trafficArea' => 'K', 'goodsOrPsv' => 'lcat_psv']],
        ];
    }

    /**
     * Test testSetIsPrintingOn
     * @group psvDisc
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
     * @group psvDisc
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
     * @group psvDisc
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
