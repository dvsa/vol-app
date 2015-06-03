<?php
/**
 * Bus Registration decision controller tests
 */
namespace OlcsTest\Controller\Bus\Processing;

use Olcs\Controller\Bus\Processing\BusProcessingDecisionController;
use Common\Service\BusRegistration;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\TestHelpers\ControllerPluginManagerHelper;

/**
 * Bus Registration decision controller tests
 */
class BusProcessingDecisionControllerTest extends MockeryTestCase
{
    /**
     * @var m\MockInterface|\Zend\Mvc\Controller\PluginManager
     */
    protected $pluginManager;

    protected $sut;

    /**
     * @todo These tests require a real service manager to run, as they are not mocking all dependencies,
     * these tests should be addresses
     */
    public function setUp()
    {
        $this->sut = new BusProcessingDecisionController();
        $this->pluginManagerHelper = new ControllerPluginManagerHelper();
        parent::setUp();
    }

    /**
     * Test the republish action
     *
     * @param $busRegData
     * @param $expectedPublishData
     * @param $expectedTrafficAreas
     * @param $expectedFilter
     * @param $expectedSuccess
     *
     * @dataProvider republishActionDataProvider
     */
    public function testRepublishAction(
        $busRegData,
        $expectedPublishData,
        $expectedTrafficAreas,
        $expectedFilter,
        $expectedSuccess
    ) {
        $this->markTestSkipped();

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params',
                'FlashMessenger' => 'FlashMessenger',
                'redirect' => 'Redirect'
            ]
        );
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('busRegId')->andReturn($busRegData['id']);

        $mockFlashMessenger = $mockPluginManager->get('FlashMessenger', '');
        $mockFlashMessenger->shouldReceive('addSuccessMessage')->times($expectedSuccess ? 1 : 0);
        $mockFlashMessenger->shouldReceive('addErrorMessage')->times($expectedSuccess ? 0 : 1);

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRouteAjax')
            ->once()
            ->with(
                null,
                ['action'=>'index', 'busRegId' => $busRegData['id'], 'status' => null],
                ['code' => '303'],
                true
            )
            ->andReturn('redirectResponse');

        $mockPluginManager->shouldReceive('get')->with('redirect')->andReturn($mockRedirect);

        $this->sut->setPluginManager($mockPluginManager);

        $busRegService = m::mock('Common\Service\Data\BusReg')
            ->shouldReceive('fetchOne')
            ->with($busRegData['id'])
            ->andReturn($busRegData)
            ->getMock();

        $mockPublicationHelper = m::mock('Olcs\Service\Utility\PublicationHelper')
            ->shouldReceive('publishMultiple')
            ->times($expectedSuccess ? 1 : 0)
            ->with(
                $expectedPublishData,
                $expectedTrafficAreas,
                'N&P',
                $expectedFilter
            )
            ->getMock();

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')->with('Common\Service\Data\BusReg')->andReturn($busRegService);
        $mockServiceManager->shouldReceive('get')->with('Olcs\Service\Utility\PublicationHelper')
            ->andReturn($mockPublicationHelper);

        $this->sut->setServiceLocator($mockServiceManager);

        $this->assertEquals('redirectResponse', $this->sut->republishAction());
    }

    public function republishActionDataProvider()
    {
        return [
            // BusRegistration::STATUS_NEW
            [
                [
                    'id' => 69,
                    'licence' => [
                        'id' => 110,
                    ],
                    'revertStatus' => ['id' => BusRegistration::STATUS_NEW],
                    'trafficAreas' => [
                        ['id' => 'A'],
                        ['id' => 'B']
                    ]
                ],
                ['busReg' => 69, 'licence' => 110, 'previousStatus' => BusRegistration::STATUS_NEW],
                ['A', 'B'],
                'BusRegGrantNewPublicationFilter',
                true
            ],
            // BusRegistration::STATUS_CANCEL
            [
                [
                    'id' => 69,
                    'licence' => [
                        'id' => 110,
                    ],
                    'revertStatus' => ['id' => BusRegistration::STATUS_CANCEL],
                    'trafficAreas' => [
                        ['id' => 'A'],
                        ['id' => 'B']
                    ]
                ],
                ['busReg' => 69, 'licence' => 110, 'previousStatus' => BusRegistration::STATUS_CANCEL],
                ['A', 'B'],
                'BusRegGrantCancelPublicationFilter',
                true
            ],
            // BusRegistration::STATUS_VAR
            [
                [
                    'id' => 69,
                    'licence' => [
                        'id' => 110,
                    ],
                    'revertStatus' => ['id' => BusRegistration::STATUS_VAR],
                    'trafficAreas' => [
                        ['id' => 'A'],
                        ['id' => 'B']
                    ]
                ],
                ['busReg' => 69, 'licence' => 110, 'previousStatus' => BusRegistration::STATUS_VAR],
                ['A', 'B'],
                'BusRegGrantVarPublicationFilter',
                true
            ],
            // Status which is not mapped
            [
                [
                    'id' => 69,
                    'licence' => [
                        'id' => 110,
                    ],
                    'revertStatus' => ['id' => 'NOT_MAPPED'],
                    'trafficAreas' => [
                        ['id' => 'A'],
                        ['id' => 'B']
                    ]
                ],
                null,
                null,
                null,
                false
            ],
        ];
    }
}
