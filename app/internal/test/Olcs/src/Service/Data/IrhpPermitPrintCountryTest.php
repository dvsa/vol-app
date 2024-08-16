<?php

namespace OlcsTest\Service\Data;

use Common\Exception\DataServiceException;
use Common\RefData;
use Common\Service\Cqrs\Response;
use CommonTest\Common\Service\Data\AbstractDataServiceTestCase;
use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrintCountry as Qry;
use Olcs\Service\Data\IrhpPermitPrintCountry;
use Mockery as m;

/**
 * Class IrhpPermitPrintCountry Test
 */
class IrhpPermitPrintCountryTest extends AbstractDataServiceTestCase
{
    /** @var IrhpPermitPrintCountry */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new IrhpPermitPrintCountry($this->abstractDataServiceServices);
    }

    /**
     * @dataProvider dpTestFetchListOptions
     */
    public function testFetchListOptions($results, $expected)
    {
        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturnUsing(
                function (Qry $dto) {
                    $this->assertEquals(RefData::IRHP_BILATERAL_PERMIT_TYPE_ID, $dto->getIrhpPermitType());
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

        $this->mockHandleQuery($mockResponse);

        $this->assertEquals($expected, $this->sut->fetchListOptions(null));
        $this->assertEquals(RefData::IRHP_BILATERAL_PERMIT_TYPE_ID, $this->sut->getIrhpPermitType());
    }

    public function dpTestFetchListOptions()
    {
        return [
            'with data' => [
                'results' => [
                    'results' => [
                        [
                            'id' => 'AB',
                            'countryDesc' => 'Country AB',
                        ],
                        [
                            'id' => 'CD',
                            'countryDesc' => 'Country CD',
                        ],
                        [
                            'id' => 'EF',
                            'countryDesc' => 'Country EF',
                        ],
                    ]
                ],
                'expected' => [
                    'AB' => 'Country AB',
                    'CD' => 'Country CD',
                    'EF' => 'Country EF',
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
