<?php

namespace OlcsTest\Service\Data;

use Common\Exception\DataServiceException;
use CommonTest\Common\Service\Data\AbstractDataServiceTestCase;
use Dvsa\Olcs\Transfer\Query\Si\SiPenaltyTypeListData as Qry;
use Mockery as m;
use Olcs\Service\Data\SiPenaltyType;

/**
 * Class SiPenaltyType Test
 * @package CommonTest\Service
 */
class SiPenaltyTypeTest extends AbstractDataServiceTestCase
{
    /** @var SiPenaltyType */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new SiPenaltyType($this->abstractDataServiceServices);
    }

    public function testFormatData()
    {
        $source = $this->getSingleSource();
        $expected = $this->getSingleExpected();

        $this->assertEquals($expected, $this->sut->formatData($source));
    }

    /**
     * @dataProvider provideFetchListOptions
     */
    public function testFetchListOptions($input, $expected)
    {
        $this->sut->setData('SiPenaltyType', $input);

        $this->assertEquals($expected, $this->sut->fetchListOptions(''));
    }

    public function provideFetchListOptions()
    {
        return [
            [$this->getSingleSource(), $this->getSingleExpected()],
            [false, []]
        ];
    }

    public function testFetchListData()
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
    public function testFetchListDataWithException()
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

    protected function getSingleExpected()
    {
        return [
            'val-1' => 'val-1 - Value 1',
            'val-2' => 'val-2 - Value 2',
            'val-3' => 'val-3 - Value 3',
        ];
    }

    protected function getSingleSource()
    {
        return [
            ['id' => 'val-1', 'description' => 'Value 1'],
            ['id' => 'val-2', 'description' => 'Value 2'],
            ['id' => 'val-3', 'description' => 'Value 3'],
        ];
    }
}
