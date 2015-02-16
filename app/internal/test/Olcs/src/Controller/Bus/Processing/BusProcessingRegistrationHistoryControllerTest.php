<?php

/**
 * Bus Processing Registration History Controller Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace OlcsTest\Controller\Bus\Processing;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Bus Processing Controller Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusProcessingRegistrationHistoryControllerTest extends MockeryTestCase
{
    public function setUp()
    {
        $this->pluginManagerHelper = new ControllerPluginManagerHelper();

        $this->sut = new \Olcs\Controller\Bus\Processing\BusProcessingRegistrationHistoryController();

        parent::setUp();
    }

    /**
     * Tests the delete action where there is a previous variation
     */
    public function testDeleteActionWithPrevious()
    {
        $busRegId = 15;
        $previousBusRegId = 14;
        $variationNo = 5;
        $routeNo = 12345;
        $action = 'delete';

        $listSearchParams = [
            'sort' => 'variationNo',
            'order' => 'DESC',
            'limit' => 1,
            'routeNo' => $routeNo
        ];

        $dataBundle = [
            'properties' => 'ALL',
            'children' => [
                'busNoticePeriod' => [
                    'properties' => 'ALL'
                ],
                'status' => [
                    'properties' => 'ALL'
                ]
            ]
        ];

        //mock rest data
        $mockRestData = [
            'id' => $busRegId,
            'variationNo' => $variationNo,
            'routeNo' => $routeNo
        ];

        //mock list data
        $mockListData = [
            'Results' => [
                0 => [
                    'id' => $previousBusRegId
                ]
            ]
        ];

        //mock plugin manager
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params',
                'FlashMessenger' => 'FlashMessenger',
                'redirect' => 'Redirect',
                'confirm' => 'Confirm'
            ]
        );

        //route params
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('busRegId')->andReturn($busRegId);
        $mockParams->shouldReceive('fromPost')->with('action')->andReturn($action);

        $mockConfirm = $mockPluginManager->get('confirm', '');
        $mockConfirm->shouldReceive('confirm')->withAnyArgs()->andReturn(true);

        $mockFlash = $mockPluginManager->get('FlashMessenger', '');
        $mockFlash->shouldReceive('addErrorMessage');

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRouteAjax')
            ->with(null, ['action' => 'index', 'busRegId' => $previousBusRegId], ['code' => '303'], true)
            ->andReturn('redirectResponse');

        $this->sut->setPluginManager($mockPluginManager);

        //rest call to return prohibition data
        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper
            ->shouldReceive('makeRestCall')
            ->with('BusReg', 'GET', ['id' => $busRegId], $dataBundle)
            ->andReturn($mockRestData);
        $mockRestHelper
            ->shouldReceive('makeRestCall')
            ->with('BusReg', 'DELETE', ['id' => $busRegId], "")
            ->andReturn([]);
        $mockRestHelper
            ->shouldReceive('makeRestCall')
            ->with('BusReg', 'GET', $listSearchParams, $dataBundle)
            ->andReturn($mockListData);

        //mock service manager
        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);

        $this->sut->setServiceLocator($mockServiceManager);

        $this->assertEquals('redirectResponse', $this->sut->deleteAction());
    }

    /**
     * Tests the delete action where a previous variation is searched for but not found
     */
    public function testDeleteActionWithPreviousNotFound()
    {
        $busRegId = 15;
        $variationNo = 5;
        $routeNo = 12345;
        $action = 'delete';

        $listSearchParams = [
            'sort' => 'variationNo',
            'order' => 'DESC',
            'limit' => 1,
            'routeNo' => $routeNo
        ];

        $dataBundle = [
            'properties' => 'ALL',
            'children' => [
                'busNoticePeriod' => [
                    'properties' => 'ALL'
                ],
                'status' => [
                    'properties' => 'ALL'
                ]
            ]
        ];

        //mock rest data
        $mockRestData = [
            'id' => $busRegId,
            'variationNo' => $variationNo,
            'routeNo' => $routeNo
        ];

        //mock plugin manager
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params',
                'FlashMessenger' => 'FlashMessenger',
                'redirect' => 'Redirect',
                'confirm' => 'Confirm'
            ]
        );

        //route params
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('busRegId')->andReturn($busRegId);
        $mockParams->shouldReceive('fromPost')->with('action')->andReturn($action);

        $mockConfirm = $mockPluginManager->get('confirm', '');
        $mockConfirm->shouldReceive('confirm')->withAnyArgs()->andReturn(true);

        $mockFlash = $mockPluginManager->get('FlashMessenger', '');
        $mockFlash->shouldReceive('addErrorMessage');

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRouteAjax')
            ->with('licence/bus', ['action' => 'bus', 'busRegId' => null], ['code' => '303'], true)
            ->andReturn('redirectResponse');

        $this->sut->setPluginManager($mockPluginManager);

        //rest call to return prohibition data
        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper
            ->shouldReceive('makeRestCall')
            ->with('BusReg', 'GET', ['id' => $busRegId], $dataBundle)
            ->andReturn($mockRestData);
        $mockRestHelper
            ->shouldReceive('makeRestCall')
            ->with('BusReg', 'DELETE', ['id' => $busRegId], "")
            ->andReturn([]);
        $mockRestHelper
            ->shouldReceive('makeRestCall')
            ->with('BusReg', 'GET', $listSearchParams, $dataBundle)
            ->andReturn([]);

        //mock service manager
        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);

        $this->sut->setServiceLocator($mockServiceManager);

        $this->assertEquals('redirectResponse', $this->sut->deleteAction());
    }

    /**
     * Tests the delete action when there's no previous variation
     */
    public function testDeleteActionNoPrevious()
    {
        $busRegId = 15;
        $variationNo = 0;
        $routeNo = 12345;
        $action = 'delete';

        $dataBundle = [
            'properties' => 'ALL',
            'children' => [
                'busNoticePeriod' => [
                    'properties' => 'ALL'
                ],
                'status' => [
                    'properties' => 'ALL'
                ]
            ]
        ];

        //mock rest data
        $mockRestData = [
            'id' => $busRegId,
            'variationNo' => $variationNo,
            'routeNo' => $routeNo
        ];

        //mock plugin manager
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params',
                'FlashMessenger' => 'FlashMessenger',
                'redirect' => 'Redirect',
                'confirm' => 'Confirm'
            ]
        );

        //route params
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('busRegId')->andReturn($busRegId);
        $mockParams->shouldReceive('fromPost')->with('action')->andReturn($action);

        $mockConfirm = $mockPluginManager->get('confirm', '');
        $mockConfirm->shouldReceive('confirm')->withAnyArgs()->andReturn(true);

        $mockFlash = $mockPluginManager->get('FlashMessenger', '');
        $mockFlash->shouldReceive('addErrorMessage');

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRouteAjax')
            ->with('licence/bus', ['action' => 'bus', 'busRegId' => null], ['code' => '303'], true)
            ->andReturn('redirectResponse');

        $this->sut->setPluginManager($mockPluginManager);

        //rest call to return prohibition data
        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper
            ->shouldReceive('makeRestCall')
            ->with('BusReg', 'GET', ['id' => $busRegId], $dataBundle)
            ->andReturn($mockRestData);
        $mockRestHelper
            ->shouldReceive('makeRestCall')
            ->with('BusReg', 'DELETE', ['id' => $busRegId], "")
            ->andReturn([]);

        //mock service manager
        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);

        $this->sut->setServiceLocator($mockServiceManager);

        $this->assertEquals('redirectResponse', $this->sut->deleteAction());
    }

    /**
     * Tests the delete action produces the confirm dialogue
     */
    public function testDeleteActionCancelled()
    {
        $busRegId = 15;
        $action = 'delete';

        $mockBusReg = [];

        //mock plugin manager
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params',
                'FlashMessenger' => 'FlashMessenger',
                'redirect' => 'Redirect'
            ]
        );

        $view = new \Zend\View\Model\ViewModel();

        //route params
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('busRegId')->andReturn($busRegId);
        $mockParams->shouldReceive('fromPost')->with('action')->andReturn($action);

        $confirmMock = m::mock('\Olcs\Mvc\Controller\Plugin\Confirm');
        $confirmMock->shouldReceive('__invoke')->andReturn($view);

        $mockPluginManager->shouldReceive('get')->with('confirm', '')->andReturn($confirmMock);

        $this->sut->setPluginManager($mockPluginManager);

        $mockBusRegService = m::mock('Common\Service\Data\BusReg');
        $mockBusRegService->shouldReceive('fetchOne')->with($busRegId)->andReturn($mockBusReg);

        $nav = m::mock('\Zend\Navigation\Navigation')
            ->shouldReceive('findOneBy')
            ->with('id', 'licence_bus_processing')
            ->getMock();

        $scripts = m::mock('\Common\Service\Script\ScriptFactory');
        $scripts->shouldReceive('loadFiles')->with($this->sut->getInlineScripts());

        //mock service manager
        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')
            ->with('Common\Service\Data\BusReg')
            ->andReturn($mockBusRegService);

        $mockServiceManager->shouldReceive('get')->with('Navigation')->andReturn($nav);
        $mockServiceManager->shouldReceive('get')->with('Script')->andReturn($scripts);

        $this->sut->setServiceLocator($mockServiceManager);

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $this->sut->deleteAction());
    }

    /**
     * Tests redirectToIndex
     */
    public function testRedirectToIndex()
    {
        //mock plugin manager
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'redirect' => 'Redirect',
            ]
        );

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRouteAjax')
            ->with(null, ['action' => 'index'], ['code' => '303'], true)
            ->andReturn('redirectResponse');

        $this->sut->setPluginManager($mockPluginManager);

        $this->assertEquals('redirectResponse', $this->sut->redirectToIndex());
    }
}
