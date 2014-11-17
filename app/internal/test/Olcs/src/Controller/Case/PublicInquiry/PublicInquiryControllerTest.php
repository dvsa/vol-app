<?php

/**
 * Public Inquiry Test Controller
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Mockery as m;
use \Olcs\TestHelpers\ControllerPluginManagerHelper;
use \Olcs\TestHelpers\ControllerRouteMatchHelper;

/**
 * Public Inquiry Test Controller
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class PublicInquiryControllerTest extends AbstractHttpControllerTestCase
{
    /**
     * @var ControllerPluginManagerHelper
     */
    protected $pluginManagerHelper;

    public function __construct()
    {
        $this->pluginManagerHelper = new ControllerPluginManagerHelper();
        $this->routeMatchHelper = new ControllerRouteMatchHelper();
    }

    public function setUp()
    {
        $this->sut = new \Olcs\Controller\Cases\PublicInquiry\PublicInquiryController();

        $this->setApplicationConfig(
            include __DIR__ . '/../../../../../../' . 'config/application.config.php'
        );

        parent::setUp();
    }

    public function testRedirectToIndex()
    {
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(['redirect' => 'Redirect']);
        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRoute')->with(
            'case_pi',
            ['action'=>'details'],
            ['code' => '303'], true
        )->andReturn('redirectResponse');

        $mockPluginManager->shouldReceive('get')->with('redirect')->andReturn($mockRedirect);

        $this->sut->setPluginManager($mockPluginManager);

        $this->assertEquals('redirectResponse', $this->sut->redirectToIndex());
    }

    public function testProcessDataMapForSave()
    {
        $mockDataService = m::mock('Common\Service\Helper\DataHelperService');
        $mockDataService->shouldReceive('processDataMap')->andReturn([]);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('Helper\Data')->andReturn($mockDataService);

        $this->sut->setServiceLocator($mockSl);

        $caseId = 99;
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(['params' => 'Params']);
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);

        $this->sut->setPluginManager($mockPluginManager);

        $result = $this->sut->processDataMapForSave(['oldData' => []], []);

        $this->assertArrayHasKey('case', $result);
    }

    public function testDetailsActionEmptyPi()
    {
        $event = $this->routeMatchHelper->getMockRouteMatch(array('controller' => 'public_inquiry'));
        $this->sut->setEvent($event);

        $mockPi = [];

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->withAnyArgs()->andReturn($mockPi);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);

        $placeholder = new \Zend\View\Helper\Placeholder();

        $mockViewHelperManager = new \Zend\View\HelperPluginManager();
        $mockViewHelperManager->setService('placeholder', $placeholder);

        $mockServiceManager->shouldReceive('get')->with('viewHelperManager')->andReturn($mockViewHelperManager);

        $this->sut->setServiceLocator($mockServiceManager);

        $this->sut->detailsAction();

        $this->assertEquals(
            $mockPi,
            $mockViewHelperManager->get('placeholder')->getContainer('details')->getValue()
        );

        $this->assertEquals(
            $mockPi,
            $mockViewHelperManager->get('placeholder')->getContainer('pi')->getValue()
        );
    }

    public function testGetDetailsActionPiSet()
    {
        $caseId = 99;

        $event = $this->routeMatchHelper->getMockRouteMatch(array('controller' => 'public_inquiry'));
        $this->sut->setEvent($event);

        $this->sut->getRequest()->setMethod('get');

        $mockPi = ['Results' => [0 => ['id' => 1]], 'Count' => 1];

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->withAnyArgs()->andReturn($mockPi);

        $mockPiService = m::mock('Common\Service\Data\Pi');
        $mockPiService->shouldReceive('canReopen')->with(1)->andReturn(false);

        $mockSlaService = m::mock('Common\Service\Data\Sla');
        $mockSlaService->shouldReceive('setContext')->withAnyArgs();
        $mockSlaService->shouldReceive('fetchBusRules')->withAnyArgs()->andReturn([]);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);
        $mockServiceManager->shouldReceive('get')->with('Common\Service\Data\Sla')->andReturn($mockSlaService);
        $mockServiceManager->shouldReceive('get')->with('Olcs\Service\Data\Pi')->andReturn($mockPiService);

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'forward' => 'Forward',
                'params' => 'Params'
            ]
        );
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);

        $mockForward = $mockPluginManager->get('forward', '');
        $mockForward->shouldReceive('dispatch')->withAnyArgs();

        $this->sut->setPluginManager($mockPluginManager);

        $placeholder = new \Zend\View\Helper\Placeholder();

        $mockViewHelperManager = new \Zend\View\HelperPluginManager();
        $mockViewHelperManager->setService('placeholder', $placeholder);

        $mockServiceManager->shouldReceive('get')->with('viewHelperManager')->andReturn($mockViewHelperManager);

        $this->sut->setServiceLocator($mockServiceManager);

        $this->sut->detailsAction();

        $this->assertEquals(
            $mockPi['Results'][0],
            $mockViewHelperManager->get('placeholder')->getContainer('details')->getValue()
        );

        $this->assertEquals(
            $mockPi['Results'][0],
            $mockViewHelperManager->get('placeholder')->getContainer('pi')->getValue()
        );
    }

    public function testPostEditDetailsActionPiSet()
    {
        $caseId = 99;
        $postId = 33;
        $action = 'edit';

        $event = $this->routeMatchHelper->getMockRouteMatch(array('controller' => 'public_inquiry'));
        $this->sut->setEvent($event);

        $this->sut->getRequest()->setMethod('post');

        $mockPi = ['Results' => [0 => ['id' => 1]], 'Count' => 1];

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->withAnyArgs()->andReturn($mockPi);

        $mockSlaService = m::mock('Common\Service\Data\Sla');
        $mockSlaService->shouldReceive('setContext')->withAnyArgs();
        $mockSlaService->shouldReceive('fetchBusRules')->withAnyArgs()->andReturn([]);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);
        $mockServiceManager->shouldReceive('get')->with('Common\Service\Data\Sla')->andReturn($mockSlaService);

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'forward' => 'Forward',
                'params' => 'Params',
                'redirect', 'Redirect'
            ]
        );
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);
        $mockParams->shouldReceive('fromPost')->with('action')->andReturn($action);
        $mockParams->shouldReceive('fromPost')->with('id')->andReturn($postId);

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRoute')->with(
            'case_pi_hearing',
            ['action' => $action, 'id' => $postId, 'pi' => $mockPi['Results'][0]['id']],
            ['code' => '303'],
            true
        )->andReturn('redirectResponse');

        $mockForward = $mockPluginManager->get('forward', '');
        $mockForward->shouldReceive('dispatch')->withAnyArgs();

        $this->sut->setPluginManager($mockPluginManager);

        $placeholder = new \Zend\View\Helper\Placeholder();

        $mockViewHelperManager = new \Zend\View\HelperPluginManager();
        $mockViewHelperManager->setService('placeholder', $placeholder);

        $mockServiceManager->shouldReceive('get')->with('viewHelperManager')->andReturn($mockViewHelperManager);

        $this->sut->setServiceLocator($mockServiceManager);

        $result = $this->sut->detailsAction();

        $this->assertEquals(
            $result,
            'redirectResponse'
        );
    }

    public function testPostAddDetailsActionPiSet()
    {
        $caseId = 99;
        $postId = 33;
        $action = 'add';

        $event = $this->routeMatchHelper->getMockRouteMatch(array('controller' => 'public_inquiry'));
        $this->sut->setEvent($event);

        $this->sut->getRequest()->setMethod('post');

        $mockPi = ['Results' => [0 => ['id' => 1]], 'Count' => 1];

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->withAnyArgs()->andReturn($mockPi);

        $mockSlaService = m::mock('Common\Service\Data\Sla');
        $mockSlaService->shouldReceive('setContext')->withAnyArgs();
        $mockSlaService->shouldReceive('fetchBusRules')->withAnyArgs()->andReturn([]);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);
        $mockServiceManager->shouldReceive('get')->with('Common\Service\Data\Sla')->andReturn($mockSlaService);

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'forward' => 'Forward',
                'params' => 'Params',
                'redirect', 'Redirect'
            ]
        );
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);
        $mockParams->shouldReceive('fromPost')->with('action')->andReturn($action);
        $mockParams->shouldReceive('fromPost')->with('id')->andReturn($postId);

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRoute')->with(
            'case_pi_hearing',
            ['action' => $action, 'id' => null, 'pi' => $mockPi['Results'][0]['id']],
            ['code' => '303'],
            true
        )->andReturn('redirectResponse');

        $mockForward = $mockPluginManager->get('forward', '');
        $mockForward->shouldReceive('dispatch')->withAnyArgs();

        $this->sut->setPluginManager($mockPluginManager);

        $placeholder = new \Zend\View\Helper\Placeholder();

        $mockViewHelperManager = new \Zend\View\HelperPluginManager();
        $mockViewHelperManager->setService('placeholder', $placeholder);

        $mockServiceManager->shouldReceive('get')->with('viewHelperManager')->andReturn($mockViewHelperManager);

        $this->sut->setServiceLocator($mockServiceManager);

        $result = $this->sut->detailsAction();

        $this->assertEquals(
            $result,
            'redirectResponse'
        );
    }

    public function testGetIdToClose()
    {
        $id = 99;
        $caseId = 99;

        $mockPi = ['Results' => [0 => ['id' => $id]], 'Count' => 1];

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->withAnyArgs()->andReturn($mockPi);

        $this->assertEquals($id, $this->sut->getIdToClose($id));

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(['params' => 'Params']);
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);

        $this->sut->setPluginManager($mockPluginManager);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);
        $this->sut->setServiceLocator($mockServiceManager);

        $this->assertEquals($id, $this->sut->getIdToClose());

    }
}
