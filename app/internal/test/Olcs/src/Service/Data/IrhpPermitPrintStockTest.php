<?php

namespace OlcsTest\Service\Data;

use Common\Exception\DataServiceException;
use Common\RefData;
use Common\Service\Cqrs\Response;
use Common\Service\Helper\TranslationHelperService;
use CommonTest\Service\Data\AbstractDataServiceTestCase;
use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrintStock;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder as TransferAnnotationBuilder;
use Olcs\Service\Data\IrhpPermitPrintStock;
use Mockery as m;
use Laminas\ServiceManager\ServiceManager;

/**
 * Class IrhpPermitPrintStock Test
 */
class IrhpPermitPrintStockTest extends AbstractDataServiceTestCase
{
    /**
     * @dataProvider dpTestFetchListOptions
     */
    public function testFetchListOptions($country, $results, $expected)
    {
        $this->translationHelper = m::mock(TranslationHelperService::class);
        $this->translationHelper->shouldReceive('translate')
            ->andReturnUsing(
                function ($text) {
                    return $text . '-translated';
                }
            );

        $serviceLocator = m::mock(ServiceManager::class);
        $serviceLocator->shouldReceive('get')
            ->with('Helper\Translation')
            ->once()
            ->andReturn($this->translationHelper);

        $mockTransferAnnotationBuilder = m::mock(TransferAnnotationBuilder::class)
            ->shouldReceive('createQuery')
            ->once()
            ->andReturnUsing(
                function (ReadyToPrintStock $dto) use ($country) {
                    $this->assertEquals(RefData::IRHP_BILATERAL_PERMIT_TYPE_ID, $dto->getIrhpPermitType());
                    $this->assertEquals($country, $dto->getCountry());
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

        $sut = (new IrhpPermitPrintStock())->createService($serviceLocator);
        $sut->setIrhpPermitType(RefData::IRHP_BILATERAL_PERMIT_TYPE_ID);
        $sut->setCountry($country);

        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $this->assertEquals($expected, $sut->fetchListOptions(null));
        $this->assertEquals(RefData::IRHP_BILATERAL_PERMIT_TYPE_ID, $sut->getIrhpPermitType());
        $this->assertEquals($country, $sut->getCountry());
    }

    public function dpTestFetchListOptions()
    {
        return [
            'with validity dates' => [
                'country' => 'DE',
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
                'country' => 'DE',
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
            'morocco specific behaviour' => [
                'country' => 'MA',
                'results' => [
                    'results' => [
                        [
                            'id' => 1,
                            'periodNameKey' => 'stock.one.period.name.key',
                        ],
                        [
                            'id' => 2,
                            'periodNameKey' => 'stock.two.period.name.key',
                        ],
                        [
                            'id' => 3,
                            'periodNameKey' => 'stock.three.period.name.key',
                        ],
                    ]
                ],
                'expected' => [
                    1 => 'stock.one.period.name.key-translated',
                    2 => 'stock.two.period.name.key-translated',
                    3 => 'stock.three.period.name.key-translated',
                ]
            ],
            'no data' => [
                'country' => 'DE',
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
