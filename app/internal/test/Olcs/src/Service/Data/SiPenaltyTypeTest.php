<?php

namespace OlcsTest\Service\Data;

use Common\Service\Entity\Exceptions\UnexpectedResponseException;
use CommonTest\Service\Data\AbstractDataServiceTestCase;
use Mockery as m;
use Olcs\Service\Data\SiPenaltyType;

/**
 * Class SiPenaltyType Test
 * @package CommonTest\Service
 */
class SiPenaltyTypeTest extends AbstractDataServiceTestCase
{
    public function testFormatData()
    {
        $source = $this->getSingleSource();
        $expected = $this->getSingleExpected();

        $sut = new SiPenaltyType();

        $this->assertEquals($expected, $sut->formatData($source));
    }

    /**
     * @dataProvider provideFetchListOptions
     */
    public function testFetchListOptions($input, $expected)
    {
        $sut = new SiPenaltyType();
        $sut->setData('SiPenaltyType', $input);

        $this->assertEquals($expected, $sut->fetchListOptions(''));
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
        $mockTransferAnnotationBuilder = m::mock()
            ->shouldReceive('createQuery')->once()->andReturn('query')
            ->once()
            ->getMock();

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getResult')
            ->andReturn($results)
            ->twice()
            ->getMock();

        $sut = new SiPenaltyType();
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $this->assertEquals($results['results'], $sut->fetchListData());
        $this->assertEquals($results['results'], $sut->fetchListData()); //ensure data is cached
    }

    /**
     * Test fetchListData with exception
     */
    public function testFetchListDataWithException()
    {
        $this->setExpectedException(UnexpectedResponseException::class);
        $mockTransferAnnotationBuilder = m::mock()
            ->shouldReceive('createQuery')->once()->andReturn('query')->getMock();

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(false)
            ->once()
            ->getMock();

        $sut = new SiPenaltyType();
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $sut->fetchListData([]);
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
