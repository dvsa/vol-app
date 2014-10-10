<?php

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\Submission;

/**
 * Class SubmissionTest
 * @package OlcsTest\Service\Data
 */
class LicenceTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->sut = new Submission();
    }

    public function testSetId()
    {
        $this->sut->setId(1);
        $this->assertEquals(1, $this->sut->getId());
    }

    public function testGetId()
    {
        $this->assertNull($this->sut->getId());
    }

    public function testSetApiResolver()
    {
        $apiResolver = new \StdClass();
        $this->sut->setApiResolver($apiResolver);
        $this->assertEquals($apiResolver, $this->sut->getApiResolver());
    }

    public function testGetApiResolver()
    {
        $this->assertNull($this->sut->getApiResolver());
    }

    public function testSetSubmissionConfig()
    {
        $config = ['foo'];
        $this->sut->setSubmissionConfig($config);
        $this->assertEquals($config, $this->sut->getSubmissionConfig());
    }

    public function testGetSubmissionConfig()
    {
        $this->assertNull($this->sut->getSubmissionConfig());
    }

    public function testCreateService()
    {
        $mockRefDataService = $this->getMock('\Olcs\Service\Data\RefData');

        $mockTranslator = $this->getMock('stdClass', ['getLocale']);
        $mockTranslator->expects($this->once())->method('getLocale')->willReturn('en_GB');

        $mockRestClient = $this->getMock('\Common\Util\RestClient', [], [], '', 0);
        $mockRestClient->expects($this->once())->method('setLanguage')->with($this->equalTo('en_GB'));

        $mockApiResolver = $this->getMock('stdClass', ['getClient']);
        $mockApiResolver
            ->expects($this->once())
            ->method('getClient')
            ->with($this->equalTo('Submission'))
            ->willReturn($mockRestClient);

        $mockSl = $this->getMock('\Zend\ServiceManager\ServiceManager');
        $mockSl->expects($this->any())
            ->method('get')
            ->willReturnMap(
                [
                    ['translator', true, $mockTranslator],
                    ['ServiceApiResolver', true, $mockApiResolver],
                    ['Common\Service\Data\RefData', true, $mockRefDataService]
                ]
            );

        $service = $this->sut->createService($mockSl);

        $this->assertInstanceOf('\Olcs\Service\Data\Submission', $service);
        $this->assertSame($mockRestClient, $service->getRestClient());
        $this->assertSame($mockRefDataService, $service->getRefDataService());
    }

    public function testExtractSelectedSubmissionSectionsData()
    {

    }
}
