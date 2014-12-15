<?php

/**
 * Case Stay Test Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Olcs\TestHelpers\ControllerRouteMatchHelper;
use \Olcs\TestHelpers\ControllerPluginManagerHelper;
use Olcs\TestHelpers\ControllerAddEditHelper;
use Mockery as m;

/**
 * Tests the case stay controller
 */
class CrudAbstractTest extends AbstractHttpControllerTestCase
{
    protected $traitsRequired = array(
        'Zend\Log\LoggerAwareTrait',
        'Common\Util\LoggerTrait',
        'Common\Util\FlashMessengerTrait',
        'Common\Util\RestCallTrait',
        'Common\Controller\Traits\ViewHelperManagerAware',
        'Olcs\Controller\Traits\DeleteActionTrait'
    );

    protected $testClass = '\Olcs\Controller\CrudAbstract';

    /**
     * Tests, in isolation, the public getter and setter for placeholderName.
     */
    public function testSetPlaceholderName()
    {
        $identifier = 'identifier1';

        $sut = $this->getSutForIsolatedTest(['getIdentifierName']);
        $sut->expects($this->once())->method('getIdentifierName')->will($this->returnValue($identifier));

        $this->assertEquals($identifier, $sut->getPlaceholderName());

        // Now test getter / setter
        $this->assertEquals('pn', $sut->setPlaceholderName('pn')->getPlaceholderName());
    }

    /**
     * Tests, in isolation, the public getter and setter for IsListResult.
     */
    public function testIsListResult()
    {
        $sut = $this->getSutForIsolatedTest(null);

        $this->assertFalse($sut->isListResult());

        // Now test getter / setter
        $this->assertTrue($sut->setIsListResult(true)->isListResult());
    }

    /**
     * Tests, in isolation, the public getter and setter for IdentifierKey.
     */
    public function testIdentifierKey()
    {
        $sut = $this->getSutForIsolatedTest(null);

        $this->assertEquals('id', $sut->getIdentifierKey());

        // Now test getter / setter

        $this->assertEquals('idKey', $sut->setIdentifierKey('idKey')->getIdentifierKey());
    }

    /**
     * Tests that the method returns value from getQuery method.
     */
    public function testGetQueryOrRouteParamReturnsQueryParam()
    {
        $name = 'name';
        $value = 'value';

        $params = $this->getMock('\Zend\Mvc\Controller\Plugin\Params', ['fromQuery']);
        $params->expects($this->any())->method('fromQuery')
               ->with($name, null)
               ->will($this->returnValue($value));

        $sut = $this->getSutForIsolatedTest(['params']);
        $sut->expects($this->any())->method('params')->will($this->returnValue($params));

        $this->assertEquals($value, $sut->getQueryOrRouteParam($name, null));
    }

    /**
     * Tests that the method returns value from getRoute method.
     */
    public function testGetQueryOrRouteParamReturnsRouteParam()
    {
        $name = 'name';
        $value = 'value';

        $params = $this->getMock('\Zend\Mvc\Controller\Plugin\Params', ['fromQuery', 'fromRoute']);
        $params->expects($this->any())->method('fromQuery')->will($this->returnValue(null));
        $params->expects($this->any())->method('fromRoute')
               ->with($name, null)
               ->will($this->returnValue($value));

        $sut = $this->getSutForIsolatedTest(['params']);
        $sut->expects($this->any())->method('params')->will($this->returnValue($params));

        $this->assertEquals($value, $sut->getQueryOrRouteParam($name, null));
    }

    /**
     * Tests that the method returns the default value
     */
    public function testGetQueryOrRouteParamReturnsDefaultParam()
    {
        $name = 'name';
        $default = 'default';

        $params = $this->getMock('\Zend\Mvc\Controller\Plugin\Params', ['fromQuery', 'fromRoute']);
        $params->expects($this->any())->method('fromQuery')->will($this->returnValue(null));
        $params->expects($this->any())->method('fromRoute')->will($this->returnValue(null));

        $sut = $this->getSutForIsolatedTest(['params']);
        $sut->expects($this->any())->method('params')->will($this->returnValue($params));

        $this->assertEquals($default, $sut->getQueryOrRouteParam($name, $default));
    }

    /**
     * Tests the Index Action. I am confident that the abtsract methds
     * this method relies on are tested in their own right. So for
     * the purpose of this test, I am only concerned with this method.
     */
    public function testIndexAction()
    {
        $id = 1;

        $view = $this->getMock('\Zend\View\View', ['setTemplate']);
        $view->expects($this->once())
             ->method('setTemplate')
             ->with('crud/index')
             ->will($this->returnSelf());

        $sut = $this->getSutForIsolatedTest(
            ['getView', 'getIdentifierName', 'checkForCrudAction', 'buildTableIntoView', 'renderView']
        );
        $sut->expects($this->once())->method('getView')
            ->will($this->returnValue($view));
        $sut->expects($this->once())->method('getIdentifierName')
            ->will($this->returnValue($id));
        $sut->expects($this->once())->method('checkForCrudAction')
            ->with(null, [], $id)->will($this->returnValue(null));
        $sut->expects($this->once())->method('buildTableIntoView');
        $sut->expects($this->once())->method('renderView')
            ->with($view)->will($this->returnValue($view));

        $this->assertSame($view, $sut->indexAction());
    }

    /**
     * Tests Get List Vars
     */
    public function testGetListVars()
    {
        $this->assertEquals([], $this->getSutForIsolatedTest(null)->getListVars());
    }

    /**
     * Build table into view adds a table into a view helper.
     */
    public function testBuildTableIntoView()
    {
        $service = 'TestingService';
        $params = [
            'one' => '1', 'two' => '2'
        ];
        $data = ['data' => 'Test Data'];

        $placeholder = new \Zend\View\Helper\Placeholder();

        $mockViewHelperManager = new \Zend\View\HelperPluginManager();
        $mockViewHelperManager->setService('placeholder', $placeholder);

        $sut = $this->getSutForIsolatedTest(
            array('getTableParams', 'getService', 'getDataBundle',
                'getTableName', 'makeRestCall', 'getTable', 'getViewHelperManager', 'alterTable')
        );
        $sut->expects($this->once())->method('getTableParams')->will($this->returnValue($params));
        $sut->expects($this->once())->method('getService')->will($this->returnValue($service));
        $sut->expects($this->once())->method('getDataBundle')->will($this->returnValue(['bundle']));
        $sut->expects($this->once())->method('getTableName')->will($this->returnValue('tableNameTesting'));

        $sut->expects($this->once())->method('makeRestCall')
            ->with($service, 'GET', $params, ['bundle'])
            ->will($this->returnValue($data));

        $sut->expects($this->once())->method('getViewHelperManager')->will($this->returnValue($mockViewHelperManager));

        $sut->expects($this->once())->method('getTable')
            ->with('tableNameTesting', $data, $params)
            ->will($this->returnValue('populatedTable'));

        $sut->expects($this->once())->method('alterTable')
            ->with('populatedTable')
            ->will($this->returnValue('alteredTable'));

        $this->assertEquals(null, $sut->buildTableIntoView());

        $this->assertEquals(
            'alteredTable',
            $mockViewHelperManager->get('placeholder')->getContainer('table')->getValue()
        );
    }

    /**
     * Tests the setPlaceholder action method.
     */
    public function testSetPlaceholder()
    {
        $namespace = 'CR TestingNamespace';
        $value = 'CR Testing Value';

        $sut = $this->getSutForIsolatedTest(['getViewHelperManager']);

        $placeholder = new \Zend\View\Helper\Placeholder();
        $mockViewHelperManager = new \Zend\View\HelperPluginManager();
        $mockViewHelperManager->setService('placeholder', $placeholder);
        $sut->expects($this->once())->method('getViewHelperManager')
            ->will($this->returnValue($mockViewHelperManager));

        $this->assertEquals(null, $sut->setPlaceholder($namespace, $value));

        $this->assertEquals(
            $value,
            $mockViewHelperManager->get('placeholder')->getContainer($namespace)->getValue()
        );
    }

    /**
     * Unit test for getTableParams.
     */
    public function testGetTableParams()
    {
        $case = '1';

        $listVars = array('case');

        $valueMap = array(
            'page'  => array('page', 1, '1'),
            'sort'  => array('sort', 'id', 'id'),
            'order' => array('order', 'DESC', 'ASC'),
            'limit' => array('limit', 10, '10'),
            'case'  => array('case', null, $case)
        );

        $params = array_map(
            function ($element) {
                return $element[2];
            },
            $valueMap
        );

        $sut = $this->getSutForIsolatedTest(
            array('getQueryOrRouteParam', 'getListVars', 'initTable')
        );
        $sut->expects($this->any())->method('getQueryOrRouteParam')
            ->will($this->returnValueMap($valueMap));

        $sut->expects($this->any())->method('getListVars')
            ->will($this->returnValue($listVars));

        $this->assertEquals($params, $sut->getTableParams());
    }

    /**
     * Tests the edit action
     */
    public function testEditAction()
    {
        $caseId = 28;
        $licence = 7;
        $id = 1;
        $mockResult = ['id' => $id];
        $action = 'edit';
        $pageLayoutInner = 'view-new/layouts/case-inner-layout';

        $sut = $this->getSutForIsolatedTest();
        $sut->setPageLayoutInner($pageLayoutInner);

        $addEditHelper = new ControllerAddEditHelper();

        $mockPluginManager = $addEditHelper->getPluginManager(
            $action,
            $caseId,
            $licence,
            $sut->getIdentifierName(),
            $id
        );

        $sut->setPluginManager($mockPluginManager);

        $mockServiceManager = $addEditHelper->getServiceManager($action, $mockResult, null);
        $sut->setServiceLocator($mockServiceManager);

        $view = $sut->editAction();
        $this->createAddEditAssertions($pageLayoutInner, $view, $addEditHelper, $mockServiceManager);
    }

    /**
     * Tests the add action correctly passed the amended page layouts
     */
    public function testAddAction()
    {
        $caseId = 28;
        $licence = 7;
        $id = 1;
        $mockResult = [];
        $action = 'add';
        $pageLayoutInner = 'view-new/layouts/case-inner-layout';

        $sut = $this->getSutForIsolatedTest();
        $sut->setPageLayoutInner($pageLayoutInner);

        $addEditHelper = new ControllerAddEditHelper();

        $mockPluginManager = $addEditHelper->getPluginManager(
            $action,
            $caseId,
            $licence,
            $sut->getIdentifierName(),
            $id
        );

        $sut->setPluginManager($mockPluginManager);

        $mockServiceManager = $addEditHelper->getServiceManager($action, $mockResult, null);
        $sut->setServiceLocator($mockServiceManager);

        $event = $this->routeMatchHelper->getMockRouteMatch(array('action' => 'not-found'));
        $sut->setEvent($event);

        $view = $sut->addAction();
        $this->createAddEditAssertions($pageLayoutInner, $view, $addEditHelper, $mockServiceManager);
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
        $innerView = $layoutView->getChildren();

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $view);
        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $headerView);
        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $layoutView);
        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $innerView[0]);

        $this->assertEquals($view->getTemplate(), 'layout/base');
        $this->assertEquals($headerView->getTemplate(), 'view-new/partials/header');
        $this->assertEquals($layoutView->getTemplate(), $pageLayout);
        $this->assertEquals($innerView[0]->getTemplate(), 'crud/form');

        $this->assertEquals(
            $addEditHelper->getForm(),
            $mockServiceManager->get('viewHelperManager')->get('placeholder')->getContainer('form')->getValue()
        );
    }

    /**
     * Isolated test for the redirect action method.
     */
    public function testRedirectAction()
    {
        $identifierName = 'id';
        $identifier = '1';

        $redirect = $this->getMock('stdClass', ['toRoute']);
        $redirect->expects($this->once())
                 ->method('toRoute')
                 ->with(null, ['action' => 'index', $identifierName => $identifier], true)
                 ->will($this->returnValue('toRoute'));

        $sut = $this->getSutForIsolatedTest(['redirect', 'getIdentifierName', 'getIdentifier']);
        $sut->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $sut->expects($this->once())
            ->method('getIdentifier')
            ->will($this->returnValue($identifier));

        $sut->expects($this->once())
            ->method('getIdentifierName')
            ->will($this->returnValue($identifierName));

        $this->assertEquals('toRoute', $sut->redirectAction());
    }

    /**
     * Isolated behaviour test.
     */
    public function testSaveThis()
    {
        $formName = 'MyFormName';
        $callbackMethodName = 'myCallBackSaveMethod';
        $dataForForm = ['id' => '1234', 'field' => 'value'];

        $form = $this->getMock('Zend\Form\Form', null);

        $view = $this->getMock('Zend\View\View', ['setTemplate']);
        $view->expects($this->once())->method('setTemplate')
                                     ->with($this->equalTo('crud/form'))->will($this->returnSelf());

        $sut = $this->getSutForIsolatedTest(
            [
                'generateFormWithData',
                'getFormName',
                'getFormCallback',
                'getDataForForm',
                'getView',
                'setPlaceholder',
                'renderView'
            ]
        );
        $sut->expects($this->once())->method('getFormName')->will($this->returnValue($formName));
        $sut->expects($this->once())->method('getFormCallback')->will($this->returnValue($callbackMethodName));
        $sut->expects($this->once())->method('getDataForForm')->will($this->returnValue($dataForForm));

        $sut->expects($this->once())->method('getView')->will($this->returnValue($view));

        $sut->expects($this->once())->method('generateFormWithData')
                                    ->with($formName, $callbackMethodName, $dataForForm)
                                    ->will($this->returnValue($form));

        $sut->expects($this->once())->method('setPlaceholder')
                                    ->with('form', $form);

        $sut->expects($this->once())->method('renderView')
                                    ->with($view, null, null)
                                    ->will($this->returnValue($view));

        $this->assertSame($view, $sut->saveThis());

    }

    /**
     * Tests fromRoute
     */
    public function testFromRoute()
    {
        $name = 'name';
        $value = 'value';

        $sut = $this->getSutForIsolatedTest(['getFromRoute']);
        $sut->expects($this->any())->method('getFromRoute')
            ->with($name, null)
            ->will($this->returnValue($value));

        $this->assertEquals($value, $sut->fromRoute($name, null));
    }

    /**
     * Tests fromPost
     */
    public function testFromPost()
    {
        $name = 'namePost';
        $value = 'namePost';

        $sut = $this->getSutForIsolatedTest(['getFromPost']);
        $sut->expects($this->any())->method('getFromPost')
            ->with($name, null)
            ->will($this->returnValue($value));

        $this->assertEquals($value, $sut->fromPost($name, null));
    }

    /**
     * Isolated test for replaceIds method.
     */
    public function testReplaceIds()
    {
        $sut = $this->getSutForIsolatedTest(null);

        $idsToConvert = ['case'];

        $data = [
            'case' => [
                'id' => '1',
                'name' => 'hello'
            ],
            'licence' => [
                'id' => '2',
                'name' => 'hello licence'
            ],
            'id' => '2',
            'name' => 'top'
        ];

        $expected = array(
            'case' => '1',
            'licence' => [
                'id' => '2',
                'name' => 'hello licence'
            ],
            'id' => '2',
            'name' => 'top'
        );

        $this->assertEquals($expected, $sut->replaceIds($data, $idsToConvert));
    }

    /**
     * Tests getViewHelperManager
     */
    public function testGetViewHelperManager()
    {
        $viewHelperManager = 'viewHelperManager';

        $serviceManager = $this->getMock('Zend\ServiceManager\ServiceManager', null);
        $serviceManager->setService('viewHelperManager', $viewHelperManager);

        $sut = $this->getSutForIsolatedTest(['getServiceLocator']);
        $sut->expects($this->any())->method('getServiceLocator')->will($this->returnValue($serviceManager));

        $this->assertSame($viewHelperManager, $sut->getViewHelperManager());
    }

    /**
     * Isolated test for Set Navigation to Current Location functionality.
     */
    public function testSetNavigationCurrentLocation()
    {
        $page = new \Zend\Navigation\Page\Uri();
        $page->setUri('/url-test');
        $page->setId('nav-id-test');

        $sut = $this->getSutForIsolatedTest(null);

        $nav = new \Zend\Navigation\Navigation();
        $nav->addPage($page);

        $sl = new \Zend\ServiceManager\ServiceManager();
        $sl->setService('Navigation', $nav);

        $sut->setServiceLocator($sl);

        $sut->setNavigationId('nav-id-test');

        $this->assertTrue($sut->setNavigationCurrentLocation());

        $this->assertEquals(
            'nav-id-test',
            $sut->getServiceLocator()->get('Navigation')->findOneBy('active', 1)->getId()
        );
    }

    /**
     * Tests process load for an existing record.
     */
    public function testProcessLoadWithId()
    {
        $bundle = array(
            'properties' => 'ALL',
            'children' => array(
                'case' => ['id', 'name'],
            )
        );

        $data = array(
            'id' => '12345',
            'case' => array(
                'id' => '1234'
            )
        );

        $result = array(
            'id' => '12345',
            'case' => '1234'
        );

        $result['fields'] = $result;
        $result['base'] = $result;

        $sut = $this->getSutForIsolatedTest(['getDataBundle', 'getQueryOrRouteParam']);
        $sut->expects($this->once())->method('getDataBundle')
            ->will($this->returnValue($bundle));

        $this->assertEquals($result, $sut->processLoad($data));
    }

    /**
     * Tests the process load method on a save for a new record.
     */
    public function testProcessLoadWithoutId()
    {
        $data = array();

        $result = array('case' => '1234');
        $result['fields']['case'] = '1234';
        $result['base']['case'] = '1234';

        $sut = $this->getSutForIsolatedTest(['getQueryOrRouteParam']);
        $sut->expects($this->once())->method('getQueryOrRouteParam')
            ->with('case')->will($this->returnValue('1234'));

        $this->assertEquals($result, $sut->processLoad($data));
    }

    /**
     * Tests buildCommentsBoxIntoView
     */
    public function testBuildCommentsBoxIntoView()
    {
        $commentBoxName = 'prohibitionNotes';

        $case = [
            'id' => '12345',
            'version' => '1',
            $commentBoxName => 'comment text'
        ];

        $data = [];
        $data['fields']['id'] = $case['id'];
        $data['fields']['version'] = $case['version'];
        $data['fields']['comment'] = $case[$commentBoxName];

        $form = $this->getMock('\Zend\Form\Form', ['setData']);

        $sut = $this->getSutForIsolatedTest(['generateForm', 'getCase', 'setPlaceholder']);

        $sut->expects($this->exactly(1))->method('getCase')
            ->will($this->returnValue($case));

        $sut->expects($this->exactly(1))->method('generateForm')
            ->will($this->returnValue($form));

        $sut->expects($this->exactly(1))->method('setPlaceholder')
            ->with('comments', $form);

        $sut->setCommentBoxName($commentBoxName);

        $this->assertEquals(null, $sut->buildCommentsBoxIntoView());
    }

    /**
     * Tests processCommentForm
     */
    public function testProcessCommentForm()
    {
        $commentsBoxName = 'commentsBox';

        $input = [
            'fields' => [
                'id' => '1',
                'version' => '2',
                'comment' => 'myComment'
            ]
        ];

        $output = [
            'id' => '1',
            'version' => '2',
            $commentsBoxName => 'myComment'
        ];

        $sut = $this->getSutForIsolatedTest(['save', 'addSuccessMessage', 'redirectToIndex']);
        $sut->expects($this->once())->method('save')->with($output);
        $sut->expects($this->once())->method('addSuccessMessage')->with('Comments updated successfully');

        $sut->setCommentBoxName($commentsBoxName)->processCommentForm($input);
    }

    /**
     * Tests the not found action is called when data can't be loaded
     */
    public function testLoadNotFoundAction()
    {
        $sut = $this->getSutForIsolatedTest();
        $sut->setIsListResult(false);

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->withAnyArgs()->andReturn([]);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);

        $event = $this->routeMatchHelper->getMockRouteMatch(array('action' => 'not-found'));
        $sut->setEvent($event);

        $sut->setServiceLocator($mockServiceManager);

        $this->assertEquals(null, $sut->load(1));

    }

    /**
     * Tests the load function
     *
     * @dataProvider loadTestProvider
     *
     * @param $mockResult
     * @param $expectedResult
     * @param $isListResult
     * @throws \Exception
     */
    public function testLoad($mockResult, $expectedResult, $isListResult)
    {
        $sut = $this->getSutForIsolatedTest();
        $sut->setIsListResult($isListResult);

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->withAnyArgs()->andReturn($mockResult);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);

        $sut->setServiceLocator($mockServiceManager);
        $sut->expects($this->never())->method('notFoundAction');

        $this->assertEquals($expectedResult, $sut->load(1));
    }

    public function loadTestProvider()
    {
        return [
            [['Results' => [0 => ['id' => 1]], 'Count' => 1], ['id' => 1], true],
            [['Results' => [0 => ['id' => 1]], 'Count' => 1], ['Results' => [0 => ['id' => 1]], 'Count' => 1], false],
            [[], [], true],
        ];
    }

    /**
     * Tests process save when redirect is false
     */
    public function testProcessSaveNoRedirect()
    {
        $data = ['id' => 1];

        $sut = $this->getSutForIsolatedTest();

        $mockDataService = m::mock('Common\Service\Helper\DataHelperService');
        $mockDataService->shouldReceive('processDataMap')->andReturn([]);

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->withAnyArgs()->andReturn([]);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);
        $mockServiceManager->shouldReceive('get')->with('Helper\Data')->andReturn($mockDataService);

        $sut->setServiceLocator($mockServiceManager);

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(['FlashMessenger' => 'FlashMessenger']);

        $mockFlashMessenger = $mockPluginManager->get('FlashMessenger', '');
        $mockFlashMessenger->shouldReceive('addSuccessMessage');

        $this->assertEquals([], $sut->processSave($data, false));
    }

    /**
     * Tests processSave
     */
    public function testProcessSave()
    {
        $data = ['id' => 1];

        $sut = $this->getSutForIsolatedTest();

        $mockDataService = m::mock('Common\Service\Helper\DataHelperService');
        $mockDataService->shouldReceive('processDataMap')->andReturn([]);

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->withAnyArgs()->andReturn([]);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);
        $mockServiceManager->shouldReceive('get')->with('Helper\Data')->andReturn($mockDataService);

        $sut->setServiceLocator($mockServiceManager);

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'redirect' => 'Redirect',
                'FlashMessenger' => 'FlashMessenger'
            ]
        );

        $mockFlashMessenger = $mockPluginManager->get('FlashMessenger', '');
        $mockFlashMessenger->shouldReceive('addSuccessMessage');

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRoute')->with(
            null,
            ['action'=>'index', $sut->getIdentifierName() => null],
            ['code' => '303'],
            true
        )->andReturn('redirectResponse');

        $mockPluginManager->shouldReceive('get')->with('redirect')->andReturn($mockRedirect);

        $sut->setPluginManager($mockPluginManager);

        $this->assertEquals('redirectResponse', $sut->processSave($data));
    }

    /**
     * Tests details action and also view rendering
     */
    public function testDetailsAction()
    {
        $id = 1;

        $layout = 'layout/base';
        $headerTemplate = 'view-new/partials/header';
        $detailsTemplate = 'details/view';
        $scripts = ['scripts/script'];
        $pageLayoutInner = 'view-new/layouts/case-inner-layout';
        $pageTitle = 'Page title';
        $pageSubTitle = 'Page sub title';

        $sut = $this->getSutForIsolatedTest();
        $sut->setDetailsView($detailsTemplate);
        $sut->setInlineScripts($scripts);
        $sut->setPageLayoutInner($pageLayoutInner);
        $sut->setPageTitle($pageTitle);
        $sut->setPageSubTitle($pageSubTitle);

        $mockResult = ['Results' => [0 => ['id' => $id], 'Count' => 1]];
        $expectedLoadResult = ['id' => $id];

        $sut->setIsListResult(true);

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(['params' => 'Params']);

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with($sut->getIdentifierName())->andReturn($id);

        $scripts = m::mock('\Common\Service\Script\ScriptFactory');
        $scripts->shouldReceive('loadFiles')->with($sut->getInlineScripts());

        $sut->setPluginManager($mockPluginManager);

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->withAnyArgs()->andReturn($mockResult);

        $placeholder = new \Zend\View\Helper\Placeholder();

        $mockViewHelperManager = new \Zend\View\HelperPluginManager();
        $mockViewHelperManager->setService('placeholder', $placeholder);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);
        $mockServiceManager->shouldReceive('get')->with('viewHelperManager')->andReturn($mockViewHelperManager);
        $mockServiceManager->shouldReceive('get')->with('Script')->andReturn($scripts);

        $sut->setServiceLocator($mockServiceManager);

        $view = $sut->detailsAction();

        $viewChildren = $view->getChildren();
        $headerView = $viewChildren[0];
        $headerVariables = $headerView->getVariables();
        $layoutView = $viewChildren[1];
        $detailsView = $layoutView->getChildren();

        //check we have view models
        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $view);
        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $headerView);
        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $layoutView);
        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $detailsView[0]);

        //check scripts and titles set
        $this->assertArrayHasKey('scripts', $headerVariables);
        $this->assertEquals($headerVariables['pageTitle'], $pageTitle);
        $this->assertEquals($headerVariables['pageSubTitle'], $pageSubTitle);

        //check templates set
        $this->assertEquals($view->getTemplate(), $layout);
        $this->assertEquals($headerView->getTemplate(), $headerTemplate);
        $this->assertEquals($layoutView->getTemplate(), $pageLayoutInner);
        $this->assertEquals($detailsView[0]->getTemplate(), $detailsTemplate);

        $this->assertEquals(
            $expectedLoadResult,
            $mockViewHelperManager->get('placeholder')->getContainer($sut->getPlaceholderName())->getValue()
        );
        $this->assertEquals(
            $expectedLoadResult,
            $mockViewHelperManager->get('placeholder')->getContainer('details')->getValue()
        );
    }

    /**
     * Tests the details action with no inner layout
     */
    public function testDetailsActionNoInnerLayout()
    {
        $id = 1;

        $layout = 'layout/base';
        $headerTemplate = 'view-new/partials/header';
        $scripts = ['scripts/script'];
        $pageTitle = 'Page title';
        $pageLayoutInner = null;
        $pageSubTitle = 'Page sub title';

        $sut = $this->getSutForIsolatedTest();
        $sut->setInlineScripts($scripts);
        $sut->setPageLayoutInner($pageLayoutInner);
        $sut->setPageTitle($pageTitle);
        $sut->setPageSubTitle($pageSubTitle);

        $mockResult = ['Results' => [0 => ['id' => $id], 'Count' => 1]];
        $expectedLoadResult = ['id' => $id];

        $sut->setIsListResult(true);

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(['params' => 'Params']);

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with($sut->getIdentifierName())->andReturn($id);

        $sut->setPluginManager($mockPluginManager);

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->withAnyArgs()->andReturn($mockResult);

        $placeholder = new \Zend\View\Helper\Placeholder();

        $mockViewHelperManager = new \Zend\View\HelperPluginManager();
        $mockViewHelperManager->setService('placeholder', $placeholder);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);
        $mockServiceManager->shouldReceive('get')->with('viewHelperManager')->andReturn($mockViewHelperManager);

        $sut->setServiceLocator($mockServiceManager);

        $view = $sut->detailsAction();

        $viewChildren = $view->getChildren();
        $headerView = $viewChildren[0];
        $headerVariables = $headerView->getVariables();
        $layoutView = $viewChildren[1];

        //check we have view models
        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $view);
        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $headerView);
        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $layoutView);

        //check titles set
        $this->assertEquals($headerVariables['pageTitle'], $pageTitle);
        $this->assertEquals($headerVariables['pageSubTitle'], $pageSubTitle);

        //check templates set
        $this->assertEquals($view->getTemplate(), $layout);
        $this->assertEquals($headerView->getTemplate(), $headerTemplate);
        $this->assertEquals($layoutView->getTemplate(), $pageLayoutInner);

        $this->assertEquals(
            $expectedLoadResult,
            $mockViewHelperManager->get('placeholder')->getContainer($sut->getPlaceholderName())->getValue()
        );
        $this->assertEquals(
            $expectedLoadResult,
            $mockViewHelperManager->get('placeholder')->getContainer('details')->getValue()
        );
    }

    /**
     * Get a new SUT. Also tests that all the required abstracts
     * traits and interfaces are present.
     *
     * @param array $methods
     * @throws \Exception
     * @return \Olcs\Controller\CrudAbstract
     */
    public function getSutForIsolatedTest(array $methods = null)
    {
        $this->routeMatchHelper = new ControllerRouteMatchHelper();
        $this->pluginManagerHelper = new ControllerPluginManagerHelper();

        $sut = $this->getMock($this->testClass, $methods);

        if (false === ($sut instanceof \Common\Controller\AbstractSectionController)) {
            throw new \Exception('This system under test does not extend for the correct ultimate abstract');
        }

        if (false === ($sut instanceof \Common\Controller\CrudInterface)) {
            throw new \Exception('This system under test does not implement the correct interface');
        }

        if (count(array_diff($this->traitsRequired, self::classUsesDeep($sut))) > 0) {
            throw new \Exception('This system under test does not use the correct traits');
        }

        return $sut;
    }

    /**
     * Handy method for finding all implemented traits.
     *
     * @param unknown $class
     * @param string $autoload
     * @return multitype:
     */
    public static function classUsesDeep($class, $autoload = true)
    {
        $traits = [];

        // Get traits of all parent classes
        do {
            $traits = array_merge(class_uses($class, $autoload), $traits);
        } while ($class = get_parent_class($class));

        // Get traits of all parent traits
        $traitsToSearch = $traits;
        while (!empty($traitsToSearch)) {
            $newTraits = class_uses(array_pop($traitsToSearch), $autoload);
            $traits = array_merge($newTraits, $traits);
            $traitsToSearch = array_merge($newTraits, $traitsToSearch);
        };

        foreach ($traits as $trait => $same) {
            $traits = array_merge(class_uses($trait, $autoload), $traits);
        }

        return array_unique($traits);
    }

    /**
     * Tests Get DataService
     */
    public function testGetDataService()
    {
        $dataServiceName = 'foo';

        $mockDataService = m::mock('Olcs\Service\Data\\' . $dataServiceName);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Olcs\Service\Data\\' . $dataServiceName)
            ->andReturn($mockDataService);

        $sut = $this->getSutForIsolatedTest(['getService']);
        $sut->expects($this->once())->method('getService')->willReturn($dataServiceName);
        $sut->setServiceLocator($mockServiceManager);

        $this->assertEquals($mockDataService, $sut->getDataService());
        $this->assertEquals($mockDataService, $sut->getDataService()); // duplicated to test branch
    }

    /**
     * Tests Get DataService
     */
    public function testGetEntityDisplayName()
    {
        $entityDisplayName = 'foo';

        $sut = $this->getSutForIsolatedTest();
        $sut->setEntityDisplayName($entityDisplayName);
        $this->assertEquals($entityDisplayName, $sut->getEntityDisplayName());
    }


    /**
     * Tests Get DataService
     */
    public function testGetDataServiceName()
    {
        $dataServiceName = 'foo';

        $sut = $this->getSutForIsolatedTest();
        $result = $sut->setDataServiceName($dataServiceName);
        $this->assertSame($sut, $result);
        $this->assertEquals($dataServiceName, $sut->getDataServiceName());
    }
}
