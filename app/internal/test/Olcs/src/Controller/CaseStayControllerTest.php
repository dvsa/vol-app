<?php

/**
 * Case Stay Test Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class CaseStayControllerTest extends AbstractHttpControllerTestCase
{

    protected $traceError = true;

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../../../config/application.config.php'
        );

        $this->controller = $this->getMock(
            '\Olcs\Controller\CaseStayController', [
            'makeRestCall',
            'fromRoute',
            'getCase',
            'generateFormWithData',
            'getCaseVariables',
            'notFoundAction',
            'redirect',
            'processAdd',
            'processEdit'
            ]
        );

        $this->view = $this->getMock(
            'Zend\View\Model\ViewModel', [
            'setVariables',
            'setTemplate'
            ]
        );

        parent::setUp();
    }

    /**
     * Tests the indexAction
     *
     * @dataProvider indexActionProvider
     *
     * @param int $caseId
     * @param array $searchResults
     *
     */
    public function testIndexAction($caseId, $searchResults)
    {
        $restEnd = 'Stay';
        $restComm = 'GET';
        $restParam = array('case' => $caseId);

        $this->controller->expects($this->once())
            ->method('fromRoute')
            ->with($this->equalTo('case'))
            ->will($this->returnValue($caseId));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with($this->equalTo($restEnd), $this->equalTo($restComm), $this->equalTo($restParam))
            ->will($this->returnValue($searchResults));

        $this->controller->expects($this->once())
            ->method('getCaseVariables');

        $this->controller->indexAction();
    }

    /**
     * Tests indexAction if a none numeric case id is posted
     *
     * @dataProvider badIdProvider
     *
     * @param int $caseId
     */
    public function testIndexActionNotFound($caseId)
    {
        $this->controller->expects($this->once())
            ->method('fromRoute')
            ->with($this->equalTo('case'))
            ->will($this->returnValue($caseId));

        $this->controller->expects($this->once())
            ->method('notFoundAction');

        $this->controller->indexAction();
    }

    /**
     * Tests the add action
     *
     * @dataProvider addActionProvider
     *
     * @param int $caseId
     * @param int $stayTypeId
     */
    public function testAddAction($caseId, $stayTypeId)
    {
        $viewTemplate = 'case/add-stay';

        $this->controller->expects($this->at(0))
            ->method('fromRoute')
            ->with($this->equalTo('case'))
            ->will($this->returnValue($caseId));

        $this->controller->expects($this->at(1))
            ->method('getCase')
            ->with($this->equalTo($caseId))
            ->will($this->returnValue(array('data' => 'data')));

        $this->controller->expects($this->at(2))
            ->method('fromRoute')
            ->with($this->equalTo('stayType'))
            ->will($this->returnValue($stayTypeId));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with('Stay', 'GET', $this->equalTo(array('case' => $caseId)))
            ->will($this->returnValue($this->getStayRestResult(false, $stayTypeId)));

        $this->controller->expects($this->once())
            ->method('generateFormWithData');

        $this->controller->expects($this->never())
            ->method('notFoundAction');

        $this->controller->addAction();
    }

    /**
     * Tests the add action fails when a record already exists
     *
     * @dataProvider addActionProvider
     *
     * @param int $caseId
     * @param int $stayTypeId
     *
     */
    public function testAddActionFailExists($caseId, $stayTypeId)
    {
        $redirectInfo = $this->getRedirectSuccess($caseId);
        $redirect = $this->getRedirectMock($redirectInfo);

        $this->controller->expects($this->at(0))
            ->method('fromRoute')
            ->with($this->equalTo('case'))
            ->will($this->returnValue($caseId));

        $this->controller->expects($this->at(1))
            ->method('getCase')
            ->with($this->equalTo($caseId))
            ->will($this->returnValue(array('data' => 'data')));

        $this->controller->expects($this->at(2))
            ->method('fromRoute')
            ->with($this->equalTo('stayType'))
            ->will($this->returnValue($stayTypeId));

        $this->controller->expects($this->at(3))
            ->method('makeRestCall')
            ->with('Stay', 'GET', $this->equalTo(array('case' => $caseId)))
            ->will($this->returnValue($this->getStayRestResult(true, $stayTypeId)));

        $this->controller->expects($this->at(4))
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->addAction();
    }

    /**
     * Tests the add action fails when no case data is found
     */
    public function testAddActionNotFoundCase()
    {
        $this->controller->expects($this->at(0))
            ->method('fromRoute')
            ->with($this->equalTo('case'));

        $this->controller->expects($this->at(1))
            ->method('getCase')
            ->will($this->returnValue(''));

        $this->controller->expects($this->at(2))
            ->method('notFoundAction');

        $this->controller->addAction();
    }

    /**
     * Tests the add action fails when no stay type data is found
     *
     * @dataProvider badIdProvider
     */
    public function testAddActionNotFoundStayType($stayTypeId)
    {
        $this->controller->expects($this->at(0))
            ->method('fromRoute')
            ->with($this->equalTo('case'));

        $this->controller->expects($this->at(1))
            ->method('getCase')
            ->will($this->returnValue(array('data' => 'data')));

        $this->controller->expects($this->at(2))
            ->method('fromRoute')
            ->with($this->equalTo('stayType'))
            ->will($this->returnValue($stayTypeId));

        $this->controller->expects($this->once())
            ->method('notFoundAction');

        $this->controller->addAction();
    }

    public function testEditActionFailStay()
    {
        $this->controller->expects($this->at(0))
            ->method('fromRoute')
            ->with($this->equalTo('stay'));

        $this->controller->expects($this->at(1))
            ->method('makeRestCall')
            ->will($this->returnValue(''));

        $this->controller->expects($this->at(2))
            ->method('notFoundAction');

        $this->controller->editAction();
    }

    public function testEditActionFailCase()
    {
        $this->controller->expects($this->at(0))
            ->method('fromRoute')
            ->with($this->equalTo('stay'));

        $this->controller->expects($this->at(1))
            ->method('makeRestCall')
            ->will($this->returnValue(array('data' => 'data')));

        $this->controller->expects($this->at(2))
            ->method('fromRoute')
            ->with($this->equalTo('case'));

        $this->controller->expects($this->once())
            ->method('getCase')
            ->will($this->returnValue(''));

        $this->controller->editAction();
    }

    /**
     * Tests the edit action fails when no stay type data is found
     *
     * @dataProvider badIdProvider
     */
    public function testEditActionFailStayType($stayTypeId)
    {
        $this->controller->expects($this->at(0))
            ->method('fromRoute')
            ->with($this->equalTo('stay'));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue(array('data' => 'data')));

        $this->controller->expects($this->at(2))
            ->method('fromRoute')
            ->with($this->equalTo('case'));

        $this->controller->expects($this->once())
            ->method('getCase')
            ->will($this->returnValue(array('data' => 'data')));

        $this->controller->expects($this->at(4))
            ->method('fromRoute')
            ->with($this->equalTo('stayType'))
            ->will($this->returnValue($stayTypeId));

        $this->controller->expects($this->once())
            ->method('notFoundAction');

        $this->controller->editAction();
    }

    /**
     * Tests the edit action
     *
     * @dataProvider editActionProvider
     *
     * @param int $caseId
     * @param int $stayTypeId
     * @param int $stayId
     */
    public function testEditAction($caseId, $stayTypeId, $stayId)
    {
        $restEnd = 'Stay';
        $restComm = 'GET';
        $restParam = array('id' => $stayId);
        $viewTemplate = 'case/add-stay';

        $this->controller->expects($this->at(0))
            ->method('fromRoute')
            ->with($this->equalTo('stay'))
            ->will($this->returnValue($stayId));

        $this->controller->expects($this->at(1))
            ->method('makeRestCall')
            ->with($this->equalTo($restEnd), $this->equalTo($restComm), $this->equalTo($restParam))
            ->will($this->returnValue(array('data' => 'data')));

        $this->controller->expects($this->at(2))
            ->method('fromRoute')
            ->with($this->equalTo('case'))
            ->will($this->returnValue($caseId));

        $this->controller->expects($this->at(3))
            ->method('getCase')
            ->with($this->equalTo($caseId))
            ->will($this->returnValue(array('data' => 'data')));

        $this->controller->expects($this->at(4))
            ->method('fromRoute')
            ->with($this->equalTo('stayType'))
            ->will($this->returnValue($stayTypeId));

        $this->controller->expects($this->once())
            ->method('generateFormWithData');

        $this->controller->editAction();
    }

    /**
     * Tests processAddStay
     *
     * @dataProvider processAddStayProvider
     *
     * @param array $data
     *
     */
    public function testProcessAddStayFailExists($data)
    {
        $redirectInfo = $this->getRedirectExistsFail($data['case']);
        $redirect = $this->getRedirectMock($redirectInfo);

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with('Stay', 'GET', $this->equalTo(array('case' => $data['case'])))
            ->will($this->returnValue($this->getStayRestResult(true, $data['stayType'])));

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->processAddStay($data);
    }

    /**
     * Tests processAddStay
     *
     * @dataProvider processAddStayProvider
     *
     * @param array $data
     *
     */
    public function testProcessAddStay($data)
    {
        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with('Stay', 'GET', $this->equalTo(array('case' => $data['case'])))
            ->will($this->returnValue($this->getStayRestResult(false, $data['stayType'])));

        $this->controller->expects($this->at(1))
            ->method('processAdd')
            ->will($this->returnValue(array('id' => 1)));

        $redirectInfo = $this->getRedirectSuccess($data['case']);
        $redirect = $this->getRedirectMock($redirectInfo);

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->processAddStay($data);
    }

    /**
     * Tests processAddStay
     *
     * @dataProvider processAddStayProvider
     *
     * @param array $data
     *
     */
    public function testProcessAddStayFail($data)
    {
        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with('Stay', 'GET', $this->equalTo(array('case' => $data['case'])))
            ->will($this->returnValue($this->getStayRestResult(false, $data['stayType'])));

        $this->controller->expects($this->at(1))
            ->method('processAdd')
            ->will($this->returnValue(array()));

        $redirectInfo = $this->getRedirectAddFail($data['case'], $data['stayType']);
        $redirect = $this->getRedirectMock($redirectInfo);

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->processAddStay($data);
    }

    /**
     * Tests processEditStay
     *
     * @dataProvider processEditStayProvider
     *
     * @param array $result
     * @param array $data
     *
     */
    public function testProcessEditStay($data)
    {
        $this->controller->expects($this->at(0))
            ->method('processEdit')
            ->will($this->returnValue(''));

        $redirectInfo = $this->getRedirectSuccess($data['case']);
        $redirect = $this->getRedirectMock($redirectInfo);

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->processEditStay($data);
    }

    /**
     * Tests for failure of processEditStay
     *
     * @dataProvider processEditStayProvider
     *
     * @param array $result
     * @param array $data
     *
     */
    public function testProcessEditStayFail($data)
    {
        $this->controller->expects($this->at(0))
            ->method('processEdit')
            ->will($this->returnValue(array('data' => 'data')));

        $redirectInfo = $this->getRedirectEditFail($data['case'], $data['stay'], $data['stayType']);
        $redirect = $this->getRedirectMock($redirectInfo);

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->processEditStay($data);
    }

    /**
     * Data provider for testEditAction
     *
     * @return array
     */
    public function editActionProvider()
    {
        return array(
            array(24, 1, 1),
            array(24, 1, 2),
        );
    }

    /**
     * Data provider for testAddAction
     *
     * @return array
     */
    public function addActionProvider()
    {
        return array(
            array(24, 1),
            array(24, 2),
            array(28, 1),
            array(28, 2)
        );
    }

    /**
     * Data provider for index action
     *
     * @return array
     */
    public function indexActionProvider()
    {
        return array(
            array(24, $this->getStayRestResult(false, 1)),
            array(24, $this->getStayRestResult(true, 1)),
        );
    }

    /**
     * Returns a list of Ids which should fail validation
     *
     * @return array
     */
    public function badIdProvider()
    {
        return array(
            array(0),
            array(''),
            array('aaa')
        );
    }

    /**
     * simulates a stay rest result array
     *
     * @param bool $results
     * @return array
     */
    public function getStayRestResult($results = true, $stayTypeId)
    {
        if ($results) {
            return array('Results' => array(0 => array('stayType' => $stayTypeId)));
        }

        return array('Results' => array());
    }

    /**
     * Data provider for testProcessAddStay
     *
     * @return array
     */
    public function processAddStayProvider()
    {
        return array(
            array(
                array('case' => 1, 'stayType' => 1, 'fields' => array()),
                array('case' => 1, 'stayType' => 1, 'fields' => array())
            ),
        );
    }

    /**
     * Data provider for testProcessEditStay
     *
     * @return array
     */
    public function processEditStayProvider()
    {
        return array(
            array(array('case' => 1, 'stay' => 1, 'stayType' => 1, 'fields' => array())),
            array(array('case' => 1, 'stay' => 1, 'stayType' => 2, 'fields' => array())),
        );
    }

    /**
     * Parameters for a successful redirect
     *
     * @param int $caseId
     * @return array
     */
    private function getRedirectSuccess($caseId)
    {
        $redirectInfo['string'] = $this->getRedirectAction();
        $redirectInfo['options'] = array('action' => 'index', 'case' => $caseId);
        return $redirectInfo;
    }

    /**
     * Parameters for redirect if adding a record failed
     *
     * @param int $caseId
     * @param int $stayTypeId
     * @return array
     */
    private function getRedirectAddFail($caseId, $stayTypeId)
    {
        $redirectInfo['string'] = $this->getRedirectAction();
        $redirectInfo['options'] = array('action' => 'add', 'case' => $caseId, 'stayType' => $stayTypeId);
        return $redirectInfo;
    }

    /**
     * Paramaeters for a redirect after a failure where a record already existed
     *
     * @param int $caseId
     * @return array
     */
    private function getRedirectExistsFail($caseId)
    {
        return $this->getRedirectSuccess($caseId);
    }

    /**
     * Paramaeters for a redirect after an edit has failed
     *
     * @param int $caseId
     * @param int $stayId
     * @param int $stayTypeId
     *
     * @return array
     */
    private function getRedirectEditFail($caseId, $stayId, $stayTypeId)
    {
        $redirectInfo['string'] = $this->getRedirectAction();
        $redirectInfo['options'] = array('action' => 'edit', 'case' => $caseId, 'stayType' => $stayTypeId, 'stay' => $stayId);
        return $redirectInfo;
    }

    /**
     * Returns the routing action for the stays page
     *
     * @return string
     */
    private function getRedirectAction()
    {
        return 'case_stay_action';
    }

    /**
     * Creates a mock class (used for the redirect method)
     *
     * @param type $redirectInfo
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
}
