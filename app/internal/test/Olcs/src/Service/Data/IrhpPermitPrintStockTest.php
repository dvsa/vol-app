<?php

namespace OlcsTest\Service\Data;

use Common\Exception\DataServiceException;
use Common\RefData;
use Common\Service\Cqrs\Response;
use Common\Service\Helper\TranslationHelperService;
use CommonTest\Common\Service\Data\AbstractDataServiceTestCase;
use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrintStock as Qry;
use Olcs\Service\Data\IrhpPermitPrintStock;
use Mockery as m;

/**
 * Class IrhpPermitPrintStock Test
 */
class IrhpPermitPrintStockTest extends AbstractDataServiceTestCase
{
    /** @var IrhpPermitPrintStock */
    private $sut;

    /** @var TranslationHelperService */
    protected $translationHelper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->translationHelper = m::mock(TranslationHelperService::class);
        $this->translationHelper->shouldReceive('translate')
            ->andReturnUsing(
                fn($text) => $text . '-translated'
            );

        $this->sut = new IrhpPermitPrintStock(
            $this->abstractDataServiceServices,
            $this->translationHelper
        );
    }

    /**
     * @dataProvider dpTestFetchListOptions
     */
    public function testFetchListOptions($country, $results, $expected)
    {
        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturnUsing(
                function (Qry $dto) use ($country) {
                    $this->assertEquals(RefData::IRHP_BILATERAL_PERMIT_TYPE_ID, $dto->getIrhpPermitType());
                    $this->assertEquals($country, $dto->getCountry());
                    return $this->query;
                }
            );

        $mockResponse = m::mock(Response::class)
            ->shouldReceive('isOk')
            ->once()
            ->andReturn(true)
            ->shouldReceive('getResult')
            ->once()
            ->andReturn($results)
            ->getMock();

        $this->sut->setIrhpPermitType(RefData::IRHP_BILATERAL_PERMIT_TYPE_ID);
        $this->sut->setCountry($country);

        $this->mockHandleQuery($mockResponse);

        $this->assertEquals($expected, $this->sut->fetchListOptions(null));
        $this->assertEquals(RefData::IRHP_BILATERAL_PERMIT_TYPE_ID, $this->sut->getIrhpPermitType());
        $this->assertEquals($country, $this->sut->getCountry());
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
