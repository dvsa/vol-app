<?php

declare(strict_types=1);

namespace OlcsTest\Service\Data;

use Common\Exception\DataServiceException;
use Olcs\Service\Data\IrfoCountry;
use Mockery as m;
use Dvsa\Olcs\Transfer\Query\Irfo\IrfoCountryList as Qry;
use CommonTest\Common\Service\Data\AbstractDataServiceTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class IrfoCountryTest extends AbstractDataServiceTestCase
{
    /** @var IrfoCountry */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new IrfoCountry($this->abstractDataServiceServices);
    }

    public function testFormatData(): void
    {
        $this->assertEquals(self::SINGLE_EXPECTED, $this->sut->formatData(self::SINGLE_SOURCE));
    }

    #[DataProvider('provideFetchListOptions')]
    public function testFetchListOptions(mixed $input, mixed $expected): void
    {
        $this->sut->setData('IrfoCountry', $input);

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
        $this->assertEquals($results['results'], $this->sut->fetchListData());  //ensure data is cached
    }

    public function testFetchLicenceDataWithException(): void
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
            [self::SINGLE_SOURCE, self::SINGLE_EXPECTED],
            [false, []]
        ];
    }
}
