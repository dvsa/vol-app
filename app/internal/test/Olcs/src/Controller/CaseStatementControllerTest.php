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

        $addressDetails = array(
            'addressLine1' => '123 Street',
            'postcode' => 'AB1 0AB',
            'country' => 'GB',
            'city' => 'Leeds'
        );

        $form = '<form></form>';

        $expectedData = array(
            'details' => array(
                'statementType' => 'statement_type.5',
                'contactType' => 'contact_type.9',
                'requestorsAddressId' => 12
            ),
            'requestorsAddress' => array(
                'addressLine1' => '123 Street',
                'postcode' => 'AB1 0AB',
                'country' => 'country.GB',
                'city' => 'Leeds'
            )
        );

        $viewMock = $this->getMock('\stdClass', array('setTemplate'));

        $viewMock->expects($this->once())
            ->method('setTemplate')
            ->with('form');

        $controller = $this->getMock(
            'Olcs\Controller\CaseStatementController',
            array('fromRoute', 'makeRestCall', 'generateFormWithData', 'getView')
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

        $controller->expects($this->once())
            ->method('generateFormWithData')
            ->with('statement', 'processEditStatement', $expectedData)
            ->will($this->returnValue($form));

        $controller->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($viewMock));

        $this->assertEquals($viewMock, $controller->editAction());
    }

    /**
     * Test deleteAction Without matching data
     */
    public function testDeleteActionWithoutMatchingData()
    {
        $caseId = 7;
        $statementId = 9;

        $controller = $this->getMock(
            'Olcs\Controller\CaseStatementController',
            array('fromRoute', 'makeRestCall', 'notFoundAction')
        );

        $controller->expects($this->at(0))
            ->method('fromRoute')
            ->with('case')
            ->will($this->returnValue($caseId));

        $controller->expects($this->at(1))
            ->method('fromRoute')
            ->with('statement')
            ->will($this->returnValue($statementId));

        $controller->expects($this->once())
            ->method('makeRestCall')
            ->with('Statement', 'GET')
            ->will($this->returnValue(false));

        $controller->expects($this->once())
            ->method('notFoundAction')
            ->will($this->returnValue('404'));

        $this->assertEquals('404', $controller->deleteAction());
    }

    /**
     * Test deleteAction Without matching case
     */
    public function testDeleteActionWithoutMatchingCase()
    {
        $caseId = 7;
        $statementId = 9;
        $results = array(
            'case' => 6
        );

        $controller = $this->getMock(
            'Olcs\Controller\CaseStatementController',
            array('fromRoute', 'makeRestCall', 'notFoundAction')
        );

        $controller->expects($this->at(0))
            ->method('fromRoute')
            ->with('case')
            ->will($this->returnValue($caseId));

        $controller->expects($this->at(1))
            ->method('fromRoute')
            ->with('statement')
            ->will($this->returnValue($statementId));

        $controller->expects($this->once())
            ->method('makeRestCall')
            ->with('Statement', 'GET')
            ->will($this->returnValue($results));

        $controller->expects($this->once())
            ->method('notFoundAction')
            ->will($this->returnValue('404'));

        $this->assertEquals('404', $controller->deleteAction());
    }

    /**
     * Test deleteAction
     */
    public function testDeleteAction()
    {
        $caseId = 7;
        $statementId = 9;
        $results = array(
            'case' => 7
        );

        $mockRedirect = $this->getMock('\stdClass', array('toRoute'));

        $mockRedirect->expects($this->once())
            ->method('toRoute')
            ->will($this->returnValue('redirect'));

        $controller = $this->getMock(
            'Olcs\Controller\CaseStatementController',
            array('fromRoute', 'makeRestCall', 'redirect')
        );

        $controller->expects($this->at(0))
            ->method('fromRoute')
            ->with('case')
            ->will($this->returnValue($caseId));

        $controller->expects($this->at(1))
            ->method('fromRoute')
            ->with('statement')
            ->will($this->returnValue($statementId));

        $controller->expects($this->at(2))
            ->method('makeRestCall')
            ->with('Statement', 'GET')
            ->will($this->returnValue($results));

        $controller->expects($this->at(3))
            ->method('makeRestCall')
            ->with('Statement', 'DELETE')
            ->will($this->returnValue($results));

        $controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($mockRedirect));

        $this->assertEquals('redirect', $controller->deleteAction());
    }

    /**
     * Test processAddStatement
     */
    public function testProcessAddStatement()
    {
        $data = array(
            'case' => 9,
            'details' => array(
                'statementType' => 'statement_type.6',
                'contactType' => 'contact_type.3'
            ),
            'requestorsAddress' => array(
                'addressLine1' => '123 street',
                'postcode' => 'Ab1 1BD',
                'country' => 'country.GB',
                'city' => 'Leeds'
            )
        );

        $expectedProcessedData = array(
            'case' => 9,
            'statementType' => 6,
            'contactType' => 3,
            'addresses' => array(
                'requestorsAddress' => array(
                    'addressLine1' => '123 street',
                    'postcode' => 'Ab1 1BD',
                    'country' => 'country.GB',
                    'city' => 'Leeds'
                )
            )
        );

        $mockRedirect = $this->getMock('\stdClass', array('toRoute'));

        $mockRedirect->expects($this->once())
            ->method('toRoute')
            ->will($this->returnValue('redirect'));

        $controller = $this->getMock(
            'Olcs\Controller\CaseStatementController',
            array('makeRestCall', 'redirect')
        );

        $controller->expects($this->once())
            ->method('makeRestCall', $expectedProcessedData)
            ->with('Statement', 'POST');

        $controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($mockRedirect));

        $controller->processAddStatement($data);
    }

    /**
     * Test processEditStatement
     */
    public function testProcessEditStatement()
    {
        $data = array(
            'case' => 9,
            'details' => array(
                'statementType' => 'statement_type.6',
                'contactType' => 'contact_type.3'
            ),
            'requestorsAddress' => array(
                'addressLine1' => '123 street',
                'postcode' => 'Ab1 1BD',
                'country' => 'country.GB',
                'city' => 'Leeds'
            )
        );

        $expectedProcessedData = array(
            'case' => 9,
            'statementType' => 6,
            'contactType' => 3,
            'addresses' => array(
                'requestorsAddress' => array(
                    'addressLine1' => '123 street',
                    'postcode' => 'Ab1 1BD',
                    'country' => 'country.GB',
                    'city' => 'Leeds'
                )
            )
        );

        $mockRedirect = $this->getMock('\stdClass', array('toRoute'));

        $mockRedirect->expects($this->once())
            ->method('toRoute')
            ->will($this->returnValue('redirect'));

        $controller = $this->getMock(
            'Olcs\Controller\CaseStatementController',
            array('makeRestCall', 'redirect')
        );

        $controller->expects($this->once())
            ->method('makeRestCall', $expectedProcessedData)
            ->with('Statement', 'PUT');

        $controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($mockRedirect));

        $controller->processEditStatement($data);
    }
}
