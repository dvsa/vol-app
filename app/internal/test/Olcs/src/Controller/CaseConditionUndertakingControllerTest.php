<?php

/**
 * Test CaseConditionUndertakingController
 *
 * @author Shaun Lizzio  <shaun.lizzio@valtech.co.uk>
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Test CaseConditionUndertakingController
 *
 * @author Shaun Lizzio  <shaun.lizzio@valtech.co.uk>
 */
class CaseConditionUndertakingControllerTest extends AbstractHttpControllerTestCase
{

    public function testIndexAction()
    {
        $controller = $this->getMock(
            'Olcs\Controller\CaseConditionUndertakingController',
            array(
                'fromRoute',
                'setBreadcrumb',
                'checkForCrudAction',
                'params',
                'getView',
                'getTabInformationArray',
                'getCase',
                'getCaseSummaryArray',
                'generateConditionTable',
                'generateUndertakingTable',
            )
        );

        $caseId = 24;
        $licenceId = 7;
        $variables = array(
            'foo' => 'bar'
        );
        $caseArray = ['id' => 24];
        $table = '<table></table>';

        $viewMock = $this->getMock('\stdClass', array('setVariables', 'setTemplate'));
        $viewMock->expects($this->once())
                ->method('setVariables');

        $mockParams = $this->getMock('\stdClass', array('fromPost'));

        $mockParams->expects($this->once())
                ->method('fromPost')
                ->with('table')
                ->will($this->returnValue('conditionUndertaking'));

        $controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $controller->expects($this->at(0))
            ->method('fromRoute')
            ->with($this->equalTo('case'))
            ->willReturn($caseId);

        $controller->expects($this->at(1))
            ->method('fromRoute')
            ->with($this->equalTo('licence'))
            ->willReturn($licenceId);

        $controller->expects($this->once())
            ->method('setBreadcrumb')
            ->with($this->equalTo(array('licence_case_list/pagination' => array('licence' => $licenceId))));

        $controller->expects($this->once())
            ->method('checkForCrudAction')
            ->with(
                $this->equalTo('conditionUndertaking'),
                $this->equalTo(array('case' => $caseId, 'licence' => $licenceId)),
                $this->equalTo('id')
            );

        $controller->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($viewMock));

        $controller->expects($this->once())
            ->method('getTabInformationArray')
            ->will($this->returnValue($variables));

        $controller->expects($this->once())
            ->method('getCase')
            ->with($this->equalTo($caseId))
            ->will($this->returnValue($caseArray));

        $controller->expects($this->once())
            ->method('getCaseSummaryArray')
            ->with($this->equalTo($caseArray))
            ->will($this->returnValue($caseArray));

        $controller->expects($this->once())
            ->method('generateConditionTable')
            ->with(
                $this->equalTo($caseId)
            )
            ->will($this->returnValue($table));

        $controller->expects($this->once())
            ->method('generateUndertakingTable')
            ->with(
                $this->equalTo($caseId)
            )
            ->will($this->returnValue($table));

        $this->assertEquals($viewMock, $controller->indexAction());
    }

    /**
     * Test for generating Condition table
     */
    public function testGenerateConditionTable()
    {
        $controller = $this->getMock(
            'Olcs\Controller\CaseConditionUndertakingController',
            array(
                'makeRestCall',
                'getPluginManager',
                'buildTable'
            )
        );

        $caseId = 24;

        $table = '<table></table>';

        $bundle = $this->getBundle('condition');
        $restResults = array('conditionUndertakings' => array(array('caseId' => '24')));

        $urlMock = $this->getMock('\stdClass');

        $mockPluginManager = $this->getMock('\stdClass', array('get'));
        $mockPluginManager->expects($this->once())
                ->method('get')
                ->with('url')
                ->willReturn($urlMock);

        $data = ['url' => $urlMock];

        $controller->expects($this->any())
            ->method('getConditionUndertakingBundle')
            ->with($this->equalTo('condition'))
            ->willReturn($bundle);

        $controller->expects($this->once())
            ->method('makeRestCall')
            ->with(
                $this->equalTo('VosaCase'),
                $this->equalTo('GET'),
                $this->equalTo(
                    array(
                        'id' => $caseId,
                        'bundle' => json_encode($bundle)
                    )
                )
            )
            ->willReturn($restResults);

        $controller->expects($this->once())
            ->method('getPluginManager')
            ->will($this->returnValue($mockPluginManager));

        $controller->expects($this->once())
                ->method('buildTable')
                ->with(
                    $this->equalTo('conditions'),
                    $this->equalTo($restResults['conditionUndertakings']),
                    $this->equalTo($data)
                )
                ->will($this->returnValue($table));

        $this->assertEquals($table, $controller->generateConditionTable($caseId));
    }

    /**
     * Test for generating Undertaking table
     */
    public function testGenerateUndertakingTable()
    {
        $controller = $this->getMock(
            'Olcs\Controller\CaseConditionUndertakingController',
            array(
                'makeRestCall',
                'getPluginManager',
                'buildTable'
            )
        );

        $caseId = 24;

        $table = '<table></table>';

        $bundle = $this->getBundle('undertaking');
        $restResults = array('conditionUndertakings' => array(array('caseId' => '24')));

        $urlMock = $this->getMock('\stdClass');

        $mockPluginManager = $this->getMock('\stdClass', array('get'));
        $mockPluginManager->expects($this->once())
                ->method('get')
                ->with('url')
                ->willReturn($urlMock);

        $data = ['url' => $urlMock];

        $controller->expects($this->any())
            ->method('getConditionUndertakingBundle')
            ->with($this->equalTo('undertaking'))
            ->willReturn($bundle);

        $controller->expects($this->once())
            ->method('makeRestCall')
            ->with(
                $this->equalTo('VosaCase'),
                $this->equalTo('GET'),
                $this->equalTo(
                    array(
                        'id' => $caseId,
                        'bundle' => json_encode($bundle)
                    )
                )
            )
            ->willReturn($restResults);

        $controller->expects($this->once())
            ->method('getPluginManager')
            ->will($this->returnValue($mockPluginManager));

        $controller->expects($this->once())
                ->method('buildTable')
                ->with(
                    $this->equalTo('undertakings'),
                    $this->equalTo($restResults['conditionUndertakings']),
                    $this->equalTo($data)
                )
                ->will($this->returnValue($table));

        $this->assertEquals($table, $controller->generateUndertakingTable($caseId));
    }

    private function getBundle($bundleType)
    {
        return array(
            'properties' => array(
                'id'
            ),
            'children' => array(
                'conditionUndertakings' => array(
                    'criteria' => array(
                        'conditionType' => $bundleType,
                    ),
                    'properties' => array(
                        'id',
                        'addedVia',
                        'isDraft',
                        'attachedTo',
                        'isFulfilled',
                        'operatingCentre'
                    ),
                    'children' => array(
                        'operatingCentre' => array(
                            'properties' => array(
                                'address',
                            ),
                            'children' => array(
                                'address' => array(
                                    'properties' => array(
                                        'addressLine1',
                                        'addressLine2',
                                        'addressLine3',
                                        'addressLine4',
                                        'paon_desc',
                                        'saon_desc',
                                        'street',
                                        'locality',
                                        'postcode',
                                        'country'
                                    )
                                )
                            )
                        )
                    )

                )
            )
        );
    }
}
