<?php
namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Tests the Case Controller
 */
class CaseControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../config/application.config.php'
        );

        $this->controller = $this->getMock(
            '\Olcs\Controller\CaseController',
            [
                'getView',
                'fromRoute',
                'fromPost',
                'getTabInformationArray',
                'log',
                'makeRestCall',
                'getPluginManager',
                'setBreadcrumb',
                'getCase',
                'getSubmissions',
                'getServiceLocator',
                'generateFormWithData',
                'crudActionMissingId',
                'redirect',
                'notFoundAction',
                'params',
                'forward',
                'processAdd',
                'processEdit'
            ]
        );

        $this->view = $this->getMock(
            'Zend\View\Model\ViewModel',
            [
                'setVariables',
                'setTemplate'
            ]
        );

        $this->pm = $this->getMock('\stdClass', array('get'));

        parent::setUp();
    }

    public function testManageActionNoTab()
    {
        $caseId = '24';
        $licence = '7';
        $actionTab = 'overview';
        $tabInfo = ['convictions' => []];
        $params = array();

        $this->getFrom('Route', 0, 'case', $caseId);
        $this->getFrom('Route', 1, 'licence', $licence);
        $this->getFrom('Route', 2, 'tab', $actionTab);

        $this->controller->expects($this->once())
            ->method('setBreadcrumb');

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($this->getParams($params)));

        $this->controller->expects($this->once())
            ->method('getTabInformationArray')
            ->will($this->returnValue($tabInfo));

        $this->controller->expects($this->once())
            ->method('notFoundAction');

        $this->controller->manageAction();
    }

    public function testManageActionWithAction()
    {
        $caseId = '24';
        $licence = '7';
        $actionTab = 'overview';
        $tabInfo = ['convictions' => []];
        $params = array(
            'table' => 'submission',
            'action' => 'Add'
        );

        $this->getFrom('Route', 0, 'case', $caseId);
        $this->getFrom('Route', 1, 'licence', $licence);
        $this->getFrom('Route', 2, 'tab', $actionTab);

        $this->controller->expects($this->once())
            ->method('setBreadcrumb');

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($this->getParams($params)));

        $this->controller->expects($this->once())
            ->method('forward')
            ->will($this->returnValue($this->getForwardDispatch()));

        $this->controller->manageAction();
    }

    public function testManageAction()
    {
        $caseId = '24';
        $licence = '7';
        $actionTab = 'overview';
        $tabInfo = ['overview' => [], 'convictions' => []];
        $params = array();
        $caseObject = $this->getSampleCaseArray($caseId, $licence);

        $this->getFrom('Route', 0, 'case', $caseId);
        $this->getFrom('Route', 1, 'licence', $licence);
        $this->getFrom('Route', 2, 'tab', $actionTab);

        $this->controller->expects($this->once())
            ->method('setBreadcrumb');

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($this->getParams($params)));

        $this->controller->expects($this->once())
            ->method('getTabInformationArray')
            ->will($this->returnValue($tabInfo));

        $this->controller->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($this->view));

        $this->controller->expects($this->once())
            ->method('getCase')
            ->with($this->equalTo($caseId))
            ->will($this->returnValue($caseObject));

        $this->controller->expects($this->once())
            ->method('getSubmissions')
            ->with($this->equalTo($caseId))
            ->will($this->returnValue(array('data' => 'data')));

        $this->controller->expects($this->once())
            ->method('getPluginManager')
            ->will($this->returnValue($this->getPluginManagerUrl()));

        $this->controller->expects($this->once())
            ->method('getServiceLocator')
            ->will($this->returnValue($this->getServiceLocatorGetTable()));

        $this->view->expects($this->once())
            ->method('setVariables');
            //->with($this->equalTo(['case' => $caseObject, 'tabs' => $tabInfo, 'tab' => $actionTab, 'summary' => $summary, 'details' => $details]));

        $this->view->expects($this->once())
            ->method('setTemplate')
            ->with($this->equalTo('case/manage'));

        $this->assertSame($this->view, $this->controller->manageAction());
    }


    /**
     * Tests the index action
     */
    public function testIndexActionNoLicence()
    {
        $licenceId = null;

        $this->getFrom('Route', 0, 'licence', $licenceId);

        $this->controller->expects($this->once())
            ->method('notFoundAction');

        $this->controller->indexAction();
    }

    /**
     * Tests the index action
     */
    public function testIndexActionNoAction()
    {
        $licenceId = 7;
        $action = '';

        $this->beginIndexAction($licenceId, $action);
        $this->controller->expects($this->exactly(2))
            ->method('makeRestCall')
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue(
                        $this->getPageDataRestArray($licenceId)
                    ),
                    $this->returnValue(
                        array()
                    )
                )
            );

        $this->controller->expects($this->once())
            ->method('getPluginManager')
            ->will($this->returnValue($this->getPluginManagerUrl()));

        $this->controller->expects($this->once())
            ->method('getServiceLocator')
            ->will($this->returnValue($this->getServiceLocatorGetTable()));

        $this->controller->indexAction();
    }

    /**
     * Tests the index action
     */
    public function testIndexActionEditRedirect()
    {
        $licenceId = 7;
        $action = 'edit';
        $id = 24;

        $this->beginIndexAction($licenceId, $action);

        $this->getFrom('Post', 2, 'id', $id);

        $redirectInfo = $this->getAddWithIdRedirect($action, $id, $licenceId);
        $redirect = $this->getRedirectMock($redirectInfo);

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->indexAction();
    }

    /**
     * Tests the index action
     */
    public function testIndexActionEditNoId ()
    {
        $licenceId = 7;
        $action = 'edit';
        $id = null;

        $this->beginIndexAction($licenceId, $action);

        $this->getFrom('Post', 2, 'id', $id);

        $this->controller->expects($this->once())
            ->method('crudActionMissingId');

        $this->controller->indexAction();
    }

    /**
     * Tests the index action
     */
    public function testIndexActionAddNoId ()
    {
        $licenceId = 7;
        $action = 'add';
        $id = '';

        $this->beginIndexAction($licenceId, $action);

        $redirectInfo = $this->getAddNoIdRedirect($action, $licenceId);
        $redirect = $this->getRedirectMock($redirectInfo);

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->indexAction();
    }

    private function beginIndexAction($licenceId, $action)
    {
        $this->getFrom('Route', 0, 'licence', $licenceId);
        $this->getFrom('Post', 1, 'action', $action);
    }

    public function testDeleteAction ()
    {
        $licenceId = 7;
        $caseId = 24;

        $this->getFrom('Route', 0, 'licence', $licenceId);
        $this->getFrom('Route', 1, 'case', $caseId);

        $this->controller->expects($this->exactly(2))
            ->method('makeRestCall')
            ->will($this->returnValue(array('data' => 'data')));

        $redirectInfo = $this->getSuccessRedirect($licenceId);
        $redirect = $this->getRedirectMock($redirectInfo);

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->deleteAction();
    }

    public function testDeleteActionNotFound ()
    {
        $licenceId = 7;
        $caseId = 24;

        $this->getFrom('Route', 0, 'licence', $licenceId);
        $this->getFrom('Route', 1, 'case', $caseId);

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue(array()));

        $this->controller->expects($this->once())
            ->method('notFoundAction');

        $this->controller->deleteAction();
    }

    public function testEditAction()
    {
        $licenceId = 7;
        $caseId = 24;
        $caseObject = $this->getSampleCaseArray($caseId, $licenceId);

        $this->getFrom('Route', 0, 'licence', $licenceId);
        $this->getFrom('Route', 1, 'case', $caseId);

        $this->controller->expects($this->exactly(2))
            ->method('makeRestCall')
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue(
                        $caseObject
                    ),
                    $this->returnValue(
                        $this->getPageDataRestArray($licenceId)
                    )
                )
            );

        $this->controller->expects($this->once())
            ->method('getServiceLocator')
            ->will($this->returnValue($this->getServiceLocatorStaticData()));

        $this->controller->editAction();

    }

    /**
     * Tests the edit action when no result is found
     */
    public function testEditActionNotFound()
    {
        $licenceId = 7;
        $caseId = 24;

        $this->getFrom('Route', 0, 'licence', $licenceId);
        $this->getFrom('Route', 1, 'case', $caseId);

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue(array()));

        $this->controller->expects($this->once())
            ->method('notFoundAction');

        $this->controller->editAction();

    }

    /**
     * @dataProvider addEditCaseProvider
     *
     * @param array $data
     */
    public function testProcessAddCase ($data)
    {
        $redirectInfo = $this->getCaseAddRedirect(24, $data['licence']);
        $redirect = $this->getRedirectMock($redirectInfo);

        $this->getFrom('Route', 0, 'licence', $data['licence']);

        $this->controller->expects($this->once())
            ->method('processAdd')->
            will($this->returnValue(array('id' => 24)));

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->processAddCase($data);
    }

    /**
     * @dataProvider addEditCaseProvider
     *
     * @param array $data
     */
    public function testProcessEditCase ($data)
    {
        $redirectInfo = $this->getSuccessRedirect($data['licence']);
        $redirect = $this->getRedirectMock($redirectInfo);

        $this->controller->expects($this->once())
            ->method('processEdit');

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->processEditCase($data);
    }

    public function addEditCaseProvider()
    {
        return array(
            array(
                array('fields' => array(),'licence' => 7, 'categories' => $this->getSampleCategoriesArray())
            )
        );
    }


    public function testGetCaseVariables()
    {
        $caseId = 24;
        $licenceId = 7;
        $variables = array();
        $caseObject = $this->getSampleCaseArray($caseId, $licenceId);

        $this->controller->expects($this->once())
            ->method('getCase')
            ->with($caseId)
            ->will($this->returnValue($caseObject));

        $this->controller->getCaseVariables($caseId, $variables);
    }

    /**
     * Tests getTabInformationArray
     */
    public function testGetTabInformationArray()
    {
        $pluginMock = $this->getMock(
            'stdClass',
            [
                'get'
            ]
        );

        $routeMock = $this->getMock(
            'stdClass',
            [
                'fromRoute'
            ]
        );

        $routeMock->expects($this->any())
            ->method('fromRoute');

        $pluginMock->expects($this->any())
            ->method('get')
            ->with('url')
            ->will($this->returnValue($routeMock));

        $sut = $this->getMock(
            '\Olcs\Controller\CaseController',
            [
                'getPluginManager'
            ]
        );

        $sut->expects($this->once())
            ->method('getPluginManager')
            ->will($this->returnValue($pluginMock));

        $sut->getTabInformationArray();
    }

    /**
     * Tests the get case function
     *
     *
     */
    public function testGetCase()
    {
        $controller = $this->getMock(
            '\Olcs\Controller\CaseController',
            [
                'makeRestCall'
            ]
        );

        $controller->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue(array('data' => 'data')));

        $controller->getCase(24);
    }

    /**
     * Tests both the fromRoute and fromPost functions
     *
     * @dataProvider testGetFromProvider
     * @param string $function
     */
    public function testGetFrom($function)
    {
        $request = 'hello';
        $return = 'helloRet';

        $params = $this->getMock('stdClass', [$function]);
        $params->expects($this->once())
               ->method($function)
               ->with($this->equalTo($request))
               ->will($this->returnValue($return));

        $sut = $this->getMock('\Olcs\Controller\CaseController', ['params']);
        $sut->expects($this->once())
               ->method('params')
               ->will($this->returnValue($params));

        $this->assertSame($return, $sut->$function($request));
    }

    /**
     * Data provider for testGetFrom
     */
    public function testGetFromProvider()
    {
        return array(
            array('fromRoute'),
            array('fromPost')
        );
    }

    public function testGetView()
    {
        $sut = new \Olcs\Controller\CaseController();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $sut->getView());
    }

    /**
     * Creates a mock class (used for the redirect method)
     *
     * @param array $redirectInfo
     * @return type
     */
    private function getRedirectMock($redirectInfo)
    {
        $redirect = $this->getMock('stdClass', ['toRoute']);
        $redirect->expects($this->once())
            ->method('toRoute')
            ->with($this->equalTo($redirectInfo['string']), $this->equalTo($redirectInfo['options']));

        return $redirect;
    }

    private function getAddNoIdRedirect($action, $licenceId)
    {
        return array(
            'string' => 'licence_case_action',
            'options' => array(
                'action' => $action,
                'licence' => $licenceId,
            )
        );
    }

    private function getAddWithIdRedirect($action, $id, $licenceId)
    {
        return array(
            'string' => 'licence_case_action',
            'options' => array(
                'action' => $action,
                'case' => $id,
                'licence' => $licenceId,
            )
        );
    }

    private function getCaseAddRedirect($caseId, $licenceId)
    {
        return array(
            'string' => 'case_manage',
            'options' => array(
                'licence' => $licenceId,
                'case' => $caseId,
                'tab' => 'overview',
            )
        );
    }

    private function getSuccessRedirect($licenceId)
    {
        return array(
            'string' => 'licence_case_list',
            'options' => array(
                'licence' => $licenceId,
            )
        );
    }

    /**
     * Shortcut for the getRoute and getPost methods
     *
     * @param string $function
     * @param int $at
     * @param mixed $with
     * @param mixed $will
     */
    private function getFrom($function, $at, $with, $will = false)
    {
        $function = ucwords($function);

        if ($will) {
            $this->controller->expects($this->at($at))
                ->method('from' . $function)
                ->with($this->equalTo($with))
                ->will($this->returnValue($will));
        } else {
            $this->controller->expects($this->at($at))
                ->method('from' . $function)
                ->with($this->equalTo($with));
        }
    }

    /**
     * Gets a mock plugin manager url call
     */
    public function getPluginManagerUrl ()
    {
        $pm = $this->getMock('\stdClass', array('get'));

        $pm->expects($this->once())
            ->method('get')
            ->with($this->equalTo('url'))
            ->will($this->returnValue('the url'));

        return $pm;
    }

    /**
     * Gets a mock plugin manager url call
     */
    public function getServiceLocatorStaticData ()
    {
        $serviceMock = $this->getMock('\stdClass', array('get'));

        $serviceMock->expects($this->once())
            ->method('get')
            ->with($this->equalTo('Config'))
            ->will($this->returnValue(array('static-list-data' => $this->getSampleCategoriesArray())));

        return $serviceMock;
    }

    /**
     * Gets a mock call to get parameters
     */
    public function getParams ($returnValue)
    {
        $paramsMock = $this->getMock('\stdClass', array('fromPost'));

        $paramsMock->expects($this->once())
            ->method('fromPost')
            ->will($this->returnValue($returnValue));

        return $paramsMock;
    }

    /**
     * Gets a call to forwward->dispatch()
     */
    public function getForwardDispatch ()
    {
        $paramsMock = $this->getMock('\stdClass', array('dispatch'));

        $paramsMock->expects($this->once())
            ->method('dispatch');

        return $paramsMock;
    }

    /**
     * Gets a mock plugin manager url call
     */
    public function getServiceLocatorGetTable ()
    {
        $serviceMock = $this->getMock(
            'stdClass',
            [
                'get'
            ]
        );

        $tableMock = $this->getMock(
            'stdClass',
            [
                'buildTable'
            ]
        );

        $tableMock->expects($this->any())
            ->method('buildTable')
            ->will($this->returnValue('table'));

        $serviceMock->expects($this->any())
            ->method('get')
            ->with('Table')
            ->will($this->returnValue($tableMock));

        return $serviceMock;
    }

    /**
     * Returns a sample array as would be expected from the getPageData function
     *
     * @param  int $licenceId
     * @return array
     */
    private function getPageDataRestArray($licenceId)
    {
        return array(
            'organisation' => array(
                'name' => 'Orgnaisation name',
            ),
            'licenceNumber' => $licenceId
        );
    }

    /**
     * Returns a sample categories array
     *
     * @return array
     */
    private function getSampleCategoriesArray()
    {
        return array(
            'case_categories_compliance' => array(
                'case_category.1' => 'Offences (inc. driver hours)',
                'case_category.2' => 'Prohibitions',
                'case_category.3' => 'Convictions',
                'case_category.4' => 'Penalties',
                'case_category.5' => 'ERRU MSI',
                'case_category.6' => 'Bus compliance',
                'case_category.7' => 'Section 9',
                'case_category.8' => 'Section 43',
                'case_category.9' => 'Impounding'
            ),
            'case_categories_bus' => array(
            ),
            'case_categories_tm' => array(
                'case_category.10' => 'Duplicate TM',
                'case_category.11' => 'Repute / professional comptenece of TM',
                'case_category.12' => 'TM Hours'
            ),
            'case_categories_app' => array(
                'case_category.13' => 'Interim with / without submission',
                'case_category.14' => 'Representation',
                'case_category.15' => 'Objection',
                'case_category.16' => 'Non-chargeable variation',
                'case_category.17' => 'Regulation 31',
                'case_category.18' => 'Schedule 4',
                'case_category.19' => 'Chargeable variation',
                'case_category.20' => 'New application'
            ),
            'case_categories_referral' => array(
                'case_category.21' => 'Surrender',
                'case_category.22' => 'Non application related maintenance issue',
                'case_category.23' => 'Review complaint',
                'case_category.24' => 'Late fee',
                'case_category.25' => 'Financial standing issue (continuation)',
                'case_category.26' => 'Repute fitness of director',
                'case_category.27' => 'Period of grace',
                'case_category.28' => 'Proposal to revoke'
            ),
        );

    }

    /**
     * Provides a sample case object
     */
    public function getSampleCaseArray($caseId, $licenceId)
    {
        return array
        (
        'createdOn' => '',
        'lastUpdatedOn' => '',
        'version' => 1,
        'id' => $caseId,
        'caseNumber' => 12345678,
        'status' => 'Open',
        'description' => 'Convictions against operator',
        'convictionData' => '',
        'ecms' => 'E123444',
        'openTime' => '2012-06-13T00:00:00+0100',
        'owner' => 'TBC',
        'caseType' => 'Compliance',
        'closedTime' => '',
        'createdBy' => '',
        'lastUpdatedBy' => '',
        'categories' => array
            (
                array(
                    'id' => 1,
                    'name' => 'Category name'
                )
            ),

        'licence' => array
            (
            'createdOn' => '2014-04-02T13:39:54+0100',
            'lastUpdatedOn' => '2014-04-02T13:39:54+0100',
            'version' => 1,
            'id' => 7,
            'goodsOrPsv' => 'Goods',
            'licenceNumber' => 'OB1234567',
            'licenceStatus' => 'Valid',
            'niFlag' => '',
            'licenceType' => 'Standard National',
            'startDate' => '2010-01-12T00:00:00+0000',
            'reviewDate' => '2010-01-12T00:00:00+0000',
            'endDate' => '2010-01-12T00:00:00+0000',
            'fabsReference' => '',
            'tradeType' => 'Utilities',
            'authorisedTrailers' => '',
            'authorisedVehicles' => '',
            'safetyInsVehicles' => '',
            'safetyInsTrailers' => '',
            'safetyInsVaries' => '',
            'tachographIns' => '',
            'tachographInsName' => '',
            'createdBy' => array
                (

                ),

            'lastUpdatedBy' => array
                (

                ),

            'organisation' => Array
                (
                    'createdOn' => '2014-04-02T13:39:54+0100',
                    'lastUpdatedOn' => '2014-04-02T13:39:54+0100',
                    'version' => 2,
                    'id' => $licenceId,
                    'registeredCompanyNumber' => 1234567,
                    'name' => 'test',
                    'tradingAs' => '',
                    'organisationType' => 'Registered company',
                    'sicCode' => '',
                    'createdBy' => array
                        (
                        ),

                    'lastUpdatedBy' => array
                        (
                        ),

                ),

            'operatingCentres' => array
                (
                ),

            'trafficArea' => array
                (
                    'createdOn' => '',
                    'lastUpdatedOn' => '',
                    'version' => 1,
                    'id' => 1,
                    'areaName' => 'North East of England',
                    'createdBy' => '',
                    'lastUpdatedBy' => ''
                ),

            'cases' => array
                (
                ),

            'transportManagers' => array
                (
                ),

            'fees' => array
                (
                ),

            'conditions' => array
                (
                    0 => array
                        (
                        )

                ),

            'tradingNames' => array
                (
                ),

            'contactDetails' => array
                (
                ),

        ),

        'convictions' => array
            (
            ),

        'complaints' => array
            (
            ),

        'penalties' => array
            (
            ),

        'prohibitions' => array
            (
            )

        );
    }
}
