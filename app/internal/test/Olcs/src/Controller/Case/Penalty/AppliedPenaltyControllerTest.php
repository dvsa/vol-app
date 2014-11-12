<?php

namespace OlcsTest\Controller;

use Olcs\Controller\Cases\Penalty\AppliedPenaltyController;
use Mockery as m;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Olcs\TestHelpers\ControllerRouteMatchHelper;

/**
 * Class AppliedPenaltyControllerTest
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class AppliedPenaltyControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ControllerPluginManagerHelper
     */
    protected $pluginManagerHelper;

    /**
     * @var ControllerRouteMatchHelper
     */
    protected $routeMatchHelper;

    public function __construct()
    {
        $this->pluginManagerHelper = new ControllerPluginManagerHelper();
        $this->routeMatchHelper = new ControllerRouteMatchHelper();
    }

    public function setUp()
    {
        $this->sut = new AppliedPenaltyController();

        parent::setUp();
    }

    public function testRedirectToIndex()
    {
        $caseId = 29;

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            ['params' => 'Params', 'redirect' => 'Redirect']
        );

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRoute')->with(
            'case_penalty',
            ['action'=>'index', 'case' => $caseId],
            ['code' => '303'], false
        )->andReturn('redirectResponse');

        $mockPluginManager->shouldReceive('get')->with('redirect')->andReturn($mockRedirect);

        $this->sut->setPluginManager($mockPluginManager);

        $this->assertEquals('redirectResponse', $this->sut->redirectToIndex());
    }

    /**
     * Checks that the prohibition id is correctly appended to the form data
     */
    public function testGetDataForFormEdit()
    {
        $seriousInfringementId = 1;
        $id = 1;
        $action = 'edit';
        $mockRestData = ['id' => $id];

        $expected = [
            'id' => $id,
            'fields' => [
                'id' => $id,
                'seriousInfringement' => $seriousInfringementId
            ],
            'base' => [
                'id' => $id,
                'fields' => [
                    'id' => $id,
                ],
            ]
        ];

        //mock plugin manager
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params'
            ]
        );

        //rest call to return prohibition data
        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->withAnyArgs()->andReturn($mockRestData);

        //route params
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('seriousInfringement')->andReturn($seriousInfringementId);
        $mockParams->shouldReceive('fromRoute')->with('action')->andReturn($action);
        $mockParams->shouldReceive('fromRoute')->with('id')->andReturn($id);
        $this->sut->setPluginManager($mockPluginManager);

        //mock service manager
        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);

        $this->sut->setServiceLocator($mockServiceManager);

        $data = $this->sut->getDataForForm();

        $this->assertEquals($data, $expected);
    }

    /**
     * Checks that the prohibition id is correctly appended to the form data
     */
    public function testGetDataForFormAdd()
    {
        $seriousInfringementId = 1;
        $id = 1;
        $case = 24;
        $action = 'add';
        $mockRestData = false;

        $expected = [
            'case' => $case,
            'fields' => [
                'case' => $case,
                'seriousInfringement' => $seriousInfringementId
            ],
            'base' => [
                'case' => $case
            ]
        ];

        //mock plugin manager
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params'
            ]
        );

        //rest call to return prohibition data
        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->withAnyArgs()->andReturn($mockRestData);

        //route params
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('seriousInfringement')->andReturn($seriousInfringementId);
        $mockParams->shouldReceive('fromRoute')->with('action')->andReturn($action);
        $mockParams->shouldReceive('fromRoute')->with('id')->andReturn($id);
        $mockParams->shouldReceive('fromQuery')->with('case', null)->andReturn($case);
        $this->sut->setPluginManager($mockPluginManager);

        $event = $this->routeMatchHelper->getMockRouteMatch(array('action' => 'not-found'));
        $this->sut->setEvent($event);

        //mock service manager
        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);

        $this->sut->setServiceLocator($mockServiceManager);

        $data = $this->sut->getDataForForm();

        $this->assertEquals($data, $expected);
    }
}
