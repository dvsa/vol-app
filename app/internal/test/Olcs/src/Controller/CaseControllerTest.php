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
                'crudActionMissingId',
                'redirect',
                'notFoundAction'
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

    public function testManageAction()
    {
        $caseId = '24';
        $licence = '7';
        $actionTab = 'overview';
        $tabInfo = ['overview' => [], 'convictions' => []];
        $summary = ['key' => 'summary'];
        $details = ['key' => 'details'];
        $caseObject = $this->getSampleCaseArray($caseId, $licence);

        $this->getFrom('Route', 0, 'case', $caseId);
        $this->getFrom('Route', 1, 'licence', $licence);
        $this->getFrom('Route', 2, 'tab', $actionTab);

        $this->controller->expects($this->once())
            ->method('setBreadcrumb');

        $this->controller->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($this->view));

        $this->controller->expects($this->once())
            ->method('getTabInformationArray')
            ->will($this->returnValue($tabInfo));

        $this->controller->expects($this->once())
            ->method('getCase')
            ->with($this->equalTo($caseId))
            ->will($this->returnValue($caseObject));
/*
        $this->controller->expects($this->once())
            ->method('getCaseSummaryArray')
            ->with($this->equalTo($caseObject))
            ->will($this->returnValue($summary));

        $this->controller->expects($this->once())
            ->method('getCaseDetailsArray')
            ->with($this->equalTo($caseObject))
            ->will($this->returnValue($details));
*/
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

    public function getPageDataRestArray($licenceId)
    {
        return array(
            'organisation' => array(
                'name' => 'Orgnaisation name',
            ),
            'licenceNumber' => $licenceId
        );
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

    public function getAddNoIdRedirect($action, $licenceId)
    {
        return array(
            'string' => 'licence_case_action',
            'options' => array(
                'action' => $action,
                'licence' => $licenceId,
            )
        );
    }

    public function getAddWithIdRedirect($action, $id, $licenceId)
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
                    'name' => 'Category Name'
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
