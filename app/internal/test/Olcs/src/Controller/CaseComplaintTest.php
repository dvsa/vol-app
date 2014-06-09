<?php

/**
 * Test CaseComplaintController
 *
 * @author Shaun Lizzio  <shaun.lizzio@valtech.co.uk>
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Test CaseComplaintController
 *
 * @author Shaun Lizzio  <shaun.lizzio@valtech.co.uk>
 */
class CaseComplaintControllerTest extends AbstractHttpControllerTestCase
{
    public function testIndexAction()
    {
        $controller = $this->getMock(
            'Olcs\Controller\CaseComplaintController',
            array(
                'setBreadcrumb',
                'getView',
                'getTabInformationArray',
                'fromRoute',
                'getCase',
                'getCaseSummaryArray',
                'makeRestCall',
                'buildTable',
                'getPluginManager',
                'checkForCrudAction'
            )
        );

        $caseId = 24;
        $licenceId = 7;
        $complaintId = 1;

        $restResults = array();

        $variables = array(
            'foo' => 'bar'
        );

        $caseArray = ['id' => 24];
        $bundle = array(
            'properties' => array(
                'id'
            ),
            'children' => array(
                'complaints' => array(
                    'properties' => array(
                        'id',
                        'complaintDate',
                        'description',
                        'complainant'
                    ),
                    'children' => array(
                        'complainant' => array(
                            'properties' => array(
                                'id',
                                'person'
                            ),
                           'children' => array(
                               'person' => array(
                                   'properties' => array(
                                       'firstName',
                                       'middleName',
                                       'surname',
                                   )
                               )
                           )
                        )
                    )
                )
            )
        );

        $restResults['complaints'] = ['foo' => 'bar'];
        $urlMock = $this->getMock('\stdClass');

        $mockPluginManager = $this->getMock('\stdClass', array('get'));
        $mockPluginManager->expects($this->once())
                ->method('get')
                ->with('url')
                ->willReturn($urlMock);

        $data = ['url' => $urlMock];

        $viewMock = $this->getMock('\stdClass', array('setVariables', 'setTemplate'));
        $viewMock->expects($this->once())
                ->method('setVariables');

        $controller->expects($this->once())
            ->method('setBreadcrumb');

        $controller->expects($this->once())
            ->method('checkForCrudAction')
            ->with(
                $this->equalTo('complaint'),
                $this->equalTo(array('case' => $caseId, 'licence' => $licenceId)),
                $this->equalTo('id')
            );

        $controller->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($viewMock));

        $controller->expects($this->once())
            ->method('getTabInformationArray')
            ->will($this->returnValue($variables));

        $controller->expects($this->at(0))
            ->method('fromRoute')
            ->with('case')
            ->will($this->returnValue($caseId));

        $controller->expects($this->at(1))
            ->method('fromRoute')
            ->with('licence')
            ->will($this->returnValue($licenceId));

        $controller->expects($this->once())
            ->method('getCase')
            ->with($this->equalTo($caseId))
            ->will($this->returnValue($caseArray));

        $controller->expects($this->once())
            ->method('getCaseSummaryArray')
            ->with($this->equalTo($caseArray))
            ->will($this->returnValue($caseArray));

        $controller->expects($this->once())
            ->method('makeRestCall')
            ->with(
                $this->equalTo('VosaCase'),
                $this->equalTo('GET'),
                $this->equalTo(
                    array('id' => $caseId, 'bundle' => \json_encode($bundle))
                )
            )
            ->will($this->returnValue($restResults));

        $controller->expects($this->once())
                ->method('getPluginManager')
                ->will($this->returnValue($mockPluginManager));

        $controller->expects($this->once())
                ->method('buildTable')
                ->with(
                    $this->equalTo('complaints'),
                    $this->equalTo($restResults['complaints']),
                    $this->equalTo($data)
                )
                ->will($this->returnValue('<table></table>'));

        $this->assertEquals($viewMock, $controller->indexAction());
    }
}
