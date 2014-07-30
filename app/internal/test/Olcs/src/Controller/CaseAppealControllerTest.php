<?php

/**
 * Test CaseAppealController
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace OlcsTest\Controller;

/**
 * Test CaseAppealController
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CaseAppealControllerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test addAction
     */
    public function testAddAction()
    {
        $licenceId = 7;
        $caseId = 24;
        $form = '<form></form>';

        $viewMock = $this->getMock('\stdClass', array('setTemplate'));

        $viewMock->expects($this->once())
            ->method('setTemplate')
            ->with('form');

        $controller = $this->getMock(
            'Olcs\Controller\CaseAppealController',
            array('fromRoute', 'generateFormWithData', 'getView', 'setBreadcrumb', 'getServiceLocator')
        );

        $controller->expects($this->at(0))
            ->method('fromRoute')
            ->with('case')
            ->will($this->returnValue($caseId));

        $controller->expects($this->at(1))
            ->method('fromRoute')
            ->with('licence')
            ->will($this->returnValue($licenceId));

        $controller->expects($this->once())
            ->method('setBreadcrumb');

        $controller->expects($this->once())
            ->method('generateFormWithData')
            ->with('appeal', 'processAddAppeal', array('case' => $caseId))
            ->will($this->returnValue($form));

        $controller->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($viewMock));

        $controller->expects($this->once())
            ->method('getServiceLocator')
            ->will($this->returnValue($this->getScriptLoadFilesMock()));

        $this->assertEquals($viewMock, $controller->addAction());
    }

    /**
     * Test editAction with missing appeal
     */
    public function testEditActionWithMissingAppeal()
    {
        $controller = $this->getMock(
            'Olcs\Controller\CaseAppealController',
            array('fromRoute', 'makeRestCall', 'notFoundAction', 'setBreadcrumb')
        );

        $controller->expects($this->exactly(2))
            ->method('fromRoute');

        $controller->expects($this->once())
            ->method('makeRestCall')
            ->with('Appeal')
            ->will($this->returnValue(false));

        $controller->expects($this->once())
            ->method('notFoundAction')
            ->will($this->returnValue('404'));

        $this->assertEquals('404', $controller->editAction());
    }

    /**
     * Test editAction
     */
    public function testEditAction()
    {
        $appealId = 3;
        $caseId = 24;

        $appealDetails = array(
            'reason' => '5',
            'outcome' => '9',
            'case' => array(
                'id' => $caseId
            )
        );

        $form = '<form></form>';

        $expectedData = array(
            'reason' => '5',
            'outcome' => '9',
            'details' => array(
                'reason' => 'appeal_reason.5',
                'outcome' => 'appeal_outcome.9',
                'case' => $caseId
            ),
            'case' => $caseId
        );

        $viewMock = $this->getMock('\stdClass', array('setTemplate'));

        $viewMock->expects($this->once())
            ->method('setTemplate')
            ->with('form');

        $controller = $this->getMock(
            'Olcs\Controller\CaseAppealController',
            array('fromRoute', 'makeRestCall', 'generateFormWithData', 'getView', 'setBreadcrumb', 'getServiceLocator')
        );

        $controller->expects($this->at(0))
            ->method('fromRoute')
            ->will($this->returnValue($appealId));

        $controller->expects($this->at(1))
            ->method('fromRoute')
            ->with('licence');

        $controller->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue($appealDetails));

        $controller->expects($this->once())
            ->method('generateFormWithData')
            ->with('appeal', 'processEditAppeal', $expectedData)
            ->will($this->returnValue($form));

        $controller->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($viewMock));

        $controller->expects($this->once())
            ->method('getServiceLocator')
            ->will($this->returnValue($this->getScriptLoadFilesMock()));

        $this->assertEquals($viewMock, $controller->editAction());
    }

    /**
     * Test processAddAppeal
     */
    public function testProcessAddAppeal()
    {
        $data = array(
            'case' => 9,
            'details' => array(
                'reason' => 'appeal_reason.6',
                'outcome' => 'appeal_outcome.3',
                'isWithdrawn' => 'Y',
                'withdrawnDate' => '2014-01-01'
            )
        );

        $expectedProcessedData = array(
            'case' => 9,
            'reason' => 6,
            'outcome' => 3,
            'isWithdrawn' => 'Y',
            'withdrawnDate' => '2014-01-01'
        );

        $mockRedirect = $this->getMock('\stdClass', array('toRoute'));

        $mockRedirect->expects($this->once())
            ->method('toRoute')
            ->will($this->returnValue('redirect'));

        $controller = $this->getMock(
            'Olcs\Controller\CaseAppealController',
            array('makeRestCall', 'redirect', 'fromRoute')
        );

        $controller->expects($this->once())
            ->method('makeRestCall', $expectedProcessedData)
            ->with('Appeal', 'POST');

        $controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($mockRedirect));

         $controller->expects($this->once())
            ->method('fromRoute')
            ->will($this->returnValue(7));

        $controller->processAddAppeal($data);
    }

    /**
     * Test processEditAppeal
     */
    public function testProcessEditAppeal()
    {
        $data = array(
            'case' => 9,
            'details' => array(
                'reason' => 'appeal_reason.6',
                'outcome' => 'appeal_outcome.3',
                'isWithdrawn' => 'N',
                'withdrawnDate' => null
            )
        );

        $expectedProcessedData = array(
            'case' => 9,
            'reason' => 6,
            'outcome' => 3,
            'isWithdrawn' => 'N',
            'withdrawnDate' => null
        );

        $mockRedirect = $this->getMock('\stdClass', array('toRoute'));

        $mockRedirect->expects($this->once())
            ->method('toRoute')
            ->will($this->returnValue('redirect'));

        $controller = $this->getMock(
            'Olcs\Controller\CaseAppealController',
            array('makeRestCall', 'redirect', 'fromRoute')
        );

        $controller->expects($this->once())
            ->method('makeRestCall', $expectedProcessedData)
            ->with('Appeal', 'PUT');

        $controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($mockRedirect));

        $controller->expects($this->once())
            ->method('fromRoute')
            ->will($this->returnValue(7));

        $controller->processEditAppeal($data);
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
