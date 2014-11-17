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
        ['id' => 12, 'sectionCode' => 'Section A', 'description' => 'Description 1'],
        ['id' => 15, 'sectionCode' => 'Section C', 'description' => 'Description 2'],
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
        $mockLicenceService = $this->getMock('\Common\Service\Data\Licence');
        $mockLicenceService->expects($this->once())
            ->method('fetchLicenceData')
            ->willReturn(['niFlag'=> true, 'goodsOrPsv' => ['id'=>'lcat_gv'], 'trafficArea' => ['id' => 'B']]);

        $sut = new PublicInquiryReason();
        $sut->setLicenceService($mockLicenceService);
        $sut->setData('pid', $this->reasons);

        $this->assertEquals([12 => 'Description 1', 15 => 'Description 2'], $sut->fetchListOptions([]));
    }

    public function testFetchListOptionsWoithGroups()
    {
        $mockLicenceService = $this->getMock('\Common\Service\Data\Licence');
        $mockLicenceService->expects($this->once())
            ->method('fetchLicenceData')
            ->willReturn(['niFlag'=> true, 'goodsOrPsv' => ['id'=>'lcat_gv'], 'trafficArea' => ['id' => 'B']]);

        $sut = new PublicInquiryReason();
        $sut->setLicenceService($mockLicenceService);
        $sut->setData('pid', $this->reasons);

        $expected = [
            'Section A' => [
                'label' => 'Section A',
                'options' => [12 => 'Description 1']
            ],
            'Section C' => [
                'label' => 'Section C',
                'options'=>[15 => 'Description 2']
            ]
        ];

        $this->assertEquals($expected, $sut->fetchListOptions([], true));
    }

    public function testFetchListOptionsEmpty()
    {
        $mockLicenceService = $this->getMock('\Common\Service\Data\Licence');
        $mockLicenceService->expects($this->once())
            ->method('fetchLicenceData')
            ->willReturn(['niFlag'=> true, 'goodsOrPsv' => ['id'=>'lcat_gv'], 'trafficArea' => ['id' => 'B']]);

        $sut = new PublicInquiryReason();
        $sut->setLicenceService($mockLicenceService);
        $sut->setData('pid', false);

        $this->assertEquals([], $sut->fetchListOptions([]));
    }

    public function testCreateService()
    {
        $mockLicenceService = $this->getMock('\Common\Service\Data\Licence');

        $mockSl = $this->getMock('\Zend\ServiceManager\ServiceManager');
        $mockSl->expects($this->once())
            ->method('get')
            ->with('\Common\Service\Data\Licence')
            ->willReturn($mockLicenceService);

        $sut = new PublicInquiryReason();
        $service = $sut->createService($mockSl);

        $this->assertInstanceOf('\Olcs\Service\Data\PublicInquiryReason', $service);
        $this->assertSame($mockLicenceService, $service->getLicenceService());
    }
}
