<?php

/**
 * CaseController Test
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace OlcsTest\Controller;

use Olcs\Controller\Cases\CaseController;
use Mockery as m;
use Olcs\TestHelpers\ControllerRouteMatchHelper;
use Olcs\TestHelpers\ControllerAddEditHelper;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use OlcsTest\Traits\MockeryTestCaseTrait;
use OlcsTest\Bootstrap;

/**
 * CaseController Test
 */
class CaseControllerTest extends ControllerTestAbstract
{
    use MockeryTestCaseTrait;

    protected $testClass = 'Olcs\Controller\Cases\CaseController';

    protected $proxyMethdods = [
        'redirectAction' => 'redirectToRoute',
        'indexAction' => 'redirectToRoute'
    ];

    public function setUp()
    {
        $this->markTestSkipped();
    }

    public function testGetCase()
    {
        $this->markTestSkipped();

        $caseId = 29;
        $case = ['id' => 29];

        $mockService = m::mock('Olcs\Service\Data\Cases');
        $mockService->shouldReceive('fetchCaseData')->with($caseId)->andReturn($case);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Olcs\Service\Data\Cases')->andReturn($mockService);

        $sut = $this->getSut();
        $sut->setServiceLocator($mockSl);

        $this->assertEquals($case, $sut->getCase($caseId));
    }

    public function testGetCaseWithId()
    {
        $caseId = 29;
        $case = ['id' => 29];

        $sut = $this->getSut();

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(['params' => 'Params']);
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);

        $mockService = m::mock('Olcs\Service\Data\Cases');
        $mockService->shouldReceive('fetchCaseData')->with($caseId)->andReturn($case);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Olcs\Service\Data\Cases')->andReturn($mockService);

        $sut->setServiceLocator($mockSl);
        $sut->setPluginManager($mockPluginManager);

        $this->assertEquals($case, $sut->getCase());
    }

    /**
     * Tests the redirectToIndex method
     */
    public function testRedirectToIndex()
    {
        $sut = $this->getSut();

        $caseId = 28;

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            ['params' => 'Params', 'redirect' => 'Redirect']
        );

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromQuery')->with('case', null)->andReturn($caseId);

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRouteAjax')->with(
            'case',
            ['action' => 'details', $sut->getIdentifierName() => $caseId],
            ['code' => '303'], true
        )->andReturn('redirectResponse');

        $mockPluginManager->shouldReceive('get')->with('redirect')->andReturn($mockRedirect);

        $sut->setPluginManager($mockPluginManager);

        $this->assertEquals('redirectResponse', $sut->redirectToIndex());
    }

    /**
     * Tests redirectToIndex throws the correct exception if there isn't a case id
     *
     * @expectedException \LogicException
     */
    public function testRedirectToIndexThrowsException()
    {
        $sut = $this->getMock('Olcs\Controller\Cases\CaseController', ['getQueryOrRouteParam']);
        $sut->expects($this->once())->method('getQueryOrRouteParam')->will($this->returnValue(0));

        $sut->redirectToIndex();
    }

    /**
     * Tests the edit action correctly passed the amended page layouts
     */
    public function testEditAction()
    {
        $caseId = 28;
        $licence = 7;
        $mockResult = ['id' => $caseId];
        $pageLayout = 'crud-form';
        $pageLayoutInner = null;
        $action = 'edit';

        $sut = $this->getSut();
        $sut->setPageLayout($pageLayout);
        $sut->setPageLayoutInner($pageLayoutInner);

        $addEditHelper = new ControllerAddEditHelper();

        $mockPluginManager = $addEditHelper->getPluginManager(
            $action,
            $caseId,
            $licence,
            $sut->getIdentifierName(),
            $caseId
        );

        $sut->setPluginManager($mockPluginManager);

        $mockCaseDataService = m::mock('Olcs\Service\Data\Cases');
        $mockCaseDataService->shouldReceive('fetchCaseData')->with($caseId)->andReturn($mockResult);

        //mock service manager
        $mockServiceManager = $addEditHelper->getServiceManager($action, $mockResult, 'cases');
        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')->with('Olcs\Service\Data\Cases')->andReturn($mockCaseDataService);
        $sut->setServiceLocator($mockServiceManager);

        $form = $addEditHelper->getForm();
        $fieldset = new \Zend\Form\Fieldset('fields');
        $field = new \Zend\Form\Element\Select('caseType');

        $fieldset->add($field);
        $form->add($fieldset);

        $view = $sut->editAction();

        $this->createAddEditAssertions('pages/' . $pageLayout, $view, $addEditHelper, $mockServiceManager);
    }

    /**
     * Tests the edit action correctly passed the amended page layouts
     */
    public function testEditActionLicenceNotInRoute()
    {
        $caseId = 28;
        $licence = null;
        $applicationLicence = 7;
        $mockResult = ['id' => $caseId];
        $pageLayout = 'crud-form';
        $pageLayoutInner = null;
        $action = 'edit';

        $applicationData = [
            'licence' => [
                'id' => $applicationLicence
            ]
        ];

        $sut = $this->getSut();
        $sut->setPageLayout($pageLayout);
        $sut->setPageLayoutInner($pageLayoutInner);

        $addEditHelper = new ControllerAddEditHelper();

        $mockPluginManager = $addEditHelper->getPluginManager(
            $action,
            $caseId,
            $licence,
            $sut->getIdentifierName(),
            $caseId
        );

        $sut->setPluginManager($mockPluginManager);

        $applicationService = m::mock('Generic\Service\Data\Application');
        $applicationService->shouldReceive('fetchOne')->andReturn($applicationData);

        $mockCaseDataService = m::mock('Olcs\Service\Data\Cases');
        $mockCaseDataService->shouldReceive('fetchCaseData')->with($caseId)->andReturn($mockResult);

        //mock service manager
        $mockServiceManager = $addEditHelper->getServiceManager($action, $mockResult, 'cases');
        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')->with('Olcs\Service\Data\Cases')->andReturn($mockCaseDataService);
        $mockServiceManager->shouldReceive('get')
            ->with('Generic\Service\Data\Application')
            ->andReturn($applicationService);

        $form = $addEditHelper->getForm();
        $fieldset = new \Zend\Form\Fieldset('fields');
        $field = new \Zend\Form\Element\Select('caseType');

        $fieldset->add($field);
        $form->add($fieldset);

        $sut->setServiceLocator($mockServiceManager);

        $view = $sut->editAction();

        $this->createAddEditAssertions('pages/' . $pageLayout, $view, $addEditHelper, $mockServiceManager);
    }

    /**
     * Tests the add action correctly passed the amended page layouts
     */
    public function testAddAction()
    {
        $caseId = 28;
        $licence = 7;
        $mockResult = [];
        $pageLayout = 'crud-form';
        $pageLayoutInner = null;
        $action = 'add';

        $sut = $this->getSut();

        $sut->setPageLayout($pageLayout);
        $sut->setPageLayoutInner($pageLayoutInner);

        $addEditHelper = new ControllerAddEditHelper();

        $mockPluginManager = $addEditHelper->getPluginManager(
            $action,
            $caseId,
            $licence,
            $sut->getIdentifierName(),
            $caseId
        );

        $sut->setPluginManager($mockPluginManager);

        $mockCaseDataService = m::mock('Olcs\Service\Data\Cases');
        $mockCaseDataService->shouldReceive('fetchCaseData')->with($caseId)->andReturn([]);

        //mock service manager
        $mockServiceManager = $addEditHelper->getServiceManager($action, $mockResult, 'cases');
        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')->with('Olcs\Service\Data\Cases')->andReturn($mockCaseDataService);
        $sut->setServiceLocator($mockServiceManager);

        $form = $addEditHelper->getForm();
        $fieldset = new \Zend\Form\Fieldset('fields');
        $field = new \Zend\Form\Element\Select('caseType');

        $fieldset->add($field);
        $form->add($fieldset);

        $event = $this->routeMatchHelper->getMockRouteMatch(array('action' => 'not-found'));
        $sut->setEvent($event);

        $view = $sut->addAction();
        $this->createAddEditAssertions('pages/' . $pageLayout, $view, $addEditHelper, $mockServiceManager);
    }

    public function documentsActionProvider()
    {
        return [
            [
                'case_t_lic',
                [
                    'licence' => [
                        'id' => 7
                    ]
                ],
                ['licenceId' => 7]
            ], [
                'case_t_tm',
                [
                    'transportManager' => [
                        'id' => 14
                    ]
                ],
                ['tmId' => 14]
            ]
        ];
    }

    /**
     * Tests the document list action
     *
     * @dataProvider documentsActionProvider
     */
    public function testDocumentsAction($caseType, $caseDetails, $caseQuery)
    {
        $this->markTestSkipped();
        $sut = $this->getSut();

        $caseId = 28;

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            ['params' => 'Params', 'url' => 'Url']
        );
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromQuery')->with('case', m::any())->andReturn($caseId);
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);
        $sut->setPluginManager($mockPluginManager);

        // We can mock/stub all the service calls that generate the table and
        // form content, this is all in the DocumentSearchTrait that is well
        // tested elsewhere
        ////////////////////////////////////////////////////////////////////////

        /**
         * @todo These tests require a real service manager to run, as they are not mocking all dependencies,
         * these tests should be addresses
         */
        $sm = Bootstrap::getRealServiceManager();

        // Mock the auth service to allow form test to pass through uninhibited
        $mockAuthService = m::mock();
        $mockAuthService->shouldReceive('isGranted')
            ->with('internal-user')
            ->andReturn(true);
        $mockAuthService->shouldReceive('isGranted')
            ->with('internal-edit')
            ->andReturn(true);

        $sm->setService('ZfcRbac\Service\AuthorizationService', $mockAuthService);

        $tableServiceMock = m::mock('\Common\Service\Table\TableBuilder')
            ->shouldReceive('buildTable')
            ->andReturnSelf()
            ->shouldReceive('render')
            ->getMock();
        $sm->setService('Table', $tableServiceMock);

        $scriptHelperMock = m::mock('\Common\Service\Script\ScriptFactory')
            ->shouldReceive('loadFiles')
            ->with(['documents', 'table-actions'])
            ->getMock();
        $sm->setService('Script', $scriptHelperMock);

        $caseData = array_merge(
            [
                'id' => $caseId,
                'caseType' => [
                    'id' => $caseType
                ]
            ],
            $caseDetails
        );

        $sm->setService('Helper\Rest', $this->getMockRestHelperForDocuments($caseId, $caseQuery));

        $dsm = m::mock('\StdClass')
            ->shouldReceive('get')
            ->with('Olcs\Service\Data\Cases')
            ->andReturn(
                m::mock('Olcs\Service\Data\Cases')
                    ->shouldReceive('fetchCaseData')
                    ->with($caseId)
                    ->andReturn($caseData)
                    ->getMock()
            )
            ->getMock();
        $sm->setService('DataServiceManager', $dsm);

        $sut->setServiceLocator($sm);
        ////////////////////////////////////////////////////////////////////////

        $view = $sut->documentsAction();

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $view);

        $sm->setService('ZfcRbac\Service\AuthorizationService', null);
    }

    /**
     * Return a form that will allow you to do pretty much anything
     */
    protected function getFormStub()
    {
        return m::mock('\Zend\Form\Form')
            ->shouldReceive('get')
            ->andReturn(
                m::mock('\StdClass')
                    ->shouldReceive('setValueOptions')
                    ->andReturnSelf()
                    ->getMock()
            )
            ->getMock()
            ->shouldDeferMissing();
    }

    protected function getMockRestHelperForDocuments($caseId, $caseQuery)
    {
        $caseQuery['caseId'] = $caseId;

        return m::mock('Common\Service\Helper\RestHelperService')
            ->shouldReceive('makeRestCall')
            ->with(
                'DocumentSearchView',
                'GET',
                [
                    'sort' => "issuedDate",
                    'order' => "DESC",
                    'page' => 1,
                    'limit' => 10,
                    $caseQuery
                ],
                m::any() // last param is usually a blank bundle specifier
            )
            ->shouldReceive('makeRestCall')
            ->with(
                'Category',
                'GET',
                [
                    'limit' => 100,
                    'sort' => 'description',
                    'isDocCategory' => true,
                ],
                m::any()
            )
            ->shouldReceive('makeRestCall')
            ->with(
                'SubCategory',
                'GET',
                [
                    'sort'      => 'subCategoryName',
                    'order'     => 'ASC',
                    'page'      => 1,
                    'limit'     => 100,
                    'isDoc'     => true
                ],
                m::any()
            )
            ->shouldReceive('makeRestCall')
            ->with(
                'RefData',
                'GET',
                [
                    'refDataCategoryId' => 'document_type',
                    'limit'=>100,
                    'sort'=>'description'
                ],
                m::any()
            )
            ->getMock();
    }

    public function testDocumentsActionWithUploadRedirectsToUpload()
    {
        $this->sut = $this->getMock(
            $this->testClass,
            array(
                'getRequest',
                'params',
                'redirect',
                'url',
                'getFromRoute',
                'getCase'
            )
        );

        $request = $this->getMock('\stdClass', ['isPost', 'getPost']);
        $request->expects($this->any())
            ->method('isPost')
            ->will($this->returnValue(true));
        $this->sut->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $params = $this->getMock('\stdClass', ['fromPost', 'fromQuery', 'fromRoute']);
        $params->expects($this->once())
            ->method('fromPost')
            ->with('action')
            ->will($this->returnValue('upload'));
        $this->sut->expects($this->any())
            ->method('params')
            ->will($this->returnValue($params));

        $this->sut->expects($this->any())
            ->method('getFromRoute')
            ->with('case')
            ->will($this->returnValue(1234));

        $redirect = $this->getMock('\stdClass', ['toRoute']);
        $redirect->expects($this->once())
            ->method('toRoute')
            ->with(
                'case_licence_docs_attachments/upload',
                ['case' => 1234]
            );
        $this->sut->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $response = $this->sut->documentsAction();
    }

    /**
     * Adds the assertions for the add and edit form tests
     *
     * @param $pageLayout
     * @param $view
     * @param $addEditHelper
     * @param $mockServiceManager
     */
    private function createAddEditAssertions($pageLayout, $view, $addEditHelper, $mockServiceManager)
    {
        $viewChildren = $view->getChildren();
        $headerView = $viewChildren[0];
        $layoutView = $viewChildren[1];

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $view);
        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $headerView);
        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $layoutView);

        $this->assertEquals($view->getTemplate(), 'layout/base');
        $this->assertEquals($headerView->getTemplate(), 'partials/header');
        $this->assertEquals($layoutView->getTemplate(), $pageLayout);

        $this->assertEquals(
            $addEditHelper->getForm(),
            $mockServiceManager->get('viewHelperManager')->get('placeholder')->getContainer('form')->getValue()
        );
    }

    /**
     * Get a new SUT
     *
     * @throws \Exception
     * @return \Olcs\Controller\Cases\CaseController
     */
    public function getSut()
    {
        $this->routeMatchHelper = new ControllerRouteMatchHelper();
        $this->pluginManagerHelper = new ControllerPluginManagerHelper();

        $sut = new CaseController();

        if (false === ($sut instanceof \Common\Controller\AbstractSectionController)) {
            throw new \Exception('This system under test does not extend for the correct ultimate abstract');
        }

        if (false === ($sut instanceof \Common\Controller\CrudInterface)) {
            throw new \Exception('This system under test does not implement the correct interface');
        }

        return $sut;
    }

    public function testAlterFormForLicence()
    {
        $caseId = 10;
        $case = ['id' => $caseId];

        $sut = $this->getSut();

        $pluginHelper = new ControllerPluginManagerHelper();
        $mockPluginManager = $pluginHelper->getMockPluginManager(['params' => 'Params']);

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);
        $mockParams->shouldReceive('fromRoute')->with('licence', '')->andReturn(1);
        $mockParams->shouldReceive('fromRoute')->with('application', '')->andReturnNull();
        $mockParams->shouldReceive('fromRoute')->with('transportManager', '')->andReturnNull();

        $sut->setPluginManager($mockPluginManager);

        $mockCaseDataService = m::mock('Olcs\Service\Data\Cases');
        $mockCaseDataService->shouldReceive('fetchCaseData')->with($caseId)->andReturn($case);

        //mock service manager
        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')->with('Olcs\Service\Data\Cases')->andReturn($mockCaseDataService);

        $sut->setServiceLocator($mockServiceManager);

        $form = new \Zend\Form\Form();

        $fieldset = new \Zend\Form\Fieldset('fields');
        $field = new \Zend\Form\Element\Select('caseType');

        $field->setValueOptions(
            [
                'case_t_imp' => 'impounding',
                'case_t_app' => 'application',
                'case_t_lic' => 'licence',
                'case_t_tm' => 'transortmanager'
            ]
        );
        $fieldset->add($field);
        $form->add($fieldset);
        $form = $sut->alterForm($form);

        $newOptions = $form->get('fields')
            ->get('caseType')
            ->getValueOptions();

        $this->assertNotContains('case_t_app', array_keys($newOptions));
        $this->assertNotContains('case_t_tm', array_keys($newOptions));
    }

    public function testAlterFormForApplication()
    {
        $caseId = 10;
        $case = ['id' => $caseId];

        $sut = $this->getSut();

        $pluginHelper = new ControllerPluginManagerHelper();
        $mockPluginManager = $pluginHelper->getMockPluginManager(['params' => 'Params']);

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);
        $mockParams->shouldReceive('fromRoute')->with('licence', '')->andReturnNull();
        $mockParams->shouldReceive('fromRoute')->with('application', '')->andReturn(1);
        $mockParams->shouldReceive('fromRoute')->with('transportManager', '')->andReturnNull();

        $sut->setPluginManager($mockPluginManager);

        $mockCaseDataService = m::mock('Olcs\Service\Data\Cases');
        $mockCaseDataService->shouldReceive('fetchCaseData')->with($caseId)->andReturn($case);

        //mock service manager
        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')->with('Olcs\Service\Data\Cases')->andReturn($mockCaseDataService);

        $sut->setServiceLocator($mockServiceManager);

        $form = new \Zend\Form\Form();

        $fieldset = new \Zend\Form\Fieldset('fields');
        $field = new \Zend\Form\Element\Select('caseType');

        $field->setValueOptions(
            [
                'case_t_imp' => 'impounding',
                'case_t_app' => 'application',
                'case_t_lic' => 'licence',
                'case_t_tm' => 'transortmanager'
            ]
        );
        $fieldset->add($field);
        $form->add($fieldset);
        $form = $sut->alterForm($form);

        $newOptions = $form->get('fields')
            ->get('caseType')
            ->getValueOptions();

        $this->assertNotContains('case_t_imp', array_keys($newOptions));
        $this->assertNotContains('case_t_lic', array_keys($newOptions));
        $this->assertNotContains('case_t_tm', array_keys($newOptions));
    }

    public function testAlterFormForTransportManager()
    {
        $caseId = 10;
        $case = ['id' => $caseId];

        $sut = $this->getSut();

        $pluginHelper = new ControllerPluginManagerHelper();
        $mockPluginManager = $pluginHelper->getMockPluginManager(['params' => 'Params']);

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);
        $mockParams->shouldReceive('fromRoute')->with('licence', '')->andReturnNull();
        $mockParams->shouldReceive('fromRoute')->with('application', '')->andReturnNull();
        $mockParams->shouldReceive('fromRoute')->with('transportManager', '')->andReturn(1);

        $sut->setPluginManager($mockPluginManager);

        $mockCaseDataService = m::mock('Olcs\Service\Data\Cases');
        $mockCaseDataService->shouldReceive('fetchCaseData')->with($caseId)->andReturn($case);

        //mock service manager
        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')->with('Olcs\Service\Data\Cases')->andReturn($mockCaseDataService);

        $sut->setServiceLocator($mockServiceManager);

        $form = new \Zend\Form\Form();

        $fieldset = new \Zend\Form\Fieldset('fields');
        $field = new \Zend\Form\Element\Select('caseType');

        $field->setValueOptions(
            [
                'case_t_imp' => 'impounding',
                'case_t_app' => 'application',
                'case_t_lic' => 'licence',
                'case_t_tm' => 'transortmanager'
            ]
        );
        $fieldset->add($field);
        $form->add($fieldset);
        $form = $sut->alterForm($form);

        $newOptions = $form->get('fields')
            ->get('caseType')
            ->getValueOptions();

        $this->assertNotContains('case_t_imp', array_keys($newOptions));
        $this->assertNotContains('case_t_lic', array_keys($newOptions));
        $this->assertNotContains('case_t_app', array_keys($newOptions));
    }

    public function testDetailsAction()
    {
        $sut = $this->getSut();

        $caseId = 24;
        $restResult = [
            'id' => $caseId,
        ];

        $mockViewHelperManager = new \Zend\View\HelperPluginManager();

        $mockRouteMatch = m::mock('Zend\Mvc\Router\RouteMatch');
        $mockRouteMatch->shouldReceive('getParams')->with()->andReturn(['action' => 'close']);
        $mockRouteMatch->shouldReceive('getMatchedRouteName')->with()->andReturn('');

        $mockCaseService = m::mock('Olcs\Service\Data\Cases');
        $mockCaseService->shouldReceive('canReopen')->with($caseId)->andReturn(false);
        $mockCaseService->shouldReceive('canClose')->with($caseId)->andReturn(true);

        $mockApplicationService = m::mock('Zend\Mvc\Application');
        $mockApplicationService->shouldReceive('getMvcEvent')->andReturnSelf();
        $mockApplicationService->shouldReceive('getRouteMatch')->andReturn($mockRouteMatch);

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->withAnyArgs()->andReturn($restResult);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);
        $mockServiceManager->shouldReceive('get')->with('Olcs\Service\Data\Cases')->andReturn($mockCaseService);
        $mockServiceManager->shouldReceive('get')->with('Application')->andReturn($mockApplicationService);
        $mockServiceManager->shouldReceive('get')->with('viewHelperManager')->andReturn($mockViewHelperManager);

        $pluginHelper = new ControllerPluginManagerHelper();
        $mockPluginManager = $pluginHelper->getMockPluginManager(['params' => 'Params']);

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);

        $sut->setPluginManager($mockPluginManager);
        $sut->setServiceLocator($mockServiceManager);

        $sut->detailsAction();
    }
}
