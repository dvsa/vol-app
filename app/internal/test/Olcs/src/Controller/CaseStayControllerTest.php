<?php

/**
 * Case Stay Test Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Tests the case stay controller
 */
class CaseStayControllerTest extends AbstractHttpControllerTestCase
{

    protected $traceError = true;

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../../../config/application.config.php'
        );

        $this->controller = $this->getMock(
            '\Olcs\Controller\CaseStayController',
            [
                'makeRestCall',
                'fromRoute',
                'getCase',
                'generateFormWithData',
                'getCaseVariables',
                'notFoundAction',
                'redirect',
                'processAdd',
                'processEdit',
                'setBreadcrumb',
                'getServiceLocator',
                'caseHasAppeal',
                'caseHasStay'
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

    /**
     * Tests the indexAction
     *
     * @dataProvider indexActionProvider
     *
     * @param int $licenceId
     * @param int $caseId
     *
     */
    public function testIndexAction($licenceId, $caseId)
    {
        $this->getFromRoute(0, 'case', $caseId);
        $this->getFromRoute(1, 'licence', $licenceId);

        $this->controller->expects($this->once())
            ->method('setBreadcrumb');

        $this->controller->expects($this->exactly(2))
            ->method('makeRestCall')
            ->will($this->onConsecutiveCalls($this->getStayRestResult(1), $this->getAppealRestResult()));

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
        $this->getFromRoute(0, 'case', $caseId);

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
     * @param int $licenceId
     */
    public function testAddAction($caseId, $stayTypeId, $licenceId)
    {
        $this->getFromRoute(0, 'licence', $licenceId);
        $this->getFromRoute(1, 'case', $caseId);
        $this->getFromRoute(2, 'stayType', $stayTypeId);

        $this->controller->expects($this->once())
            ->method('caseHasAppeal')
            ->will($this->returnValue(true));

        $this->controller->expects($this->once())
            ->method('caseHasStay')
            ->will($this->returnValue(false));

        $this->controller->expects($this->once())
            ->method('getCase')
            ->with($this->equalTo($caseId))
            ->will($this->returnValue(array('data' => 'data')));

        $this->controller->expects($this->once())
            ->method('setBreadcrumb');

        $this->controller->expects($this->once())
            ->method('generateFormWithData');

        $this->controller->expects($this->once())
            ->method('getServiceLocator')
            ->will($this->returnValue($this->getScriptLoadFilesMock()));

        $this->controller->expects($this->never())
            ->method('notFoundAction');

        $this->controller->addAction();
    }

    /**
     * Tests the add action
     * @expectedException \Common\Exception\BadRequestException
     */
    public function testAddActionNoAppeal()
    {
        $this->controller->expects($this->once())
            ->method('caseHasAppeal')
            ->will($this->returnValue(false));

        $this->controller->addAction();
    }

    /**
     * Tests the add action fails when a record already exists
     *
     * @dataProvider addActionProvider
     *
     * @param int $caseId
     * @param int $stayTypeId
     * @param int $licenceId
     *
     * @expectedException \Common\Exception\BadRequestException
     */
    public function testAddActionFailExists($caseId, $stayTypeId, $licenceId)
    {
        $this->getFromRoute(0, 'licence', $licenceId);
        $this->getFromRoute(1, 'case', $caseId);
        $this->getFromRoute(2, 'stayType', $stayTypeId);

        $this->controller->expects($this->once())
            ->method('caseHasAppeal')
            ->will($this->returnValue(true));

        $this->controller->expects($this->once())
            ->method('caseHasStay')
            ->will($this->returnValue(true));

        $this->controller->addAction();
    }

    /**
     * Tests the add action fails when no case data is found
     */
    public function testAddActionNotFoundCase()
    {
        $this->getFromRoute(0, 'licence', false);
        $this->getFromRoute(1, 'case', false);
        $this->getFromRoute(2, 'stayType', false);

        $this->controller->expects($this->once())
            ->method('caseHasAppeal')
            ->will($this->returnValue(true));

        $this->controller->expects($this->once())
            ->method('getCase')
            ->will($this->returnValue(''));

        $this->controller->expects($this->once())
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
        $this->getFromRoute(0, 'licence', false);
        $this->getFromRoute(1, 'case', false);
        $this->getFromRoute(2, 'stayType', $stayTypeId);

        $this->controller->expects($this->once())
            ->method('caseHasAppeal')
            ->will($this->returnValue(true));

        $this->controller->expects($this->once())
            ->method('getCase')
            ->will($this->returnValue(array('data' => 'data')));

        $this->controller->expects($this->once())
            ->method('notFoundAction');

        $this->controller->addAction();
    }

    /**
     * Tests the edit action fails if the stay id is missing
     */
    public function testEditActionFailStay()
    {
        $this->getFromRoute(0, 'id', false);
        $this->getFromRoute(1, 'case', false);
        $this->getFromRoute(2, 'licence', false);
        $this->getFromRoute(3, 'stayType', false);

        $this->controller->expects($this->once())
            ->method('caseHasAppeal')
            ->will($this->returnValue(true));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue(''));

        $this->controller->expects($this->once())
            ->method('notFoundAction');

        $this->controller->editAction();
    }

    /**
     * Tests the edit action fails if the case id is missing
     */
    public function testEditActionFailCase()
    {
        $this->getFromRoute(0, 'id', false);
        $this->getFromRoute(1, 'case', false);
        $this->getFromRoute(2, 'licence', false);
        $this->getFromRoute(3, 'stayType', false);

        $this->controller->expects($this->once())
            ->method('caseHasAppeal')
            ->will($this->returnValue(true));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue(array('case' => array('id' => 24))));

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
        $this->getFromRoute(0, 'id', false);
        $this->getFromRoute(1, 'case', false);
        $this->getFromRoute(2, 'licence', false);
        $this->getFromRoute(3, 'stayType', $stayTypeId);

        $this->controller->expects($this->once())
            ->method('caseHasAppeal')
            ->will($this->returnValue(true));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue(array('case' => array('id' => 24))));

        $this->controller->expects($this->once())
            ->method('getCase')
            ->will($this->returnValue(array('data' => 'data')));

        $this->controller->expects($this->once())
            ->method('notFoundAction');

        $this->controller->editAction();
    }

    /**
     * Tests the edit action throws an exception if there is no appeal
     *
     * @expectedException \Common\Exception\BadRequestException
     */
    public function testEditActionNoAppeal()
    {
        $this->controller->expects($this->once())
            ->method('caseHasAppeal')
            ->will($this->returnValue(false));

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
     * @param int $licenceId
     */
    public function testEditAction($caseId, $stayTypeId, $stayId, $licenceId)
    {
        $this->getFromRoute(0, 'id', $stayId);
        $this->getFromRoute(1, 'case', $caseId);
        $this->getFromRoute(2, 'licence', $licenceId);
        $this->getFromRoute(3, 'stayType', $stayTypeId);

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue((array('case' => array('id' => $caseId)))));

        $this->controller->expects($this->once())
            ->method('caseHasAppeal')
            ->will($this->returnValue(true));

        $this->controller->expects($this->once())
            ->method('getCase')
            ->with($this->equalTo($caseId))
            ->will($this->returnValue(array('data' => 'data')));

        $this->controller->expects($this->once())
            ->method('generateFormWithData');

        $this->controller->expects($this->once())
            ->method('setBreadcrumb');

        $this->controller->expects($this->once())
            ->method('getServiceLocator')
            ->will($this->returnValue($this->getScriptLoadFilesMock()));

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
    public function testProcessAddStay($data)
    {
        $this->controller->expects($this->once())
            ->method('processAdd')
            ->will($this->returnValue(array('id' => 1)));

        $redirectInfo = $this->getRedirectSuccess($data['case'], $data['licence']);
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
            ->method('processAdd')
            ->will($this->returnValue(array()));

        $redirectInfo = $this->getRedirectAddFail($data['case'], $data['stayType'], $data['licence']);
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
     * @param array $data
     *
     */
    public function testProcessEditStay($data)
    {
        $this->controller->expects($this->at(0))
            ->method('processEdit')
            ->will($this->returnValue(''));

        $redirectInfo = $this->getRedirectSuccess($data['case'], $data['licence']);
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
     * @param array $data
     *
     */
    public function testProcessEditStayFail($data)
    {
        $this->controller->expects($this->at(0))
            ->method('processEdit')
            ->will($this->returnValue(array('data' => 'data')));

        $redirectInfo = $this->getRedirectEditFail($data['case'], $data['stay'], $data['stayType'], $data['licence']);
        $redirect = $this->getRedirectMock($redirectInfo);

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->processEditStay($data);
    }

    /**
     * Tests getDeleteServiceName
     */
    public function testGetDeleteServiceName()
    {
        $this->assertEquals('Stay', $this->controller->getDeleteServiceName());
    }

    /**
     * Data provider for testEditAction
     *
     * @return array
     */
    public function editActionProvider()
    {
        return array(
            array(24, 1, 1, 7),
            array(24, 1, 2, 7),
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
            array(24, 1, 7),
            array(24, 2, 7),
            array(28, 1, 7),
            array(28, 2, 7)
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
            array(7, 24),
            array(7, 24),
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

    public function getAppealRestResult()
    {
        return array(
            'Results' => array(
                0 => array(
                    'id' => 1,
                    'outcome' => 1,
                    'reason' => 1,
                    'deadlineDate' => '',
                    'appealDate' => '',
                    'hearingDate' => '',
                    'decisionDate' => '',
                    'papersDueDate' => '',
                    'papersSentDate' => ''
                )
            )
        );
    }

    /**
     * simulates a stay rest result array with results
     *
     * @param int $stayTypeId
     * @return array
     */
    public function getStayRestResult($stayTypeId)
    {
        return array(
            'Results' => array(
                0 => array(
                    'id' => 1,
                    'stayType' => $stayTypeId,
                    'outcome' => 'stay_status_granted',
                    'requestDate' => strtotime(time())
                )
            )
        );
    }

    /**
     * Data provider for testProcessAddStay
     *
     * @return array
     */
    public function processAddStayProvider()
    {
        return [
            [
                [
                    'case' => 1,
                    'stayType' => 1,
                    'licence' => 7,
                    'fields' => [
                        'isWithdrawn' => 'N',
                        'withdrawnDate' => '2014-01-01'
                    ]
                ],
                [
                    'case' => 1,
                    'stayType' => 2,
                    'licence' => 7,
                    'fields' => [
                        'isWithdrawn' => 'N',
                        'withdrawnDate' => null
                    ]
                ]
            ]
        ];
    }

    /**
     * Data provider for testProcessEditStay
     *
     * @return array
     */
    public function processEditStayProvider()
    {
        return [
            [
                [
                    'case' => 1,
                    'stay' => 1,
                    'stayType' => 1,
                    'licence' => 7,
                    'fields' => [
                        'isWithdrawn' => 'Y',
                        'withdrawnDate' => '2014-01-01'
                    ]
                ]
            ],
            [
                [
                    'case' => 1,
                    'stay' => 1,
                    'stayType' => 2,
                    'licence' => 7,
                    'fields' => [
                        'isWithdrawn' => 'N',
                        'withdrawnDate' => '2014-01-01'
                    ]
                ]
            ],
        ];
    }

    /**
     * Parameters for a successful redirect
     *
     * @param int $caseId
     * @param int $licenceId
     * @return array
     */
    private function getRedirectSuccess($caseId, $licenceId)
    {
        $redirectInfo['string'] = $this->getRedirectAction();
        $redirectInfo['options'] = array('action' => 'index', 'case' => $caseId, 'licence' => $licenceId);
        return $redirectInfo;
    }

    /**
     * Parameters for redirect if adding a record failed
     *
     * @param int $caseId
     * @param int $stayTypeId
     * @param int $licenceId
     * @return array
     */
    private function getRedirectAddFail($caseId, $stayTypeId, $licenceId)
    {
        $redirectInfo['string'] = $this->getRedirectAction();
        $redirectInfo['options'] = array(
            'action' => 'add',
            'case' => $caseId,
            'stayType' => $stayTypeId,
            'licence' => $licenceId
        );
        return $redirectInfo;
    }

    /**
     * Parameters for a redirect after a failure where a record already existed
     * This is an alias for getRedirectSuccess as they do the same thing
     *
     * @param int $caseId
     * @param int $licenceId
     * @return array
     */
    private function getRedirectExistsFail($caseId, $licenceId)
    {
        return $this->getRedirectSuccess($caseId, $licenceId);
    }

    /**
     * Paramaeters for a redirect after an edit has failed
     *
     * @param int $caseId
     * @param int $stayId
     * @param int $stayTypeId
     * @param int $licenceId
     *
     * @return array
     */
    private function getRedirectEditFail($caseId, $stayId, $stayTypeId, $licenceId)
    {
        $redirectInfo['string'] = $this->getRedirectAction();
        $redirectInfo['options'] = array(
            'action' => 'edit',
            'case' => $caseId,
            'stayType' => $stayTypeId,
            'stay' => $stayId,
            'licence' => $licenceId
        );
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

    /**
     * Generate a fromRoute function call
     *
     * @param int $at
     * @param mixed $with
     * @param mixed $will
     */
    private function getFromRoute($at, $with, $will = false)
    {
        if ($will) {
            $this->controller->expects($this->at($at))
                ->method('fromRoute')
                ->with($this->equalTo($with))
                ->will($this->returnValue($will));
        } else {
            $this->controller->expects($this->at($at))
                ->method('fromRoute')
                ->with($this->equalTo($with));
        }
    }

    private function getScriptLoadFilesMock ()
    {
        $scriptMock = $this->getMock('\stdClass', ['loadFiles']);
        $scriptMock->expects($this->any())
            ->method('loadFiles')
            ->will($this->returnValue([]));

        $serviceMock = $this->getMock('\stdClass', ['get']);
        $serviceMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue($scriptMock));

        return $serviceMock;
    }
}
