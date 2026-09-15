<?php

declare(strict_types=1);

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
final class IrhpPermitPrintRangeTypeTest extends AbstractDataServiceTestCase
{
    /** @var IrhpPermitPrintRangeType */
    private $sut;

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

        $translationHelper = m::mock(TranslationHelperService::class);
        $translationHelper->shouldReceive('translate')
            ->andReturnUsing(
                fn($text) => $text . '-translated'
            );

        $this->sut = new IrhpPermitPrintRangeType(
            $this->abstractDataServiceServices,
            $translationHelper
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestFetchListOptions')]
    public function testFetchListOptions(mixed $results, mixed $expected): void
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

    public static function dpTestFetchListOptions(): \Iterator
    {
        yield 'with data' => [
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
        ];
        yield 'no data' => [
            'results' => null,
            'expected' => []
        ];
    }

    public function testFetchListOptionsWithException(): void
    {
        $this->expectException(DataServiceException::class);

        $this->response->shouldReceive('isOk')
            ->once()
            ->andReturn(false);

        $this->sut->fetchListOptions();
    }
}
