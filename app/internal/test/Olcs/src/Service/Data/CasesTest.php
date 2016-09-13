<?php

namespace OlcsTest\Service\Data;

use Common\Exception\ResourceNotFoundException;
use CommonTest\Service\Data\AbstractDataServiceTestCase;
use Mockery as m;
use Olcs\Service\Data\Cases;

/**
 * Class CasesTest
 * @package OlcsTest\Service\Data
 */
class CasesTest extends AbstractDataServiceTestCase
{
    public function testFetchData()
    {
        $id = 123;
        $caseData = ['id' => $id];

        $mockTransferAnnotationBuilder = m::mock()
            ->shouldReceive('createQuery')->once()->andReturn('query')
            ->once()
            ->getMock();

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getResult')
            ->andReturn($caseData)
            ->once()
            ->getMock();

        $sut = new Cases();
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $this->assertEquals($caseData, $sut->fetchData($id));
        $this->assertEquals($caseData, $sut->fetchData($id)); //ensure data is cached
    }

    /**
     * Test fetchListData with exception
     */
    public function testFetchListDataWithException()
    {
        $this->setExpectedException(ResourceNotFoundException::class);

        $mockTransferAnnotationBuilder = m::mock()
            ->shouldReceive('createQuery')->once()->andReturn('query')->getMock();

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(false)
            ->once()
            ->getMock();

        $sut = new Cases();
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $sut->fetchData(123);
    }
}
