<?php

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\SubmissionSectionComment;

/**
 * Class SubmissionSectionCommentTest
 * @package OlcsTest\Service\Data
 */
class SubmissionSectionCommentTest extends \PHPUnit_Framework_TestCase
{
    protected $sut;

    public function setUp()
    {
        $this->sut = new SubmissionSectionComment();
    }

    public function testCreateService()
    {
        $mockTranslator = $this->getMock('stdClass', ['getLocale']);
        $mockTranslator->expects($this->once())->method('getLocale')->willReturn('en_GB');

        $mockRestClient = $this->getMock('\Common\Util\RestClient', [], [], '', 0);
        $mockRestClient->expects($this->once())->method('setLanguage')->with($this->equalTo('en_GB'));

        $mockApiResolver = $this->getMock('stdClass', ['getClient']);
        $mockApiResolver
            ->expects($this->once())
            ->method('getClient')
            ->with($this->equalTo('SubmissionSectionComment'))
            ->willReturn($mockRestClient);

        $mockSl = $this->getMock('\Zend\ServiceManager\ServiceManager');
        $mockSl->expects($this->any())
            ->method('get')
            ->willReturnMap(
                [
                    ['translator', true, $mockTranslator],
                    ['ServiceApiResolver', true, $mockApiResolver],
                ]
            );

        $service = $this->sut->createService($mockSl);

        $this->assertInstanceOf('\Olcs\Service\Data\SubmissionSectionComment', $service);
        $this->assertSame($mockRestClient, $service->getRestClient());
    }

    public function testGetBundle()
    {
        $bundle = $this->sut->getBundle();

        $this->assertArrayHasKey('properties', $bundle);

    }

    public function testSetSubmissionService()
    {
        $service = new \StdClass();
        $this->sut->setSubmissionService($service);
        $this->assertEquals($service, $this->sut->getSubmissionService());
    }

    public function testSetSubmissionConfig()
    {
        $config = ['sections' => [], 'mandatory-sections' => []];
        $this->sut->setSubmissionconfig($config);
        $this->assertEquals($config, $this->sut->getSubmissionConfig());
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
}
