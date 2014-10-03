<?php
/**
 * Disc Sequence Service Unit Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace AdminTest\Service\Data;

/**
 * Disc Sequence Service Unit Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

/**
 * Class Test
 * @package AdminTest\Service\Data
 */
class DiscSequenceTest extends AbstractDataServiceTest
{

    public $trafficAreaId = 'K';

    public $goodsOrPsv = 'lcat_gv';

    public $version = 1;

    public $serviceName = '\Admin\Service\Data\DiscSequence';

    /**
     * Test get bundle method
     * @group discSequence
     */
    public function testGetBundle()
    {
        $bundle = $this->service->getBundle();
        $this->assertInternalType('array', $bundle);
        $this->assertEquals(count($bundle) > 0, true);
    }

    /**
     * Test fetch list options with bad params
     * @expectedException \Exception
     * @dataProvider fetchListOptionsProvider
     * @group discSequence
     */
    public function testFetchListOptionsWithBadParams($context)
    {
        $this->service->fetchListOptions($context);
    }

    /**
     * Data provider for testFetchListOptions
     * @group discSequence
     */
    public function fetchListOptionsProvider()
    {
        return [
            [null],
            [['niFl']],
            [['niFlag' => 'N']],
            [['niFlag' => 'N', 'goodsOrPsv' => 'lcat_gv']]
        ];
    }

    /**
     * Test fetch list options
     * @group discSequence
     */
    public function testFetchListOptions()
    {
        $options = $this->service->fetchListOptions(
            ['niFlag' => 'N', 'goodsOrPsv' => 'lcat_gv', 'licenceType' => 'ltyp_r']
        );
        $this->assertEquals(is_array($options), true);
        $this->assertEquals($options[1], 'OK');
    }

    /**
     * Test fetch list options - no results
     * @dataProvider fetchListOptionsNoResultsProvider
     * @group discSequence
     */
    public function testFetchListOptionsNoResults($context, $trafficAreaId, $goodsOrPsv)
    {
        $this->trafficAreaId = $trafficAreaId;
        $this->goodsOrPsv = $goodsOrPsv;
        $options = $this->service->fetchListOptions($context);
        $this->assertEquals(is_array($options), true);
        $this->assertEquals(count($options), 0);
    }

    /**
     * Data provider for testFetchListOptionsNoResults
     * @group discSequence
     */
    public function fetchListOptionsNoResultsProvider()
    {
        return [
            [['niFlag' => 'N', 'goodsOrPsv' => 'lcat_gv', 'licenceType' => 'ltyp_r'], null, 'lcat_gv'],
            [['niFlag' => 'N', 'goodsOrPsv' => 'lcat_gv', 'licenceType' => 'ltyp_r'], 'K', null],
            [['niFlag' => 'N', 'goodsOrPsv' => 'lcat_gv', 'licenceType' => 'ltyp_r'], 'N', 'lcat_gv'],
            [['niFlag' => 'Y', 'goodsOrPsv' => 'lcat_gv', 'licenceType' => 'ltyp_r'], 'K', 'lcat_gv'],
        ];
    }

    /**
     * Test get disc number
     * @group discSequence
     */
    public function testGetDiscNumber()
    {
        $discNumber = $this->service->getDiscNumber(1, 'ltyp_r');
        $this->assertEquals($discNumber, 1);
    }

    /**
     * Test get disc prefix
     * @group discSequence
     */
    public function testGetDiscPrefix()
    {
        $discPrefix = $this->service->getDiscPrefix(1, 'ltyp_r');
        $this->assertEquals($discPrefix, 'OK');
    }

    /**
     * Test get disc details with no params
     * @group discSequence
     */
    public function testGetDiscDetailsWithNoParams()
    {
        $discNumber = $this->service->getDiscNumber();
        $this->assertEquals($discNumber, false);
    }

    /**
     * Test new start number with bad params
     * @expectedException \Exception
     * @dataProvider setNewStartNumberProvider
     * @group discSequence
     */
    public function testSetNewStartNumberWithBadParams($licenceType, $discSequence, $startNumber)
    {
        $this->service->setNewStartNumber($licenceType, $discSequence, $startNumber);
    }

    /**
     * Data provider for testSetStartNumber
     * @group discSequence
     */
    public function setNewStartNumberProvider()
    {
        return [
            [null, null, null],
            ['ltyp_r', null, null],
            ['ltyp_r', 1, null]
        ];
    }

    /**
     * Test set new start number with bad response
     * @expectedException \Exception     
     * @group discSequence
     */
    public function testSetNewStartNumberWithBadResponse()
    {
        $this->version = null;
        $this->service->setNewStartNumber('ltyp_r', 1, 1);
    }

    /**
     * Test set new start number
     * @group discSequence
     */
    public function testSetNewStartNumber()
    {
        $result = $this->service->setNewStartNumber('ltyp_r', 1, 1);
        $this->assertEquals(is_array($result), true);
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
        // first parameter can be $data if there is no path provided
        if (is_array($path)) {
            $data = $path;
            $path = '';
        }

        $retv = [];
        $bundle = json_encode(
            [
                'properties' => 'ALL',
                'children' => [
                    'trafficArea' => [
                        'properties' => [
                            'id'
                        ]
                    ],
                    'goodsOrPsv' => [
                        'properties' => [
                            'id'
                        ]
                    ]
                ]
            ]
        );

        // fetch list options, 1st page
        if (isset($data['bundle']) && $data['bundle'] == $bundle && $path == '' && $data['page'] == 1) {
            $retv = [
                'Count' => 1,
                'Results' => [[
                    'id' => 1,
                    'rPrefix' => 'OK',
                    'snPrefix' => 'OK',
                    'siPrefix' => 'OK',
                    'trafficArea' => ['id' => $this->trafficAreaId],
                    'goodsOrPsv' => ['id' => $this->goodsOrPsv]
                ]]
            ];
        }
        // fetch list options, 2nd empty page
        if (isset($data['bundle']) && $data['bundle'] == $bundle && $path == '' && $data['page'] == 2) {
            $retv = [
                'Count' => 1,
                'Results' => []
            ];
        }

        $discNumberBundle = json_encode(
            [
                'properties' => [
                    'restricted'
                ]
            ]
        );
        // get disc number
        if (isset($data['bundle']) && $data['bundle'] == $discNumberBundle && $path == '') {
            $retv = [
                'restricted' => 1
            ];
        }

        $discPrefixBundle = json_encode(
            [
                'properties' => [
                    'rPrefix'
                ]
            ]
        );
        // get disc prefix
        if (isset($data['bundle']) && $data['bundle'] == $discPrefixBundle && $path == '') {
            $retv = [
                'rPrefix' => 'OK'
            ];
        }

        $startNumberBundle = json_encode(
            [
                'properties' => [
                    'version'
                ]
            ]
        );
        // get version for setting new start number
        if (isset($data['bundle']) && $data['bundle'] == $startNumberBundle && $path == '') {
            $retv = [
                'version' => $this->version
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
        return [];
    }
}
