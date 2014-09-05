<?php

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\PublicInquiryReason;

/**
 * Class PublicInquiryReasonTest
 * @package OlcsTest\Service\Data
 */
class PublicInquiryReasonTest extends \PHPUnit_Framework_TestCase
{
    private $reasons = [
        ['id' => 12, 'sectionCode' => 'Section A'],
        ['id' => 15, 'sectionCode' => 'Section C'],
    ];

    public function testFetchPublicInquiryReasonData()
    {
        $piReasons = ['Results' =>
            $this->reasons
        ];

        $mockRestClient = $this->getMock('\Common\Util\RestClient', [], [], '', false);
        $mockRestClient->expects($this->once())
            ->method('get')
            ->with($this->equalTo(''), $this->isType('array'))
            ->willReturn($piReasons);

        $sut = new PublicInquiryReason();
        $sut->setRestClient($mockRestClient);

        $this->assertEquals($this->reasons, $sut->fetchPublicInquiryReasonData([]));
        //test data is cached
        $this->assertEquals($this->reasons, $sut->fetchPublicInquiryReasonData([]));
    }

    public function testFetchPublicInquiryReasonDataFailure()
    {
        $piReasons = [];

        $mockRestClient = $this->getMock('\Common\Util\RestClient', [], [], '', false);
        $mockRestClient->expects($this->once())
            ->method('get')
            ->with($this->equalTo(''), $this->isType('array'))
            ->willReturn($piReasons);

        $sut = new PublicInquiryReason();
        $sut->setRestClient($mockRestClient);

        $this->assertEquals(false, $sut->fetchPublicInquiryReasonData([]));
        //test failure isn't retried
        $this->assertEquals(false, $sut->fetchPublicInquiryReasonData([]));
    }

    public function testFetchListOptions()
    {
        $sut = new PublicInquiryReason();
        $sut->setData('pir', $this->reasons);

        $this->assertEquals([12 => 'Section A', 15 => 'Section C'], $sut->fetchListOptions([]));
    }

    public function testFetchListOptionsEmpty()
    {
        $sut = new PublicInquiryReason();
        $sut->setData('pir', false);

        $this->assertEquals([], $sut->fetchListOptions([]));

    }
}
