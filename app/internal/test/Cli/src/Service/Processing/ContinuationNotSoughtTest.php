<?php

/**
 * Test Batch Processing Service
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
namespace CliTest\Service\Processing;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use Cli\Service\Processing\ContinuationNotSought;
use Common\Service\Entity\LicenceEntityService;

/**
 * Test Batch Processing Service
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class ContinuationNotSoughtTest extends MockeryTestCase
{
    protected $sm;
    protected $sut;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->sut = new ContinuationNotSought();
        $this->sut->setServiceLocator($this->sm);
    }

    public function testProcess()
    {
        $mockLicenceStatusHelper = m::mock();
        $this->sm->setService('Helper\LicenceStatus', $mockLicenceStatusHelper);

        $mockLicenceEntityService = m::mock();
        $this->sm->setService('Entity\Licence', $mockLicenceEntityService);

        $mockTmLicenceEntityService = m::mock();
        $this->sm->setService('Entity\TransportManagerLicence', $mockTmLicenceEntityService);

        $mockApplicationProcessingService = m::mock();
        $this->sm->setService('Processing\Application', $mockApplicationProcessingService);

        $data = [
            'Count' => 76,
            'Results' => [
                ['id' => 1966, 'licenceVehicles' => ['LV1']],
                ['id' => 21, 'licenceVehicles' => ['LV2']],
            ]
        ];

        $mockLicenceEntityService->shouldReceive('getForContinuationNotSought')->with()->once()->andReturn($data);

        $mockLicenceEntityService->shouldReceive('setStatusToContinuationNotSought')
            ->with($data['Results'][0])
            ->once();
        $mockLicenceEntityService->shouldReceive('setStatusToContinuationNotSought')
            ->with($data['Results'][1])
            ->once();

        $mockLicenceStatusHelper->shouldReceive('ceaseDiscs')
            ->with($data['Results'][0])
            ->once();
        $mockLicenceStatusHelper->shouldReceive('ceaseDiscs')
            ->with($data['Results'][1])
            ->once();

        $mockLicenceStatusHelper->shouldReceive('removeLicenceVehicles')
            ->with($data['Results'][0]['licenceVehicles'])
            ->once();
        $mockLicenceStatusHelper->shouldReceive('removeLicenceVehicles')
            ->with($data['Results'][1]['licenceVehicles'])
            ->once();

        $mockTmLicenceEntityService->shouldReceive('deleteForLicence')
            ->with($data['Results'][0]['id'])
            ->once();
        $mockTmLicenceEntityService->shouldReceive('deleteForLicence')
            ->with($data['Results'][1]['id'])
            ->once();

        $mockApplicationProcessingService->shouldReceive('expireCommunityLicencesForLicence')
            ->with($data['Results'][0]['id'])
            ->once();
        $mockApplicationProcessingService->shouldReceive('expireCommunityLicencesForLicence')
            ->with($data['Results'][1]['id'])
            ->once();

        $this->sut->process([]);
    }

    public function testProcessTestMode()
    {
        $mockLicenceStatusHelper = m::mock();
        $this->sm->setService('Helper\LicenceStatus', $mockLicenceStatusHelper);

        $mockLicenceEntityService = m::mock();
        $this->sm->setService('Entity\Licence', $mockLicenceEntityService);

        $mockTmLicenceEntityService = m::mock();
        $this->sm->setService('Entity\TransportManagerLicence', $mockTmLicenceEntityService);

        $mockApplicationProcessingService = m::mock();
        $this->sm->setService('Processing\Application', $mockApplicationProcessingService);

        $data = [
            'Count' => 76,
            'Results' => [
                ['id' => 1966, 'licenceVehicles' => ['LV1']],
                ['id' => 21, 'licenceVehicles' => ['LV2']],
            ]
        ];

        $mockLicenceEntityService->shouldReceive('getForContinuationNotSought')->with()->once()->andReturn($data);

        $this->sut->process(['testMode' => true]);
    }
}
