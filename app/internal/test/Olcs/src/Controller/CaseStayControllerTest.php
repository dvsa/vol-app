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

        parent::setUp();
    }

    /**
     * Reuses addActionProvider as requires a case ID
     *
     * @dataProvider caseIdProvider
     * @todo fix bugs before this can be used
     */
    public function IndexAction($caseId)
    {
        $response = $this->dispatch('/case/24/action/manage/stays', 'GET', array('action' => 'index', 'case' => $caseId));
        $this->assertResponseStatusCode(200);
        $this->assertControllerName('casestaycontroller');
        $this->assertControllerClass('CaseStayController');
        $this->assertMatchedRouteName('case_stay_action');
        $this->assertActionName('index');

        $restEnd = 'Stay';
        $restComm = 'GET';
        $restParam = array('vosa_case' => $caseId);

        $sut = $this->getMock(
            '\Olcs\Controller\CaseStayController', ['fromRoute', 'makeRestCall', 'getView', 'getCaseVariables']
        );

        $view = $this->getMock(
            'Zend\View\Model\ViewModel', ['setVariables', 'setTemplate']
        );

        $sut->expects($this->at(0))
            ->method('fromRoute')
            ->with($this->equalTo('case'))
            ->will($this->returnValue($this->caseProvider($caseId)));

        $sut->expects($this->at(1))
            ->method('makeRestCall')
            ->with($this->equalTo($restEnd), $this->equalTo($restComm), $this->equalTo($restParam))
            ->will($this->returnValue($this->caseProvider($caseId)));

        $sut->expects($this->at(2))
            ->method('getCaseVariables');

        $sut->expects($this->at(3))
            ->method('getView');

        $sut->indexAction();
    }

    /**
     * @dataProvider addActionProvider
     */
    public function testAddAction($caseId,$stayType)
    {
        $response = $this->dispatch('/case/24/action/manage/stays/add/1', 'GET', array('action' => 'add', 'case' => $caseId, 'staytype' => $stayType));
        $this->assertResponseStatusCode(200);
        $this->assertControllerName('casestaycontroller');
        $this->assertControllerClass('CaseStayController');
        $this->assertMatchedRouteName('case_stay_action');
        $this->assertActionName('add');
        $case = ['key' => 'case'];
        $viewTemplate = 'case/add-stay';

        $view = $this->getMock(
            'Zend\View\Model\ViewModel', ['setVariables', 'setTemplate']
        );

        $sut = $this->getMock('\Olcs\Controller\CaseStayController', ['fromRoute', 'getCase', 'generateFormWithData', 'setTemplate', 'notFoundAction']);

        $sut->expects($this->at(0))
            ->method('fromRoute')
            ->with($this->equalTo('case'))
            ->will($this->returnValue($caseId));

        $sut->expects($this->at(1))
            ->method('getCase')
            ->with($this->equalTo($caseId))
            ->will($this->returnValue($this->caseProvider($caseId)));

        if (empty($this->caseProvider($caseId))) {
            $sut->expects($this->at(2))
                ->method('notFoundAction');
        } else {
            $sut->expects($this->at(2))
                ->method('generateFormWithData');

            //$view->expects($this->at(0))
            //->method('setTemplate')
            //->with($this->equalTo($viewTemplate));

            $sut->expects($this->never())
                ->method('notFoundAction');
        }

        $sut->addAction();
    }

    /**
     * @dataProvider editActionProvider
     */
    public function testEditAction($caseId, $stayTypeId, $stayId)
    {
        $response = $this->dispatch('/case/24/action/manage/stays/edit/1/10', 'GET', array('action' => 'edit', 'case' => $caseId, 'staytype' => $stayTypeId, 'stay' => $stayId));
        $this->assertResponseStatusCode(200);
        $this->assertControllerName('casestaycontroller');
        $this->assertControllerClass('CaseStayController');
        $this->assertMatchedRouteName('case_stay_action');
        $this->assertActionName('edit');

        $restEnd = 'Stay';
        $restComm = 'GET';
        $restParam = array('id' => $stayId);
        $viewTemplate = 'case/add-stay';

        $sut = $this->getMock(
            '\Olcs\Controller\CaseStayController', ['fromRoute', 'makeRestCall', 'getCase', 'generateFormWithData', 'notFoundAction', 'setTemplate']
        );

        $view = $this->getMock(
            'Zend\View\Model\ViewModel', ['setVariables', 'setTemplate']
        );

        $sut->expects($this->at(0))
            ->method('fromRoute')
            ->with($this->equalTo('stay'))
            ->will($this->returnValue($stayId));

        $sut->expects($this->at(1))
            ->method('makeRestCall')
            ->with($this->equalTo($restEnd), $this->equalTo($restComm), $this->equalTo($restParam))
            ->will($this->returnValue($this->stayProvider($stayId)));

        if (empty($this->stayProvider($stayId))) {
            $sut->expects($this->at(2))
                ->method('notFoundAction');
        } else {
            $sut->expects($this->at(2))
                ->method('fromRoute')
                ->with($this->equalTo('case'))
                ->will($this->returnValue($caseId));

            $sut->expects($this->at(3))
                ->method('getCase')
                ->with($this->equalTo($caseId))
                ->will($this->returnValue($this->caseProvider($caseId)));

            if (empty($this->caseProvider($caseId))) {
                $sut->expects($this->at(4))
                    ->method('notFoundAction');
            } else {
                $sut->expects($this->at(4))
                    ->method('generateFormWithData');

                $sut->expects($this->never())
                    ->method('notFoundAction');

                //$view->expects($this->once())
                //->method('setTemplate')
                //->with($this->equalTo($viewTemplate));
            }
        }

        $sut->editAction();
    }

    /**
     * @dataProvider processAddStayProvider
     * @todo fix problem with testing redirect
     */
    public function testProcessAddStay($result, $data)
    {
        $data['fields'] = array();

        $sut = $this->getMock(
            '\Olcs\Controller\CaseStayController', ['redirect', 'processAdd']
        );

        $sut->expects($this->at(0))
            ->method('processAdd')
            ->will($this->returnValue($result));

        if ($this->getInsertSuccess($result)) {
            $redirectInfo = $this->getRedirectSuccess($data['case']);
        } else {
            $redirectInfo = $this->getRedirectAddFail($data['case'], $data['staytype']);
        }

        $redirect = $this->getRedirectMock($redirectInfo);

        $sut->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $sut->processAddStay($data);
    }

    /**
     * @dataProvider processEditStayProvider
     * @todo fix problem with testing redirect
     */
    public function testProcessEditStay($result, $data)
    {
        $data['fields'] = array();

        $sut = $this->getMock(
            '\Olcs\Controller\CaseStayController', ['redirect', 'processEdit']
        );

        $sut->expects($this->at(0))
            ->method('processEdit')
            ->will($this->returnValue($result));

        if ($this->getEditSuccess($result)) {
            $redirectInfo = $this->getRedirectSuccess($data['case']);
        } else {
            $redirectInfo = $this->getRedirectEditFail($data['case'], $data['stay'], $data['staytype']);
        }

        $redirect = $this->getRedirectMock($redirectInfo);

        $sut->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        return $sut->processEditStay($data);
    }

    /**
     * Returns a fake case object or null if input data is not int
     *
     * @param int $caseId
     * @return array|null
     */
    public function caseProvider($caseId)
    {
        if ((int) $caseId) {
            $case = ['key' => 'case'];
            return $case;
        }

        return null;
    }

    /**
     * Returns a fake stay object or null if input data is not int
     *
     * @param int $caseId
     * @return array|null
     */
    public function stayProvider($stayId)
    {
        if ((int) $stayId) {
            $stay = ['key' => 'stay'];
            return $stay;
        }

        return null;
    }

    /**
     * Data provider for testEditAction
     *
     * @return array
     */
    public function editActionProvider()
    {
        return array(
            array(24, 1, 10),
            array(24, 2, 10),
            array(0, 1, 0),
            array(0, 2, 0),
            array(24, 1, 0),
            array(24, 2, 0),
            array(0, 1, 10),
            array(0, 2, 10),
            array('', 1, ''),
            array('', 2, ''),
            array('a', 1, 'b'),
            array('a', 2, 'b'),
            array(false, 1, true),
            array(false, 2, true)
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
            array(24,1),
            array(24,2),
            array(0,1),
            array(0,2),
            array('',1),
            array('',2),
            array(false,1),
            array(false,2),
            array(true,1),
            array(true,2),
            array('a',1),
            array('a',2)
        );
    }

    /**
     * Data provider for testAddAction
     *
     * @return array
     */
    public function caseIdProvider()
    {
        return array(
            array(24),
            array(0),
            array(''),
            array(false),
            array(true),
            array('a')
        );
    }

    public function processAddStayProvider()
    {
        return array(
            array(array('id' => 1), array('case' => 1, 'staytype' => 1)),
            array(array('id' => 1), array('case' => 1, 'staytype' => 2)),
            array(array(), array('case' => 1, 'staytype' => 2)),
            array(array(), array('case' => 1, 'staytype' => 2))
        );
    }

    public function processEditStayProvider()
    {
        return array(
            array(array('failed' => true), array('case' => 1, 'stay' => 1, 'staytype' => 1)),
            array(array('failed' => true), array('case' => 1, 'stay' => 1, 'staytype' => 2)),
            array(array(), array('case' => 1, 'stay' => 1, 'staytype' => 1)),
            array(array(), array('case' => 1, 'stay' => 1, 'staytype' => 2))
        );
    }

    public function getRedirectSuccess($caseId)
    {
        $redirectInfo['string'] = $this->getRedirectAction();
        $redirectInfo['options'] = array('action' => 'index', 'case' => $caseId);
        return $redirectInfo;
    }

    public function getRedirectAddFail($caseId)
    {
        $redirectInfo['string'] = $this->getRedirectAction();
        $redirectInfo['options'] = array('action' => 'add', 'case' => $caseId, 'staytype' => $stayTypeId);
        return $redirectInfo;
    }

    public function getRedirectEditFail($caseId, $stayId, $stayTypeId)
    {
        $redirectInfo['string'] = $this->getRedirectAction();
        $redirectInfo['options'] = array('action' => 'edit', 'case' => $caseId, 'staytype' => $stayTypeId, 'stay' => $stayId);
        return $redirectInfo;
    }

    public function getRedirectAction()
    {
        return 'case_stay_action';
    }

    public function getRedirectMock($redirectInfo)
    {
        $redirect = $this->getMock('stdClass', ['toRoute']);
        $redirect->expects($this->once())
            ->method('toRoute')
            ->with($this->equalTo($redirectInfo['string']), $this->equalTo($redirectInfo['options']));

        return $redirect;
    }

    public function getInsertSuccess($result){
        if (isset($result['id'])) {
            return true;
        } else {
            return false;
        }
    }

    public function getEditSuccess($result){
        if (empty($result)) {
            return true;
        } else {
            return false;
        }
    }
}
