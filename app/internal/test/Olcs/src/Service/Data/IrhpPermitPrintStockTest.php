<?php

namespace OlcsTest\Service\Data;

use Common\Service\Cqrs\Response;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;
use CommonTest\Service\Data\AbstractDataServiceTestCase;
use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrintStock;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder as TransferAnnotationBuilder;
use Olcs\Service\Data\IrhpPermitPrintStock;
use Mockery as m;

/**
 * Class IrhpPermitPrintStock Test
 */
class IrhpPermitPrintStockTest extends AbstractDataServiceTestCase
{
    /**
     * @dataProvider dpTestFetchListOptions
     */
    public function testFetchListOptions($results, $expected)
    {
        $mockTransferAnnotationBuilder = m::mock(TransferAnnotationBuilder::class)
            ->shouldReceive('createQuery')
            ->with(m::type(ReadyToPrintStock::class))
            ->once()
            ->andReturn('query')
            ->getMock();

        $mockResponse = m::mock(Response::class)
            ->shouldReceive('isOk')
            ->once()
            ->andReturn(true)
            ->shouldReceive('getResult')
            ->once()
            ->andReturn($results)
            ->getMock();

        $sut = new IrhpPermitPrintStock();

        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $this->assertEquals($expected, $sut->fetchListOptions(null));
    }

    public function dpTestFetchListOptions()
    {
        return [
            'with data' => [
                'results' => [
                    'results' => [
                        [
                            'id' => 1,
                            'validFrom' => '2019-01-01',
                            'validTo' => '2019-12-31',
                            'irhpPermitType' => [
                                'name' => [
                                    'description' => 'name 1'
                                ]
                            ],
                        ],
                        [
                            'id' => 2,
                            'validFrom' => '2019-07-01',
                            'validTo' => '2020-06-30',
                            'irhpPermitType' => [
                                'name' => [
                                    'description' => 'name 2'
                                ]
                            ],
                        ],
                        [
                            'id' => 3,
                            'validFrom' => '2020-01-01',
                            'validTo' => '2020-12-31',
                            'irhpPermitType' => [
                                'name' => [
                                    'description' => 'name 3'
                                ]
                            ],
                        ],
                    ]
                ],
                'expected' => [
                    1 => 'name 1 (2019-01-01 to 2019-12-31)',
                    2 => 'name 2 (2019-07-01 to 2020-06-30)',
                    3 => 'name 3 (2020-01-01 to 2020-12-31)',
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
        $this->expectException(UnexpectedResponseException::class);

        $mockTransferAnnotationBuilder = m::mock(TransferAnnotationBuilder::class)
            ->shouldReceive('createQuery')
            ->with(m::type(ReadyToPrintStock::class))
            ->once()
            ->andReturn('query')
            ->getMock();

        $mockResponse = m::mock(Response::class)
            ->shouldReceive('isOk')
            ->once()
            ->andReturn(false)
            ->getMock();

        $sut = new IrhpPermitPrintStock();

        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $sut->fetchListOptions(null);
    }
}
