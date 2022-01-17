<?php

namespace OlcsTest\Service\Data;

use Common\Service\Data\Application as ApplicationDataService;
use Common\Service\Data\Licence as LicenceDataService;
use Laminas\ServiceManager\ServiceManager;
use Olcs\Service\Data\SubmissionLegislation;
use Mockery as m;

/**
 * Class SubmissionLegislationTest
 * @package OlcsTest\Service\Data
 */
class SubmissionLegislationTest extends m\Adapter\Phpunit\MockeryTestCase
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
        $mockLicenceService = $this->createMock(LicenceDataService::class);
        $mockAppService = m::mock(ApplicationDataService::class);

        $mockSl = m::mock(ServiceManager::class);
        $mockSl->expects('get')
            ->with('\Common\Service\Data\Licence')
            ->andReturn($mockLicenceService);

        $mockSl->expects('get')
            ->with('\Common\Service\Data\Application')
            ->andReturn($mockAppService);

        $sut = new SubmissionLegislation();
        $service = $sut->createService($mockSl);

        $this->assertInstanceOf('\Olcs\Service\Data\SubmissionLegislation', $service);
        $this->assertSame($mockLicenceService, $service->getLicenceService());
        $this->assertSame($mockAppService, $service->getApplicationService());
    }
}
