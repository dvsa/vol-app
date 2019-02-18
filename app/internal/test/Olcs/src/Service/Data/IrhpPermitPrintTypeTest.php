<?php

namespace OlcsTest\Service\Data;

use Common\Service\Cqrs\Response;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;
use CommonTest\Service\Data\AbstractDataServiceTestCase;
use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrintType;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder as TransferAnnotationBuilder;
use Olcs\Service\Data\IrhpPermitPrintType;
use Mockery as m;

/**
 * Class IrhpPermitPrintType Test
 */
class IrhpPermitPrintTypeTest extends AbstractDataServiceTestCase
{
    /**
     * @dataProvider dpTestFetchListOptions
     */
    public function testFetchListOptions($results, $expected)
    {
        $mockTransferAnnotationBuilder = m::mock(TransferAnnotationBuilder::class)
            ->shouldReceive('createQuery')
            ->with(m::type(ReadyToPrintType::class))
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

        $sut = new IrhpPermitPrintType();

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
        $this->expectException(UnexpectedResponseException::class);

        $mockTransferAnnotationBuilder = m::mock(TransferAnnotationBuilder::class)
            ->shouldReceive('createQuery')
            ->with(m::type(ReadyToPrintType::class))
            ->once()
            ->andReturn('query')
            ->getMock();

        $mockResponse = m::mock(Response::class)
            ->shouldReceive('isOk')
            ->once()
            ->andReturn(false)
            ->getMock();

        $sut = new IrhpPermitPrintType();

        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $sut->fetchListOptions(null);
    }
}
