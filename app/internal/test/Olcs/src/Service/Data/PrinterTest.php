<?php

/**
 * Printer Data Service Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Service\Data;

use Common\Exception\DataServiceException;
use Olcs\Service\Data\Printer;
use Mockery as m;
use Dvsa\Olcs\Transfer\Query\Printer\PrinterList as Qry;
use CommonTest\Common\Service\Data\AbstractDataServiceTestCase;

/**
 * Printer Data Service Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PrinterTest extends AbstractDataServiceTestCase
{
    /** @var Printer */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new Printer($this->abstractDataServiceServices);
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
            ->once()
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->assertEquals($results['results'], $this->sut->fetchListData());
    }

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

    public function testFetchListOptionsEmpty()
    {
        $this->sut->setData('Printer', []);
        $this->assertEquals([], $this->sut->fetchListOptions(null));
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

        $this->sut->setData('Printer', $data);
        $this->assertEquals($expected, $this->sut->fetchListOptions(null));
    }
}
