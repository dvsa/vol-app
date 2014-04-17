<?php

/**
 * Test CaseStatementController
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Test CaseStatementController
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CaseStatementControllerTest extends AbstractHttpControllerTestCase
{
    public function testIndexAction()
    {
        $controller = $this->getMock(
            'Olcs\Controller\CaseStatementController',
            array(
                'fromRoute',
                'makeRestCall',
                'buildTable',
                'getView',
                'getCaseVariables'
            )
        );

        $caseId = 3;

        $restResults = array();

        $variables = array(
            'foo' => 'bar'
        );

        $viewMock = $this->getMock('\stdClass', array('setTemplate'));

        $controller->expects($this->once())
            ->method('fromRoute')
            ->with('case')
            ->will($this->returnValue($caseId));

        $controller->expects($this->once())
            ->method('makeRestCall')
            ->with('Statement')
            ->will($this->returnValue($restResults));

        $controller->expects($this->once())
            ->method('buildTable')
            ->with('statement')
            ->will($this->returnValue('<table></table>'));

        $controller->expects($this->once())
            ->method('getCaseVariables')
            ->with($caseId)
            ->will($this->returnValue($variables));

        $controller->expects($this->once())
            ->method('getView')
            ->with($variables)
            ->will($this->returnValue($viewMock));

        $this->assertEquals($viewMock, $controller->indexAction());
    }

    /**
     * Test addAction
     */
    public function testAddAction()
    {
        $caseId = 7;
        $form = '<form></form>';

        $viewMock = $this->getMock('\stdClass', array('setTemplate'));

        $viewMock->expects($this->once())
            ->method('setTemplate')
            ->with('form');

        $controller = $this->getMock(
            'Olcs\Controller\CaseStatementController',
            array('fromRoute', 'generateFormWithData', 'getView')
        );

        $controller->expects($this->once())
            ->method('fromRoute')
            ->with('case')
            ->will($this->returnValue($caseId));

        $controller->expects($this->once())
            ->method('generateFormWithData')
            ->with('statement', 'processAddStatement', array('case' => $caseId))
            ->will($this->returnValue($form));

        $controller->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($viewMock));

        $this->assertEquals($viewMock, $controller->addAction());
    }

    /**
     * Test editAction with missing statement
     */
    public function testEditActionWithMissingStatement()
    {
        $statementId = 7;

        $controller = $this->getMock(
            'Olcs\Controller\CaseStatementController',
            array('fromRoute', 'makeRestCall', 'notFoundAction')
        );

        $controller->expects($this->once())
            ->method('fromRoute')
            ->will($this->returnValue($statementId));

        $controller->expects($this->once())
            ->method('makeRestCall')
            ->with('Statement')
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
        $statementId = 7;
        $statementDetails = array(
            'statementType' => '5',
            'contactType' => '9',
            'requestorsAddressId' => 12
        );
        $addressDetails = array();

        $controller = $this->getMock(
            'Olcs\Controller\CaseStatementController',
            array('fromRoute', 'makeRestCall')
        );

        $controller->expects($this->once())
            ->method('fromRoute')
            ->will($this->returnValue($statementId));

        $controller->expects($this->at(1))
            ->method('makeRestCall')
            ->with('Statement')
            ->will($this->returnValue($statementDetails));

        $controller->expects($this->at(2))
            ->method('makeRestCall')
            ->with('Address')
            ->will($this->returnValue($addressDetails));
    }
}
