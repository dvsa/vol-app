<?php

namespace CommonTest\Common\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\SiCategoryType;
use Dvsa\Olcs\Transfer\Query\Si\SiCategoryTypeListData as Qry;
use Mockery as m;

/**
 * Class SiCategoryType Test
 * @package CommonTest\Service
 */
class SiCategoryTypeTest extends AbstractDataServiceTestCase
{
    /** @var SiCategoryType */
    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new SiCategoryType($this->abstractDataServiceServices);
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
        $this->sut->setData('SiCategoryType', $input);

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
            'sort'  => 'description',
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
            ->once()
            ->andReturn(true)
            ->shouldReceive('getResult')
            ->twice()
            ->andReturn($results)
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
            ->once()
            ->andReturn(false)
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
            ['id' => 'val-1', 'description' => 'Value 1'],
            ['id' => 'val-2', 'description' => 'Value 2'],
            ['id' => 'val-3', 'description' => 'Value 3'],
        ];
    }
}
