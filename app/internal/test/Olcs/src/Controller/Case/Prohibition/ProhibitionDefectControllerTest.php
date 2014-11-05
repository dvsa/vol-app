<?php

namespace OlcsTest\Controller;

use Olcs\Controller\Cases\Prohibition\ProhibitionDefectController;
use Mockery as m;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Olcs\TestHelpers\ControllerRouteMatchHelper;

/**
 * Class ProhibitionDefectControllerTest
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ProhibitionDefectControllerTest extends \PHPUnit_Framework_TestCase
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
        $this->sut = new ProhibitionDefectController();

        parent::setUp();
    }

    public function testRedirectToIndex()
    {
        $prohibition = 1;

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            ['params' => 'Params', 'redirect' => 'Redirect']
        );

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('prohibition')->andReturn($prohibition);

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRoute')->with(
            'case_prohibition_defect',
            ['action'=>'index', 'prohibition' => $prohibition, 'id' => null],
            ['code' => '303'], true
        )->andReturn('redirectResponse');

        $mockPluginManager->shouldReceive('get')->with('redirect')->andReturn($mockRedirect);

        $this->sut->setPluginManager($mockPluginManager);

        $this->assertEquals('redirectResponse', $this->sut->redirectToIndex());
    }

    /**
     * Tests the index action
     */
    public function testIndexAction()
    {
        $caseId = 24;
        $prohibitionId = 1;
        $action = null;
        $defaultPage = 1;
        $defaultSort = 'id';
        $defaultOrder = 'DESC';
        $defaultLimit = 10;
        $mockRestData = ['Results' => [0 => ['id' => 1]], 'Count' => 1];

        //mock plugin manager
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'forward' => 'Forward',
                'params' => 'Params',
                'url' => 'Url'
            ]
        );

        //route params
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);
        $mockParams->shouldReceive('fromRoute')->with('prohibition')->andReturn($prohibitionId);

        //checking for CRUD action
        $mockParams->shouldReceive('fromPost')->with('action')->andReturn($action);

        //sorting
        $mockParams->shouldReceive('fromQuery')->with('page', $defaultPage)->andReturn($defaultPage);
        $mockParams->shouldReceive('fromQuery')->with('sort', $defaultSort)->andReturn($defaultSort);
        $mockParams->shouldReceive('fromQuery')->with('order', $defaultOrder)->andReturn($defaultOrder);
        $mockParams->shouldReceive('fromQuery')->with('limit', $defaultLimit)->andReturn($defaultLimit);

        //list vars
        $mockParams->shouldReceive('fromQuery')->with('case', null)->andReturn($caseId);
        $mockParams->shouldReceive('fromQuery')->with('prohibition', null)->andReturn($prohibitionId);

        //forward to the Prohibition controller to get prohibition data
        $mockForward = $mockPluginManager->get('forward', '');
        $mockForward->shouldReceive('dispatch')->withAnyArgs();

        //mock url helper
        $mockPluginManager->shouldReceive('get')->with('url')->andReturnSelf();

        $this->sut->setPluginManager($mockPluginManager);

        //rest call to return prohibition data
        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->withAnyArgs()->andReturn($mockRestData);

        //placeholders
        $placeholder = new \Zend\View\Helper\Placeholder();
        $placeholder->getContainer('pageTitle')->set('foo1');
        $placeholder->getContainer('pageTitle')->append('foo2');
        $placeholder->getContainer('pageTitle')->append('foo3');
        $placeholder->getContainer('pageTitle')->append('foo4');
        $placeholder->getContainer('pageSubtitle')->set('foo1');
        $placeholder->getContainer('pageSubtitle')->append('foo2');

        //add placeholders to view helper
        $mockViewHelperManager = new \Zend\View\HelperPluginManager();
        $mockViewHelperManager->setService('placeholder', $placeholder);

        //mock table builder
        $mockTableBuilder = m::mock('Common\Service\Table\TableBuilder');
        $mockTableBuilder->shouldReceive('buildTable')->withAnyArgs();

        //mock service manager
        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);
        $mockServiceManager->shouldReceive('get')->with('viewHelperManager')->andReturn($mockViewHelperManager);
        $mockServiceManager->shouldReceive('get')->with('Table')->andReturn($mockTableBuilder);

        $this->sut->setServiceLocator($mockServiceManager);

        $this->sut->indexAction();

        $this->assertTrue($placeholder->getContainer('pageTitle')->offsetExists(0));
        $this->assertFalse($placeholder->getContainer('pageTitle')->offsetExists(1));
        $this->assertTrue($placeholder->getContainer('pageTitle')->offsetExists(2));
        $this->assertFalse($placeholder->getContainer('pageTitle')->offsetExists(3));
        $this->assertTrue($placeholder->getContainer('pageSubtitle')->offsetExists(0));
        $this->assertFalse($placeholder->getContainer('pageSubtitle')->offsetExists(1));
    }

    /**
     * Checks that the prohibition id is correctly appended to the form data
     */
    public function testGetDataForFormEdit()
    {
        $prohibitionId = 1;
        $id = 1;
        $action = 'edit';
        $mockRestData = ['id' => $id];

        $expected = [
            'id' => $id,
            'fields' => [
                'id' => $id,
                'prohibition' => $prohibitionId
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
        $mockParams->shouldReceive('fromRoute')->with('prohibition')->andReturn($prohibitionId);
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
        $prohibitionId = 1;
        $id = 1;
        $case = 24;
        $action = 'add';
        $mockRestData = false;

        $expected = [
            'case' => $case,
            'fields' => [
                'case' => $case,
                'prohibition' => $prohibitionId
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
        $mockParams->shouldReceive('fromRoute')->with('prohibition')->andReturn($prohibitionId);
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
