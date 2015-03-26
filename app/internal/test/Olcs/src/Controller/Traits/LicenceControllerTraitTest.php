<?php

namespace OlcsTest\Controller\Traits;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Zend\View\HelperPluginManager as HelperPluginManager;
use Zend\ServiceManager\ServiceManager as ServiceLocator;
use Common\Service\Data\Licence as LicenceService;
use OlcsTest\Bootstrap;

/**
 * Class LicenceControllerTraitTest
 * @package OlcsTest\Controller\Traits
 */
class LicenceControllerTraitTest extends MockeryTestCase
{
    public function testSetupMarkers()
    {
        // stub data
        $case = ['CASE_DATA'];

        $licence = [
            'id' => '1',
            'name' => 'shaun',
            'cases' => [
                0 => $case
            ]
        ];

        $licenceStatusRule = [
            'licenceId' => '1',
            'licenceStatus' => ['id' => 'lsts_curtailed', 'description' => 'Curtailed'],
            'startDate' => '2015-03-20 12:34:56',
            'endDate' => '2015-04-01 12:34:56',
        ]; // this will be an entity service call

        // subject under test
        $sut = new \OlcsTest\Controller\Traits\Stub\StubLicenceController();

        // mock service dependencies
        $sl = Bootstrap::getServiceManager();
        $sut->setServiceLocator($sl);

        $licenceMarkerService = m::mock('Olcs\Service\Marker\LicenceMarkers');
        $markerPluginManager = m::mock('Olcs\Service\Marker\MarkerPluginManager')
            ->shouldReceive('get')
            ->with('Olcs\Service\Marker\LicenceMarkers')
            ->andReturn($licenceMarkerService)
            ->getMock();
        $sl->setService('Olcs\Service\Marker\MarkerPluginManager', $markerPluginManager);

        $licenceStatusService = m::mock();
        $sl->setService('Entity\LicenceStatusRule', $licenceStatusService);

        // expectations
        $licenceStatusService
            ->shouldReceive('getPendingChangesForLicence')
            ->andReturn([$licenceStatusRule]);
        $licenceMarkerService
            ->shouldReceive('generateMarkerTypes')
                ->with(
                    ['appeal', 'stay'],
                    ['case' => $case, 'licence' => $licence]
                )
                ->once()
                ->andReturn('CASE_MARKERS')
            ->shouldReceive('generateMarkerTypes')
                ->with(
                    ['status', 'statusRule'],
                    ['licence' => $licence, 'licenceStatusRule' => $licenceStatusRule]
                )
                ->once()
                ->andReturn('STATUS_MARKERS');

        $expectedMarkers = [
            'CASE_MARKERS',
            'STATUS_MARKERS',
        ];

        $this->assertEquals($expectedMarkers, $sut->setupMarkers($licence));
    }
}
