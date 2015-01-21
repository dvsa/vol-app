<?php
/**
 * TmApplicationOc Service test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\TmApplicationOc;
use Mockery as m;

/**
 * TmApplicationOc Service test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TmApplicationOcTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Mock $service
     */
    public $sut;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->sut = m::mock('\Olcs\Service\Data\TmApplicationOc')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    /**
     * Test getLicenceOperatingCentreService
     * @group tmApplicationOcTest
     */
    public function testGetLicenceOperatingCentreService()
    {
        $this->sut->setLicenceOperatingCentreService('service');
        $this->assertEquals('service', $this->sut->getLicenceOperatingCentreService());
    }

    /**
     * Test getTmApplicationId
     * @group tmApplicationOcTest
     */
    public function testGetTmApplicationId()
    {
        $this->sut->setTmApplicationId('appid');
        $this->assertEquals('appid', $this->sut->getTmApplicationId());
    }

    /**
     * Test getLicenceId
     * @group tmApplicationOcTest
     */
    public function testGetLicenceId()
    {
        $this->sut->setLicenceId('licid');
        $this->assertEquals('licid', $this->sut->getLicenceId());
    }

    /**
     * Test fetchListOptions
     * 
     * @group tmApplicationOcTest
     */
    public function testFetchListOptionsExist()
    {

        $this->sut->setTmApplicationId(1);
        $this->sut->setLicenceId(2);

        $bundle = [
            'children' => [
                'transportManagerApplication',
                'operatingCentre' => [
                    'children' => [
                        'address'
                    ]
                ]
            ]
        ];

        $params = [
            'limit' => 1000,
            'transportManagerApplication' => 1,
            'bundle' => json_encode($bundle)
        ];
        $tmAppOcs = [
            'Count' => 1,
            'Results' => [
                [
                    'transportManagerApplication' => [
                        'action' => 'A'
                    ],
                    'operatingCentre' => [
                        'id' => 1,
                        'address' => [
                            'addressLine1' => 'a1',
                            'addressLine2' => 'a2',
                            'town' => 'town',
                        ]
                    ]
                ]
            ]
        ];

        $this->sut
            ->shouldReceive('getRestClient')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('', $params)
                ->andReturn($tmAppOcs)
                ->getMock()
            );

        $licOcs = [
            'Count' => 1,
            'Results' => [
                [
                    'operatingCentre' => [
                        'id' => 2,
                        'address' => [
                            'addressLine1' => 'b1',
                            'addressLine2' => 'b2',
                            'town' => 'town',
                        ]
                    ]
                ]
            ]
        ];

        $mockLicenceOcService = m::mock()
            ->shouldReceive('getOperatingCentresForLicence')
            ->with(2)
            ->andReturn($licOcs)
            ->getMock();

        $this->sut->setLicenceOperatingCentreService($mockLicenceOcService);

        $ocs = [
            1 => 'a1, a2, town',
            2 => 'b1, b2, town',
        ];

        $this->assertEquals($ocs, $this->sut->fetchListOptions(null));
    }

    /**
     * Test fetchListOptions with empty results
     * 
     * @group tmApplicationOcTest
     */
    public function testFetchListOptionsEmpty()
    {

        $this->sut->setTmApplicationId(1);
        $this->sut->setLicenceId(2);

        $bundle = [
            'children' => [
                'transportManagerApplication',
                'operatingCentre' => [
                    'children' => [
                        'address'
                    ]
                ]
            ]
        ];

        $params = [
            'limit' => 1000,
            'transportManagerApplication' => 1,
            'bundle' => json_encode($bundle)
        ];
        $tmAppOcs = [
            'Count' => 0,
            'Results' => []
        ];

        $this->sut
            ->shouldReceive('getRestClient')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('', $params)
                ->andReturn($tmAppOcs)
                ->getMock()
            );

        $licOcs = [
            'Count' => 0,
            'Results' => []
        ];

        $mockLicenceOcService = m::mock()
            ->shouldReceive('getOperatingCentresForLicence')
            ->with(2)
            ->andReturn($licOcs)
            ->getMock();

        $this->sut->setLicenceOperatingCentreService($mockLicenceOcService);

        $ocs = [];

        $this->assertEquals($ocs, $this->sut->fetchListOptions(null));
    }
}
