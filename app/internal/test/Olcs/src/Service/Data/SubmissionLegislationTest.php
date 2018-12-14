<?php

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\SubmissionLegislation;

/**
 * Class SubmissionLegislationTest
 * @package OlcsTest\Service\Data
 */
class SubmissionLegislationTest extends \PHPUnit\Framework\TestCase
{
    private $reasons = [
        ['id' => 12, 'description' => 'Description 1', 'isProposeToRevoke' => 'Y'],
        ['id' => 15, 'description' => 'Description 2', 'isProposeToRevoke' => 'N'],
    ];

    private $reasons2 = [
        ['value' => 12, 'label' => 'Description 1', 'attributes' => ['data-in-office-revokation' => 'Y']],
        ['value' => 15, 'label' => 'Description 2', 'attributes' => ['data-in-office-revokation' => 'N']],
    ];

    public function testFormatData()
    {
        $sut = new SubmissionLegislation();

        $this->assertEquals($this->reasons2, $sut->formatData($this->reasons));
    }

    public function testCreateService()
    {
        $mockLicenceService = $this->createMock('\Common\Service\Data\Licence');
        $mockApplicationService = $this->createMock('\Common\Service\Data\Application');

        $mockSl = $this->createMock('\Zend\ServiceManager\ServiceManager');
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
