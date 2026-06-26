<?php

namespace CommonTest\Common\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\FeeType;
use Dvsa\Olcs\Transfer\Query\FeeType\GetDistinctList as Qry;
use Mockery as m;

/**
 * Class Fee Type Test
 * @package CommonTest\Service
 */
class FeeTypeTest extends AbstractDataServiceTestCase
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
        $source = $this->getSingleSource();
        $expected = $this->getSingleExpected();

        $this->assertEquals($expected, $this->sut->formatData($source));
    }

    /**
     * @dataProvider provideFetchListOptions
     * @param $input
     * @param $expected
     */
    public function testFetchListOptions($input, $expected): void
    {
        $this->sut->setData('FeeType', $input);

        $this->assertEquals($expected, $this->sut->fetchListOptions(''));
    }

    /**
     * @return (array|false)[][]
     *
     * @psalm-return list{list{array, array}, list{false, array<never, never>}}
     */
    public function provideFetchListOptions(): array
    {
        return [
            [$this->getSingleSource(), $this->getSingleExpected()],
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
    protected function getSingleExpected()
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
    protected function getSingleSource()
    {
        return [
            ['id' => 'FEETYPE1'],
            ['id' => 'FEETYPE2'],
            ['id' => 'FEETYPE3'],
        ];
    }
}
