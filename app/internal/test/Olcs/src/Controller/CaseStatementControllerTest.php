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
                'getCaseVariables',
                'setBreadcrumb'
            )
        );

        $caseId = 3;
        $licenceId = 7;

        $restResults = array();

        $variables = array(
            'foo' => 'bar'
        );

        $viewMock = $this->getMock('\stdClass', array('setTemplate'));

        $controller->expects($this->once())
            ->method('setBreadcrumb');

        $controller->expects($this->at(0))
            ->method('fromRoute')
            ->with('case')
            ->will($this->returnValue($caseId));

        $controller->expects($this->at(1))
            ->method('fromRoute')
            ->with('licence')
            ->will($this->returnValue($licenceId));

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
        $caseId = 24;
        $licenceId = 7;
        $form = '<form></form>';

        $viewMock = $this->getMock('\stdClass', array('setTemplate'));

        $viewMock->expects($this->once())
            ->method('setTemplate')
            ->with('form');

        $controller = $this->getMock(
            'Olcs\Controller\CaseStatementController',
            array('fromRoute', 'generateFormWithData', 'getView', 'setBreadcrumb')
        );

        $controller->expects($this->once())
            ->method('setBreadcrumb');

        $controller->expects($this->at(0))
            ->method('fromRoute')
            ->with('case')
            ->will($this->returnValue($caseId));

        $controller->expects($this->at(1))
            ->method('fromRoute')
            ->with('licence')
            ->will($this->returnValue($licenceId));

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
            array('fromRoute', 'makeRestCall', 'notFoundAction', 'setBreadcrumb')
        );

        $controller->expects($this->once())
            ->method('setBreadcrumb');

        $controller->expects($this->at(0))
            ->method('fromRoute')
            ->will($this->returnValue(3));

        $controller->expects($this->at(1))
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
        $caseId = 24;
        $licenceId = 7;
        $statementId = 7;

        $statementDetails = array(
            'statementType' => '5',
            'contactType' => '9',
            'requestorsAddress' => array(
                'addressLine1' => '123 Street',
                'postcode' => 'AB1 0AB',
                'countryCode' => 'GB',
                'town' => 'Leeds'
            )
        );

        $form = '<form></form>';

        $expectedData = array(
            'statementType' => '5',
            'contactType' => '9',
            'details' => array(
                'statementType' => 'statement_type.5',
                'contactType' => 'contact_type.9',
                'requestorsAddress' => array(
                    'addressLine1' => '123 Street',
                    'postcode' => 'AB1 0AB',
                    'countryCode' => 'GB',
                    'town' => 'Leeds'
                ),
            ),
            'requestorsAddress' => array(
                'addressLine1' => '123 Street',
                'postcode' => 'AB1 0AB',
                'countryCode' => 'GB',
                'town' => 'Leeds'
            ),
            'case' => $caseId
        );

        $viewMock = $this->getMock('\stdClass', array('setTemplate'));

        $viewMock->expects($this->once())
            ->method('setTemplate')
            ->with('form');

        $controller = $this->getMock(
            'Olcs\Controller\CaseStatementController',
            array('fromRoute', 'makeRestCall', 'generateFormWithData', 'getView', 'setBreadcrumb')
        );

        $controller->expects($this->once())
            ->method('setBreadcrumb');

        $controller->expects($this->at(0))
            ->method('fromRoute')
            ->with('case')
            ->will($this->returnValue($caseId));

        $controller->expects($this->at(1))
            ->method('fromRoute')
            ->with('licence')
            ->will($this->returnValue($licenceId));

        $controller->expects($this->at(2))
            ->method('fromRoute')
            ->with('id')
            ->will($this->returnValue($statementId));

        $controller->expects($this->once())
            ->method('makeRestCall')
            ->with('Statement')
            ->will($this->returnValue($statementDetails));

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
                'countryCode' => 'GB',
                'town' => 'Leeds'
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
                    'countryCode' => 'GB',
                    'town' => 'Leeds'
                )
            )
        );

        $mockRedirect = $this->getMock('\stdClass', array('toRoute'));

        $mockRedirect->expects($this->once())
            ->method('toRoute')
            ->will($this->returnValue('redirect'));

        $controller = $this->getMock(
            'Olcs\Controller\CaseStatementController',
            array('makeRestCall', 'fromRoute', 'redirect')
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
                'countryCode' => 'GB',
                'town' => 'Leeds'
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
                    'countryCode' => 'GB',
                    'town' => 'Leeds'
                )
            )
        );

        $mockRedirect = $this->getMock('\stdClass', array('toRoute'));

        $mockRedirect->expects($this->once())
            ->method('toRoute')
            ->will($this->returnValue('redirect'));

        $controller = $this->getMock(
            'Olcs\Controller\CaseStatementController',
            array('makeRestCall', 'fromRoute', 'redirect')
        );

        $controller->expects($this->once())
            ->method('makeRestCall', $expectedProcessedData)
            ->with('Statement', 'PUT');

        $controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($mockRedirect));

        $controller->processEditStatement($data);
    }

    public function testMapDocumentData()
    {
        $controller = $this->getMock(
            'Olcs\Controller\CaseStatementController',
            array(
                'getBookmarkData',
            )
        );

        $data['addresses']['requestorsAddress']['addressLine1'] = 'line1';
        $data['addresses']['requestorsAddress']['addressLine2'] = 'line2';
        $data['addresses']['requestorsAddress']['addressLine3'] = 'line3';
        $data['addresses']['requestorsAddress']['addressLine4'] = 'line4';
        $data['addresses']['requestorsAddress']['town'] = 'city';
        $data['addresses']['requestorsAddress']['postcode'] = 'AB1 2CD';
        $data['addresses']['requestorsAddress']['countryCode'] = 'GB';
        $data['requestorsForename'] = 'Joe';
        $data['requestorsFamilyName'] = 'Bloggs';
        $data['dateRequested'] = '2014-07-19';
        $data['authorisersDecision'] = 'Licence granted';

        $bookmarkData['licence']['trafficArea']['areaName'] = 'North East of England';
        $bookmarkData['licence']['licNo'] =  'OB12345';

        $controller->expects($this->once())
                ->method('getBookmarkData')
                ->with($data)
                ->will($this->returnValue($bookmarkData));

        $bookmarks = $controller->mapDocumentData($data);

    }

    public function testgetBookmarkData()
    {
        $controller = $this->getMock(
            'Olcs\Controller\CaseStatementController',
            array(
                'makeRestCall'
            )
        );

        $data['addresses']['requestorsAddress']['addressLine1'] = 'line1';
        $data['addresses']['requestorsAddress']['addressLine2'] = 'line2';
        $data['addresses']['requestorsAddress']['addressLine3'] = 'line3';
        $data['addresses']['requestorsAddress']['addressLine4'] = 'line4';
        $data['addresses']['requestorsAddress']['town'] = 'city';
        $data['addresses']['requestorsAddress']['postcode'] = 'AB1 2CD';
        $data['addresses']['requestorsAddress']['countryCode'] = 'GB';
        $data['requestorsForename'] = 'Joe';
        $data['requestorsFamilyName'] = 'Bloggs';
        $data['dateRequested'] = '2014-07-19';
        $data['authorisersDecision'] = 'Licence granted';

        $bookmarkData['licence']['trafficArea']['areaName'] = 'North East of England';
        $bookmarkData['licence']['licNo'] =  'OB12345';

        $data['case'] = 24;
        $bundle = array(
            'properties' => array(
                'id',
                'licence'
            ),
            'children' => array(
                'licence' => array(
                    'properties' => array(
                        'id',
                        'licNo',
                        'trafficArea',
                    ),
                    'children' => array(
                        'trafficArea' => array(
                            'properties' => array(
                                'id',
                                'areaName'
                            ),
                        )
                    )
                )
            )
        );

        $controller->expects($this->once())
                ->method('makeRestCall')
                ->with(
                    $this->equalTo('Cases'),
                    'GET',
                    ['id' => $data['case'], 'bundle' => json_encode($bundle)]
                )
                ->will($this->returnValue($bookmarkData));

        $bookmarks = $controller->mapDocumentData($data);

    }
}
