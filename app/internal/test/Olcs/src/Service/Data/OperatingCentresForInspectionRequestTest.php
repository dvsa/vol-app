<?php

namespace OlcsTest\Service\Data;

use CommonTest\Service\Data\AbstractDataServiceTestCase;
use Mockery as m;
use Olcs\Service\Data\OperatingCentresForInspectionRequest;

/**
 * OperatingCentresForInspectionRequest Service test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class OperatingCentresForInspectionRequestTest extends AbstractDataServiceTestCase
{
    /**
     * @var Mock $service
     */
    public $sut;

    /**
     * Set up
     */
    public function setUp()
    {
        $this->sut = new OperatingCentresForInspectionRequest();
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
    public function testFetchListOptions($data, $expected)
    {
        $this->sut->setData('OperatingCentres', $data);

        $this->assertEquals($expected, $this->sut->fetchListOptions(''));
    }

    /**
     * Data provider
     */
    public function providerListOptions()
    {
        return [
            [
                [
                    'results'  => [
                        [
                            'id' => 2,
                            'address' => [
                                'addressLine1' => 'line3',
                                'addressLine2' => 'line4',
                                'town' => 'town1'
                            ]
                        ]
                    ]
                ],
                [
                    2 => 'line3, line4, town1'
                ]
            ],
            [
                [
                    'results'  => []
                ],
                []
            ],
        ];
    }

    public function testFetchListData()
    {
        $results = ['results' => 'results'];
        $mockTransferAnnotationBuilder = m::mock()
            ->shouldReceive('createQuery')->once()->andReturn('query')
            ->once()
            ->getMock();

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->once()
            ->andReturn(true)
            ->shouldReceive('getResult')
            ->andReturn($results)
            ->getMock();

        $this->mockHandleQuery($this->sut, $mockTransferAnnotationBuilder, $mockResponse);

        $this->assertEquals($results, $this->sut->fetchListData());
        $this->assertEquals($results, $this->sut->fetchListData());  //ensure data is cached
    }

    public function testFetchLicenceDataWithError()
    {
        $mockTransferAnnotationBuilder = m::mock()
            ->shouldReceive('createQuery')->once()->andReturn('query')->getMock();

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->once()
            ->andReturn(false)
            ->getMock();

        $this->mockHandleQuery($this->sut, $mockTransferAnnotationBuilder, $mockResponse);

        $this->mockServiceLocator->shouldReceive('get')
            ->with('Helper\FlashMessenger')
            ->andReturn(
                m::mock()
                    ->shouldReceive('addErrorMessage')
                    ->with('unknown-error')
                    ->once()
                    ->getMock()
            );

        $this->sut->fetchListData();
    }
}
