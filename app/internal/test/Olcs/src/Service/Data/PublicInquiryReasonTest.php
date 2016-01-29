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

    public function testFetchListOptionsForLicenceWithoutGoodsOrPsv()
    {
        $mockLicenceService = $this->getMock('\Common\Service\Data\Licence', ['fetchLicenceData', 'getId']);
        $mockLicenceService->expects($this->once())
            ->method('fetchLicenceData')
            ->willReturn(['id' => 7,'niFlag'=> true, 'goodsOrPsv' => null, 'trafficArea' => ['id' => 'B']]);
        $mockLicenceService->expects($this->once())
            ->method('getId')
            ->willReturn(987);

        $mockApplicationService = $this->getMock('\Common\Service\Data\Application', ['fetchApplicationData', 'setId']);
        $mockApplicationService->expects($this->once())
            ->method('fetchApplicationData')
            ->willReturn(['goodsOrPsv' => ['id'=>'lcat_gv'], 'niFlag' => 'Y']);
        $mockApplicationService->expects($this->once())
            ->method('setId')
            ->with($this->equalTo(321));

        $mockApplicationEntityService = $this->getMock('\StdClass', ['getApplicationsForLicence']);
        $mockApplicationEntityService->expects($this->once())
            ->method('getApplicationsForLicence')
            ->with($this->equalTo(987))
            ->willReturn(['Results' => [['id' => 321]]]);

        $mockServiceLocator = $this->getMock('\Zend\ServiceManager\ServiceLocatorInterface', ['get', 'has']);
        $mockServiceLocator->expects($this->any())
            ->method('get')
            ->with($this->equalTo('Entity\Application'))
            ->will($this->returnValue($mockApplicationEntityService));

        $sut = new PublicInquiryReason();
        $sut->setServiceLocator($mockServiceLocator);
        $sut->setLicenceService($mockLicenceService);
        $sut->setApplicationService($mockApplicationService);
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
        $mockApplicationService = $this->getMock('\Common\Service\Data\Application');

        $mockSl = $this->getMock('\Zend\ServiceManager\ServiceManager');
        $mockSl->expects($this->at(0))
            ->method('get')
            ->with('\Common\Service\Data\Licence')
            ->willReturn($mockLicenceService);
        $mockSl->expects($this->at(1))
            ->method('get')
            ->with('\Common\Service\Data\Application')
            ->willReturn($mockApplicationService);

        $sut = new PublicInquiryReason();
        $service = $sut->createService($mockSl);

        $this->assertInstanceOf('\Olcs\Service\Data\PublicInquiryReason', $service);
        $this->assertSame($mockLicenceService, $service->getLicenceService());
        $this->assertSame($mockApplicationService, $service->getApplicationService());
    }
}
