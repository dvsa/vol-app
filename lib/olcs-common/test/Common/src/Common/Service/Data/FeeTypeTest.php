<?php

declare(strict_types=1);

namespace CommonTest\Common\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\FeeType;
use Dvsa\Olcs\Transfer\Query\FeeType\GetDistinctList as Qry;
use Mockery as m;

/**
 * Class Fee Type Test
 * @package CommonTest\Service
 */
final class FeeTypeTest extends AbstractDataServiceTestCase
{
    /** @var FeeType */
    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->sut = new FeeType($this->abstractDataServiceServices);
    }

    public function testFormatData(): void
    {
        $source = self::getSingleSource();
        $expected = self::getSingleExpected();

        $this->assertEquals($expected, $this->sut->formatData($source));
    }

    /**
     * @param $input
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideFetchListOptions')]
    public function testFetchListOptions($input, $expected): void
    {
        $this->sut->setData('FeeType', $input);

        $this->assertEquals($expected, $this->sut->fetchListOptions(''));
    }

    /**
     * @return \Iterator<(int | string), array<(array<mixed> | false)>>
     *
     * @psalm-return list{list{array, array}, list{false, array<never, never>}}
     */
    public static function provideFetchListOptions(): \Iterator
    {
        yield [self::getSingleSource(), self::getSingleExpected()];
        yield [false, []];
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
    }

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

    /**
     * @return array
     */
    protected static function getSingleExpected()
    {
        return [
            'FEETYPE1' => 'FEETYPE1',
            'FEETYPE2' => 'FEETYPE2',
            'FEETYPE3' => 'FEETYPE3',
        ];
    }

    /**
     * @return array
     */
    protected static function getSingleSource()
    {
        return [
            ['id' => 'FEETYPE1'],
            ['id' => 'FEETYPE2'],
            ['id' => 'FEETYPE3'],
        ];
    }
}
