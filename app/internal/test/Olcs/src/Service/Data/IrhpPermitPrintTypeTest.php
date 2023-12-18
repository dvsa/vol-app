<?php

namespace OlcsTest\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Cqrs\Response;
use CommonTest\Common\Service\Data\AbstractDataServiceTestCase;
use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrintType as Qry;
use Olcs\Service\Data\IrhpPermitPrintType;
use Mockery as m;

/**
 * Class IrhpPermitPrintType Test
 */
class IrhpPermitPrintTypeTest extends AbstractDataServiceTestCase
{
    /** @var IrhpPermitPrintType */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new IrhpPermitPrintType($this->abstractDataServiceServices);
    }

    /**
     * @dataProvider dpTestFetchListOptions
     */
    public function testFetchListOptions($results, $expected)
    {
        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturn($this->query);

        $mockResponse = m::mock(Response::class)
            ->shouldReceive('isOk')
            ->once()
            ->andReturn(true)
            ->shouldReceive('getResult')
            ->once()
            ->andReturn($results)
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->assertEquals($expected, $this->sut->fetchListOptions(null));
    }

    public function dpTestFetchListOptions()
    {
        return [
            'with data' => [
                'results' => [
                    'results' => [
                        [
                            'id' => 1,
                            'name' => [
                                'description' => 'name 1'
                            ],
                        ],
                        [
                            'id' => 2,
                            'name' => [
                                'description' => 'name 2'
                            ],
                        ],
                        [
                            'id' => 3,
                            'name' => [
                                'description' => 'name 3'
                            ],
                        ],
                    ]
                ],
                'expected' => [
                    1 => 'name 1',
                    2 => 'name 2',
                    3 => 'name 3',
                ]
            ],
            'no data' => [
                'results' => null,
                'expected' => []
            ]
        ];
    }

    public function testFetchListOptionsWithException()
    {
        $this->expectException(DataServiceException::class);

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturn($this->query);

        $mockResponse = m::mock(Response::class)
            ->shouldReceive('isOk')
            ->once()
            ->andReturn(false)
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->sut->fetchListOptions(null);
    }
}
