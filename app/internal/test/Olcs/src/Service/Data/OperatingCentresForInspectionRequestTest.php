<?php

declare(strict_types=1);

namespace OlcsTest\Service\Data;

use Common\Service\Helper\FlashMessengerHelperService;
use CommonTest\Common\Service\Data\AbstractDataServiceTestCase;
use Dvsa\Olcs\Transfer\Query\InspectionRequest\OperatingCentres as Qry;
use Mockery as m;
use Olcs\Service\Data\OperatingCentresForInspectionRequest;

/**
 * OperatingCentresForInspectionRequest Service test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class OperatingCentresForInspectionRequestTest extends AbstractDataServiceTestCase
{
    /** @var OperatingCentresForInspectionRequest */
    private $sut;

    /** @var FlashMessengerHelperService */
    protected $flashMessengerHelper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->flashMessengerHelper = m::mock(FlashMessengerHelperService::class);

        $this->sut = new OperatingCentresForInspectionRequest(
            $this->abstractDataServiceServices,
            $this->flashMessengerHelper
        );
    }

    /**
     * Test get / set type
     */
    #[\PHPUnit\Framework\Attributes\Group('operatingCentresForInspectionRequest')]
    public function testGetSetType(): void
    {
        $this->sut->setType('application');
        $this->assertEquals('application', $this->sut->getType());
    }

    /**
     * Test get / set identifier
     */
    #[\PHPUnit\Framework\Attributes\Group('operatingCentresForInspectionRequest')]
    public function testGetSetIdentifier(): void
    {
        $this->sut->setIdentifier(1);
        $this->assertEquals(1, $this->sut->getIdentifier());
    }

    /**
     * Test fetch list options
     */
    #[\PHPUnit\Framework\Attributes\Group('operatingCentresForInspectionRequest1')]
    #[\PHPUnit\Framework\Attributes\DataProvider('providerListOptions')]
    public function testFetchListOptions(mixed $data, mixed $expected): void
    {
        $this->sut->setData('OperatingCentres', $data);

        $this->assertEquals($expected, $this->sut->fetchListOptions(''));
    }

    /**
     * Data provider
     */
    public static function providerListOptions(): array
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

    public function testFetchListData(): void
    {
        $results = ['results' => 'results'];

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturn($this->query);

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->once()
            ->andReturn(true)
            ->shouldReceive('getResult')
            ->andReturn($results)
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->assertEquals($results, $this->sut->fetchListData());
        $this->assertEquals($results, $this->sut->fetchListData());  //ensure data is cached
    }

    public function testFetchLicenceDataWithError(): void
    {
        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturn($this->query);

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->once()
            ->andReturn(false)
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->flashMessengerHelper
            ->shouldReceive('addErrorMessage')
            ->with('unknown-error')
            ->once();

        $this->sut->fetchListData();
    }
}
