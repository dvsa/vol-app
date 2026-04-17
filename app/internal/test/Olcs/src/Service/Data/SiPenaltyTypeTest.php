<?php

declare(strict_types=1);

namespace OlcsTest\Service\Data;

use Common\Exception\DataServiceException;
use CommonTest\Common\Service\Data\AbstractDataServiceTestCase;
use Dvsa\Olcs\Transfer\Query\Si\SiPenaltyTypeListData as Qry;
use Mockery as m;
use Olcs\Service\Data\SiPenaltyType;
use PHPUnit\Framework\Attributes\DataProvider;

class SiPenaltyTypeTest extends AbstractDataServiceTestCase
{
    /** @var SiPenaltyType */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new SiPenaltyType($this->abstractDataServiceServices);
    }

    public function testFormatData(): void
    {
        $this->assertEquals(self::SINGLE_EXPECTED_WITH_ID, $this->sut->formatData(self::SINGLE_SOURCE));
    }

    #[DataProvider('provideFetchListOptions')]
    public function testFetchListOptions(mixed $input, mixed $expected): void
    {
        $this->sut->setData('SiPenaltyType', $input);

        $this->assertEquals($expected, $this->sut->fetchListOptions(''));
    }

    public static function provideFetchListOptions(): array
    {
        return [
            [self::SINGLE_SOURCE, self::SINGLE_EXPECTED_WITH_ID],
            [false, []]
        ];
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
}
