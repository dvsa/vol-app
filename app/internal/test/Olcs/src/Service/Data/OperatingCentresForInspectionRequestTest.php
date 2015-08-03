<?php
/**
 * OperatingCentresForInspectionRequest Service test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace OlcsTest\Service\Data;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use OlcsTest\Bootstrap;

/**
 * OperatingCentresForInspectionRequest Service test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class OperatingCentresForInspectionRequestTest extends MockeryTestCase
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
        $this->markTestSkipped();
        $this->sut = m::mock('\Olcs\Service\Data\OperatingCentresForInspectionRequest')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $this->sm = Bootstrap::getServiceManager();
        $this->sut->setServiceLocator($this->sm);
    }

    /**
     * Test get / set type
     *
     * @group operatingCentresForInspectionRequest
     */
    public function testGetSetType()
    {
        $this->sut->setType('application');
        $this->assertEquals('application', $this->sut->getType());
    }

    /**
     * Test get / set identifier
     *
     * @group operatingCentresForInspectionRequest
     */
    public function testGetSetIdentifier()
    {
        $this->sut->setIdentifier(1);
        $this->assertEquals(1, $this->sut->getIdentifier());
    }

    /**
     * Test fetch list options
     *
     * @dataProvider providerListOptions
     * @group operatingCentresForInspectionRequest1
     */
    public function testFetchListOptions($type, $service, $data, $expected)
    {
        $identifier = 1;
        $this->sut->setType($type);
        $this->sut->setIdentifier(1);

        if ($type === 'licence') {
            $this->sm->setService(
                $service,
                m::mock()
                ->shouldReceive('getAllForInspectionRequest')
                ->with($identifier)
                ->andReturn($data)
                ->getMock()
            );
        } else {
            $this->sm->setService(
                $service,
                m::mock()
                ->shouldReceive('getForSelect')
                ->with(1)
                ->andReturn($data)
                ->getMock()
            );
        }

        $options = $this->sut->fetchListOptions('');
        $this->assertEquals($options, $expected);
    }

    /**
     * Data provider
     */
    public function providerListOptions()
    {
        return [
            [
                'application',
                'Entity\ApplicationOperatingCentre',
                [
                    1 => 'line1, line2, town'
                ],
                [
                    1 => 'line1, line2, town'
                ]
            ],
            [
                'licence',
                'Entity\LicenceOperatingCentre',
                [
                    'Results'  => [
                        [
                            'operatingCentre' => [
                                'id' => 2,
                                'address' => [
                                    'addressLine1' => 'line3',
                                    'addressLine2' => 'line4',
                                    'town' => 'town1'
                                ]
                            ],
                        ]
                    ]
                ],
                [
                    2 => 'line3, line4, town1'
                ]
            ],
            [
                'licence',
                'Entity\LicenceOperatingCentre',
                [
                    'Results'  => []
                ],
                []
            ],
        ];
    }
}
