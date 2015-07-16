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
        $this->markTestSkipped();

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

        $continuationDetails = [
            'Count' => 0,
        ];

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
        $sl->setService('Helper\LicenceStatus', $licenceStatusService);

        $mockContinuationDetailEntityService = m::mock();
        $sl->setService('Entity\ContinuationDetail', $mockContinuationDetailEntityService);

        // expectations
        $licenceStatusService
            ->shouldReceive('getCurrentOrPendingRulesForLicence')
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
                    ['status', 'statusRule', 'continuation'],
                    [
                        'licence' => $licence,
                        'licenceStatusRule' => $licenceStatusRule,
                        'continuationDetails' => null
                    ]
                )
                ->once()
                ->andReturn('STATUS_MARKERS');

        $mockContinuationDetailEntityService->shouldReceive('getContinuationMarker')->with(1)
            ->once()->andReturn($continuationDetails);

        $expectedMarkers = [
            'CASE_MARKERS',
            'STATUS_MARKERS',
        ];

        $this->assertEquals($expectedMarkers, $sut->setupMarkers($licence));
    }

    public function testSetupContinuationMarker()
    {
        $this->markTestSkipped();

        $licence = [
            'id' => 1966,
        ];

        $continuationDetails = [
            'Count' => 1,
            'Results' => ['CONTINUATION_DETAILS'],
        ];

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
        $sl->setService('Helper\LicenceStatus', $licenceStatusService);

        $mockContinuationDetailEntityService = m::mock();
        $sl->setService('Entity\ContinuationDetail', $mockContinuationDetailEntityService);

        // expectations
        $licenceStatusService
            ->shouldReceive('getCurrentOrPendingRulesForLicence')
            ->andReturn(null);
        $licenceMarkerService
            ->shouldReceive('generateMarkerTypes')
                ->with(
                    ['status', 'statusRule', 'continuation'],
                    [
                        'licence' => $licence,
                        'licenceStatusRule' => null,
                        'continuationDetails' => $continuationDetails['Results'][0]
                    ]
                )
                ->once()
                ->andReturn('STATUS_MARKERS');

        $mockContinuationDetailEntityService->shouldReceive('getContinuationMarker')->with(1966)
            ->once()->andReturn($continuationDetails);

        $expectedMarkers = [
            'STATUS_MARKERS',
        ];

        $this->assertEquals($expectedMarkers, $sut->setupMarkers($licence));
    }
}
