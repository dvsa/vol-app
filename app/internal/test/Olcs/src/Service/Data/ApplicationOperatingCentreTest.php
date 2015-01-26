<?php
/**
 * ApplicationOperatingCentre Service test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace OlcsTest\Service\Data;

use Mockery as m;

/**
 * ApplicationOperatingCentre Service test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationOperatingCentreTest extends \PHPUnit_Framework_TestCase
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
        $this->sut = m::mock('\Olcs\Service\Data\ApplicationOperatingCentre')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    /**
     * Test getLicenceOperatingCentreService
     * @group ApplicationOcTest
     */
    public function testGetLicenceOperatingCentreService()
    {
        $this->sut->setLicenceOperatingCentreService('service');
        $this->assertEquals('service', $this->sut->getLicenceOperatingCentreService());
    }

    /**
     * Test getApplicationId
     * @group ApplicationOcTest
     */
    public function testGetApplicationId()
    {
        $this->sut->setApplicationId('appid');
        $this->assertEquals('appid', $this->sut->getApplicationId());
    }

    /**
     * Test getLicenceId
     * @group ApplicationOcTest
     */
    public function testGetLicenceId()
    {
        $this->sut->setLicenceId('licid');
        $this->assertEquals('licid', $this->sut->getLicenceId());
    }

    /**
     * Test fetchListOptions
     * 
     * @group ApplicationOcTest
     */
    public function testFetchListOptionsExist()
    {

        $this->sut->setapplicationId(1);
        $this->sut->setLicenceId(2);

        $bundle = [
            'children' => [
                'application',
                'operatingCentre' => [
                    'children' => [
                        'address'
                    ]
                ]
            ]
        ];

        $params = [
            'limit' => 1000,
            'application' => 1,
            'bundle' => json_encode($bundle)
        ];
        $appOcs = [
            'Count' => 1,
            'Results' => [
                [
                    'action' => 'A',
                    'operatingCentre' => [
                        'id' => 1,
                        'address' => [
                            'addressLine1' => 'a1',
                            'addressLine2' => 'a2',
                            'town' => 'town',
                        ]
                    ]
                ],
                [
                    'action' => 'D',
                    'operatingCentre' => [
                        'id' => 2,
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
                ->andReturn($appOcs)
                ->getMock()
            );

        $licOcs = [
            'Count' => 1,
            'Results' => [
                [
                    'operatingCentre' => [
                        'id' => 2,
                        'address' => [
                            'addressLine1' => 'b2',
                            'addressLine2' => 'b2',
                            'town' => 'town',
                        ]
                    ],
                    'operatingCentre' => [
                        'id' => 3,
                        'address' => [
                            'addressLine1' => 'b3',
                            'addressLine2' => 'b3',
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
            3 => 'b3, b3, town',
        ];

        $this->assertEquals($ocs, $this->sut->fetchListOptions(null));
    }

    /**
     * Test fetchListOptions with empty results
     * 
     * @group ApplicationOcTest
     */
    public function testFetchListOptionsEmpty()
    {

        $this->sut->setApplicationId(1);
        $this->sut->setLicenceId(2);

        $bundle = [
            'children' => [
                'application',
                'operatingCentre' => [
                    'children' => [
                        'address'
                    ]
                ]
            ]
        ];

        $params = [
            'limit' => 1000,
            'application' => 1,
            'bundle' => json_encode($bundle)
        ];
        $appOcs = [
            'Count' => 0,
            'Results' => []
        ];

        $this->sut
            ->shouldReceive('getRestClient')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('', $params)
                ->andReturn($appOcs)
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
