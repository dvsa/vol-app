<?php

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\PublicInquiryReason;
use CommonTest\Service\Data\AbstractDataServiceTestCase;
use Mockery as m;

/**
 * Class PublicInquiryReasonTest
 * @package OlcsTest\Service\Data
 */
class PublicInquiryReasonTest extends AbstractDataServiceTestCase
{
    private $reasons = [
        ['id' => 12, 'sectionCode' => 'Section A', 'description' => 'Description 1'],
        ['id' => 15, 'sectionCode' => 'Section C', 'description' => 'Description 2'],
    ];

    public function testFetchListData()
    {
        $results = ['results' => $this->reasons];
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

        $sut = new PublicInquiryReason();
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse, $results);

        $this->assertEquals($results, $sut->fetchListData([]));
    }


    public function testFetchPublicInquiryReasonDataFailure()
    {
        $results = [];
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

        $sut = new PublicInquiryReason();
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse, $results);

        $this->assertEmpty($sut->fetchListData([]));
    }
}
