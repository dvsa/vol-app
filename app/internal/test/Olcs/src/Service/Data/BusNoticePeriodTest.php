<?php

declare(strict_types=1);

namespace OlcsTest\Service\Data;

use Common\Exception\DataServiceException;
use Dvsa\Olcs\Transfer\Query\Bus\BusNoticePeriodList as Qry;
use Olcs\Service\Data\BusNoticePeriod;
use Mockery as m;
use CommonTest\Common\Service\Data\AbstractDataServiceTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class BusNoticePeriodTest extends AbstractDataServiceTestCase
{
    private BusNoticePeriod $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new BusNoticePeriod($this->abstractDataServiceServices);
    }

    public function testFormatData(): void
    {
        $this->assertEquals(
            self::SINGLE_EXPECTED,
            $this->sut->formatData(
                [
                    ['id' => 'val-1', 'noticeArea' => 'Value 1'],
                    ['id' => 'val-2', 'noticeArea' => 'Value 2'],
                    ['id' => 'val-3', 'noticeArea' => 'Value 3'],
                ],
            )
        );
    }

    /**
     * @param $input
     * @param $expected
     */
    #[DataProvider('provideFetchListOptions')]
    public function testFetchListOptions(mixed $input, mixed $expected): void
    {
        $this->sut->setData('BusNoticePeriod', $input);

        $this->assertEquals($expected, $this->sut->fetchListOptions(''));
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
            ->andReturn(true)
            ->once()
            ->shouldReceive('getResult')
            ->andReturn($results)
            ->twice()
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->assertEquals($results['results'], $this->sut->fetchListData());
        $this->assertEquals($results['results'], $this->sut->fetchListData()); //ensure data is cached
    }

    /**
     * Test fetchListData with exception
     */
    public function testFetchListDataWithException(): void
    {
        $this->expectException(DataServiceException::class);

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturn($this->query);

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(false)
            ->once()
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->sut->fetchListData();
    }

    public static function provideFetchListOptions(): array
    {
        return [
            [
                [
                    ['id' => 'val-1', 'noticeArea' => 'Value 1'],
                    ['id' => 'val-2', 'noticeArea' => 'Value 2'],
                    ['id' => 'val-3', 'noticeArea' => 'Value 3'],
                ],
                self::SINGLE_EXPECTED,
            ],
            [false, []]
        ];
    }
}
