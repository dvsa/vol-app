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
}
