<?php

namespace CommonTest\Common\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\TrafficArea;
use Mockery as m;
use Dvsa\Olcs\Transfer\Query\TrafficArea\TrafficAreaList as Qry;

/**
 * Class TrafficArea Test
 * @package CommonTest\Service
 */
class TrafficAreaTest extends AbstractDataServiceTestCase
{
    /** @var TrafficArea */
    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new TrafficArea($this->abstractDataServiceServices);
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
        $this->sut->setData('TrafficArea', $input);

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
        $params = [
            'sort'  => 'name',
            'order' => 'ASC',
        ];
        $dto = Qry::create($params);

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturnUsing(
                function ($dto) use ($params) {
                    $this->assertEquals($params['sort'], $dto->getSort());
                    $this->assertEquals($params['order'], $dto->getOrder());
                    return $this->query;
                }
            );

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
            'val-1' => 'Value 1',
            'val-2' => 'Value 2',
            'val-3' => 'Value 3',
        ];
    }

    /**
     * @return array
     */
    protected function getSingleSource()
    {
        return [
            ['id' => 'val-1', 'name' => 'Value 1'],
            ['id' => 'val-2', 'name' => 'Value 2'],
            ['id' => 'val-3', 'name' => 'Value 3'],
        ];
    }
}
