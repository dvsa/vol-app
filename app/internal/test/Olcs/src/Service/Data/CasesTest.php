<?php

namespace OlcsTest\Service\Data;

use Common\Exception\ResourceNotFoundException;
use CommonTest\Common\Service\Data\AbstractDataServiceTestCase;
use Dvsa\Olcs\Transfer\Query\Cases\Cases as Qry;
use Mockery as m;
use Olcs\Service\Data\Cases;

/**
 * Class CasesTest
 * @package OlcsTest\Service\Data
 */
class CasesTest extends AbstractDataServiceTestCase
{
    /** @var Cases */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new Cases($this->abstractDataServiceServices);
    }

    public function testFetchData()
    {
        $id = 123;
        $caseData = ['id' => $id];

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturn($this->query);

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getResult')
            ->andReturn($caseData)
            ->once()
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->assertEquals($caseData, $this->sut->fetchData($id));
        $this->assertEquals($caseData, $this->sut->fetchData($id)); //ensure data is cached
    }

    /**
     * Test fetchListData with exception
     */
    public function testFetchListDataWithException()
    {
        $this->expectException(ResourceNotFoundException::class);

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

        $this->sut->fetchData(123);
    }
}
