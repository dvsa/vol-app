<?php

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\SubmissionLegislation;

/**
 * Class SubmissionLegislationTest
 * @package OlcsTest\Service\Data
 */
class SubmissionLegislationTest extends \PHPUnit_Framework_TestCase
{
    private $reasons = [
        0 => ['id' => 12, 'description' => 'Description 1', 'isProposeToRevoke' => 'Y'],
        1 => ['id' => 15, 'description' => 'Description 2', 'isProposeToRevoke' => 'N'],
    ];

    private $reasons2 = [
        12 => 'Description 1',
        15 => 'Description 2',
    ];

    public function testFormatData()
    {
        $sut = new SubmissionLegislation();

        $this->assertEquals($this->reasons2, $sut->formatData($this->reasons));
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

        $sut = new SubmissionLegislation();
        $service = $sut->createService($mockSl);

        $this->assertInstanceOf('\Olcs\Service\Data\SubmissionLegislation', $service);
        $this->assertSame($mockLicenceService, $service->getLicenceService());
        $this->assertSame($mockApplicationService, $service->getApplicationService());
    }
}
