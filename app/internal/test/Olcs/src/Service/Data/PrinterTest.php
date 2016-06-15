<?php

/**
 * Printer Data Service Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Service\Data;

use Olcs\Service\Data\Printer;
use Mockery as m;
use Dvsa\Olcs\Transfer\Query\Printer\GetList as Qry;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;
use CommonTest\Service\Data\AbstractDataServiceTestCase;

/**
 * Printer Data Service Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PrinterTest extends AbstractDataServiceTestCase
{
    public function testFetchListData()
    {
        $results = ['results' => 'results'];
        $mockTransferAnnotationBuilder = m::mock()
            ->shouldReceive('createQuery')
            ->once()
            ->andReturn('query')
            ->getMock();

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getResult')
            ->andReturn($results)
            ->once()
            ->getMock();

        $sut = new Printer();
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse, $results);

        $this->assertEquals($results['results'], $sut->fetchListData([]));
    }

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
        $sut = new Printer();
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse, []);

        $sut->fetchListData([]);
    }

    public function testFetchListOptionsEmpty()
    {
        $sut = new Printer();
        $sut->setData('Printer', []);
        $this->assertEquals([], $sut->fetchListOptions(null));
    }

    public function testFetchListOptions()
    {
        $data = [
            [
                'id' => 1,
                'printerName' => 'foo'
            ],
            [
                'id' => 2,
                'printerName' => 'cake'
            ]
        ];
        $expected = [
            1 => 'foo',
            2 => 'cake'
        ];
        $sut = new Printer();
        $sut->setData('Printer', $data);
        $this->assertEquals($expected, $sut->fetchListOptions(null));
    }
}
