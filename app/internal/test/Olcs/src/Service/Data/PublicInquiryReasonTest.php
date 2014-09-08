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

        $this->assertEquals($this->reasons, $sut->fetchPublicInquiryData([]));
        //test data is cached
        $this->assertEquals($this->reasons, $sut->fetchPublicInquiryData([]));
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

        $this->assertEquals(false, $sut->fetchPublicInquiryData([]));
        //test failure isn't retried
        $this->assertEquals(false, $sut->fetchPublicInquiryData([]));
    }

    public function testFetchListOptions()
    {
        $mockLicenceService = $this->getMock('\Olcs\Service\Data\Licence');
        $mockLicenceService->expects($this->once())
            ->method('fetchLicenceData')
            ->willReturn(['niFlag'=> true, 'goodsOrPsv' => ['id'=>'lcat_gv']]);

        $sut = new PublicInquiryReason();
        $sut->setLicenceService($mockLicenceService);
        $sut->setData('pid', $this->reasons);

        $this->assertEquals([12 => 'Section A', 15 => 'Section C'], $sut->fetchListOptions([]));
    }

    public function testFetchListOptionsEmpty()
    {
        $mockLicenceService = $this->getMock('\Olcs\Service\Data\Licence');
        $mockLicenceService->expects($this->once())
            ->method('fetchLicenceData')
            ->willReturn(['niFlag'=> true, 'goodsOrPsv' => ['id'=>'lcat_gv']]);

        $sut = new PublicInquiryReason();
        $sut->setLicenceService($mockLicenceService);
        $sut->setData('pid', false);

        $this->assertEquals([], $sut->fetchListOptions([]));
    }

    public function testCreateService()
    {
        $mockLicenceService = $this->getMock('\Olcs\Service\Data\Licence');
        $mockTranslator = $this->getMock('stdClass', ['getLocale']);
        $mockTranslator->expects($this->once())->method('getLocale')->willReturn('en_GB');

        $mockRestClient = $this->getMock('\Common\Util\RestClient', [], [], '', 0);
        $mockRestClient->expects($this->once())->method('setLanguage')->with($this->equalTo('en_GB'));

        $mockApiResolver = $this->getMock('stdClass', ['getClient']);
        $mockApiResolver
            ->expects($this->once())
            ->method('getClient')
            ->with($this->equalTo('Reason'))
            ->willReturn($mockRestClient);

        $mockSl = $this->getMock('\Zend\ServiceManager\ServiceManager');
        $mockSl->expects($this->any())
            ->method('get')
            ->willReturnMap(
                [
                    ['translator', true, $mockTranslator],
                    ['ServiceApiResolver', true, $mockApiResolver],
                    ['Olcs\Service\Data\Licence', true, $mockLicenceService]
                ]
            );

        $sut = new PublicInquiryReason();
        $service = $sut->createService($mockSl);

        $this->assertInstanceOf('\Olcs\Service\Data\PublicInquiryReason', $service);
        $this->assertSame($mockRestClient, $service->getRestClient());
        $this->assertSame($mockLicenceService, $service->getLicenceService());
    }
}
