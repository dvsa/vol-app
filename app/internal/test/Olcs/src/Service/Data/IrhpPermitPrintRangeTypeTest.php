<?php

namespace OlcsTest\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Cqrs\Response;
use Common\Service\Helper\TranslationHelperService;
use CommonTest\Common\Service\Data\AbstractDataServiceTestCase;
use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrintRangeType;
use Olcs\Service\Data\IrhpPermitPrintRangeType;
use Mockery as m;

/**
 * Class IrhpPermitPrintRangeType Test
 */
class IrhpPermitPrintRangeTypeTest extends AbstractDataServiceTestCase
{
    /** @var IrhpPermitPrintRangeType */
    private $sut;

    /** @var TranslationHelperService */
    protected $translationHelper;

    /** @var Response */
    protected $response;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(ReadyToPrintRangeType::class))
            ->andReturn($this->query);

        $this->response = m::mock(Response::class);
        $this->mockHandleQuery($this->response);

        $this->translationHelper = m::mock(TranslationHelperService::class);
        $this->translationHelper->shouldReceive('translate')
            ->andReturnUsing(
                function ($text) {
                    return $text . '-translated';
                }
            );

        $this->sut = new IrhpPermitPrintRangeType(
            $this->abstractDataServiceServices,
            $this->translationHelper
        );
    }

    /**
     * @dataProvider dpTestFetchListOptions
     */
    public function testFetchListOptions($results, $expected)
    {
        $irhpPermitStockId = 100;
        $this->sut->setIrhpPermitStock($irhpPermitStockId);

        $this->response->shouldReceive('isOk')
            ->once()
            ->andReturn(true)
            ->shouldReceive('getResult')
            ->once()
            ->andReturn($results);

        $this->assertEquals($expected, $this->sut->fetchListOptions());
        $this->assertEquals($irhpPermitStockId, $this->sut->getIrhpPermitStock());
    }

    public function dpTestFetchListOptions()
    {
        return [
            'with data' => [
                'results' => [
                    'results' => [
                        'range.type.1',
                        'range.type.2',
                        'range.type.3',
                    ]
                ],
                'expected' => [
                    'range.type.1' => 'permits.irhp.range.type.range.type.1-translated',
                    'range.type.2' => 'permits.irhp.range.type.range.type.2-translated',
                    'range.type.3' => 'permits.irhp.range.type.range.type.3-translated',
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

        $this->response->shouldReceive('isOk')
            ->once()
            ->andReturn(false);

        $this->sut->fetchListOptions();
    }
}
