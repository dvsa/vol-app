<?php

namespace OlcsTest\Service\Data;

use Common\Exception\DataServiceException;
use Common\RefData;
use Common\Service\Cqrs\Response;
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
            ->once()
            ->andReturnUsing(
                function (ReadyToPrintStock $dto) {
                    $this->assertEquals(RefData::IRHP_BILATERAL_PERMIT_TYPE_ID, $dto->getIrhpPermitType());
                    $this->assertEquals('DE', $dto->getCountry());
                    return 'query';
                }
            )
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
        $sut->setIrhpPermitType(RefData::IRHP_BILATERAL_PERMIT_TYPE_ID);
        $sut->setCountry('DE');

        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $this->assertEquals($expected, $sut->fetchListOptions(null));
        $this->assertEquals(RefData::IRHP_BILATERAL_PERMIT_TYPE_ID, $sut->getIrhpPermitType());
        $this->assertEquals('DE', $sut->getCountry());
    }

    public function dpTestFetchListOptions()
    {
        return [
            'with validity dates' => [
                'results' => [
                    'results' => [
                        [
                            'id' => 1,
                            'validFrom' => '2019-01-01',
                            'validTo' => '2019-12-31',
                        ],
                        [
                            'id' => 2,
                            'validFrom' => '2019-07-01',
                            'validTo' => '2020-06-30',
                        ],
                        [
                            'id' => 3,
                            'validFrom' => '2020-01-01',
                            'validTo' => '2020-12-31',
                        ],
                    ]
                ],
                'expected' => [
                    1 => '2019-01-01 to 2019-12-31',
                    2 => '2019-07-01 to 2020-06-30',
                    3 => '2020-01-01 to 2020-12-31',
                ]
            ],
            'without validity dates' => [
                'results' => [
                    'results' => [
                        [
                            'id' => 1,
                        ],
                        [
                            'id' => 2,
                        ],
                        [
                            'id' => 3,
                        ],
                    ]
                ],
                'expected' => [
                    1 => 'Stock 1',
                    2 => 'Stock 2',
                    3 => 'Stock 3',
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
