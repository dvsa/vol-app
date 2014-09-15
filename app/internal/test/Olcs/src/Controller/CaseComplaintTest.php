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
class CaseComplaintTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../' . 'config/application.config.php'
        );
        $this->controller = $this->getMock(
            '\Olcs\Controller\CaseComplaintController',
            array(
                'params',
                'getParams',
                'redirect',
                'setBreadcrumb',
                'makeRestCall',
                'generateFormWithData',
                'generateForm',
                'setData',
                'processEdit',
                'processAdd',
                'getServiceManager',
                'getPluginManager'
            )
        );
        parent::setUp();
    }

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
                'getTable',
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
        $bundle = $this->getComplaintBundle();

        $restResults['complaintCases'] = [];

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
            ->with('Cases', 'GET', array('id' => $caseId), $bundle)
            ->will($this->returnValue($restResults));

        $controller->expects($this->once())
                ->method('getTable')
                ->with(
                    $this->equalTo('complaints'),
                    $this->equalTo($restResults['complaintCases'])
                )
                ->will($this->returnValue('<table></table>'));

        $this->assertEquals($viewMock, $controller->indexAction());
    }

    public function testAddAction()
    {

        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('case', 'licence', 'id'))
            ->will($this->returnValue(array('case' => 24, 'licence' => 7)));

        $mockParams = $this->getMock('\stdClass', array('fromPost'));

        $mockParams->expects($this->once())
            ->method('fromPost')
            ->with('cancel-complaint')
            ->will($this->returnValue(null));

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with('Cases', 'GET', array('id' => 24))
            ->will($this->returnValue(array('id' => 24)));

        $form = $this->getMock('\stdClass', array('setData'));

        $this->controller->expects($this->once())
            ->method('generateForm')
            ->with('complaint', 'processComplaint')
            ->will($this->returnValue($form));

        $form->expects($this->once())
            ->method('setData')
            ->with(
                [
                    'case' => 24,
                    'organisation-details' => [
                        'id' => 7,
                        'version' => 1
                    ]
                ]
            );

        $this->controller->addAction();
    }

    /**
     * Test for cancel button pressed.
     */
    public function testAddActionCancelled()
    {
        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('case', 'licence', 'id'))
            ->will($this->returnValue(array('case' => 24, 'licence' => 7)));

        $mockParams = $this->getMock('\stdClass', array('fromPost'));

        $mockParams->expects($this->once())
            ->method('fromPost')
            ->with('cancel-complaint')
            ->will($this->returnValue('cancel'));

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $mockRedirect = $this->getMock('\stdClass', array('toRoute'));

        $mockRedirect->expects($this->once())
            ->method('toRoute')
            ->with(
                'case_complaints', array(
                    'licence' => 7,
                    'case' => 24)
            )
            ->will($this->returnValue('mockUrl'));

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($mockRedirect));

        $this->controller->addAction();
    }

    public function testAddActionFail()
    {

        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('case', 'licence', 'id'))
            ->will($this->returnValue(array('case' => null, 'licence' => null)));

        $mockParams = $this->getMock('\stdClass', array('fromPost'));

        $mockParams->expects($this->once())
            ->method('fromPost')
            ->with('cancel-complaint')
            ->will($this->returnValue(null));

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with('Cases', 'GET', array('id' => null))
            ->will($this->returnValue(null));

        $this->controller->addAction();
    }

    public function testEditAction()
    {
        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('case', 'licence', 'id'))
            ->will($this->returnValue(array('case' => 24, 'licence' => 7, 'id' => 1)));

        $bundle = $this->getComplaintBundleForUpdates();

        $mockParams = $this->getMock('\stdClass', array('fromPost'));

        $mockParams->expects($this->once())
            ->method('fromPost')
            ->with('cancel-complaint')
            ->will($this->returnValue(null));

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $returnArray = [
            'id' => 24,
            'status' => array(
                'id' => 'cs_ack'
            ),
            'complaintType' => array(
                'id' => 'ct_cor'
            ),
            'organisation' => '2',
            'complainantContactDetails' => ['person' => 3],
            'driver' => ['contactDetails' => ['person' => 5]]
        ];

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with('Complaint', 'GET', array('id' => 1), $bundle)
            ->will($this->returnValue($returnArray));

        $expectedFormData = array(
            'id' => 24,
            'status' => array(
                'id' => 'cs_ack'
            ),
            'complaintType' => array(
                'id' => 'ct_cor'
            ),
            'organisation' => '2',
            'complainantContactDetails' => ['person' => 3],
            'driver' => ['contactDetails' => ['person' => 5]],
            'case' => 24,
            'organisation-details' => '2',
            'complaint-details' => array(
                'id' => 24,
                'status' => 'cs_ack',
                'complaintType' => 'ct_cor',
                'organisation' => '2',
                'complainantContactDetails' => array('person' => 3),
                'driver' => array(
                    'contactDetails' => array(
                        'person' => 5
                    )
                ),
                'case' => 24,
                'organisation-details' => '2',
            ),
            'complainant-details' => 3,
            'driver-details' => 5
        );

        $form = $this->getMock('\stdClass', array('setData'));

        $this->controller->expects($this->once())
            ->method('generateFormWithData')
            ->with('complaint', 'processComplaint', $expectedFormData)
            ->will($this->returnValue($form));

        $this->controller->editAction();
    }

    /**
     * Test for cancel button pressed.
     */
    public function testEditActionCancelled()
    {
        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('case', 'licence', 'id'))
            ->will($this->returnValue(array('case' => 24, 'licence' => 7)));

        $mockParams = $this->getMock('\stdClass', array('fromPost'));

        $mockParams->expects($this->once())
            ->method('fromPost')
            ->with('cancel-complaint')
            ->will($this->returnValue('cancel'));

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $mockRedirect = $this->getMock('\stdClass', array('toRoute'));

        $mockRedirect->expects($this->once())
            ->method('toRoute')
            ->with(
                'case_complaints', array(
                    'licence' => 7,
                    'case' => 24)
            )
            ->will($this->returnValue('mockUrl'));

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($mockRedirect));

        $this->controller->editAction();
    }

    public function testEditActionFail()
    {
        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('case', 'licence', 'id'))
            ->will($this->returnValue(array('case' => null, 'licence' => null, 'id' => '')));

        $mockParams = $this->getMock('\stdClass', array('fromPost'));

        $bundle = $this->getComplaintBundleForUpdates();

        $mockParams->expects($this->once())
            ->method('fromPost')
            ->with('cancel-complaint')
            ->will($this->returnValue(null));

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with('Complaint', 'GET', array('id' => ''), $bundle)
            ->will($this->returnValue(null));

        $this->controller->editAction();
    }

    public function testProcessComplaintEditAction()
    {
        $data['complaint-details'] = ['id' => 1];
        $data['complainant-details'] = ['id' => 2];
        $data['driver-details'] = ['id' => 3];

        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('action', 'licence', 'case'))
            ->will($this->returnValue(array ( 'licence' => 7, 'case' => 54, 'action' => 'edit' )));

        $this->controller->expects($this->at(1))
            ->method('processEdit')
            ->with($data['complaint-details'], 'Complaint')
            ->will($this->returnValue(array('id' => 1)));

        $this->controller->expects($this->at(2))
            ->method('processEdit')
            ->with($data['complainant-details'], 'Person')
            ->will($this->returnValue(array('id' => 2)));

        $this->controller->expects($this->at(3))
            ->method('processEdit')
            ->with($data['driver-details'], 'Person')
            ->will($this->returnValue(array('id' => 3)));

        $toRoute = $this->getMock('\stdClass', array('toRoute'));

        $toRoute->expects($this->once())
            ->method('toRoute')
            ->with(
                'case_complaints', array(
                    'case' =>  54, 'licence' => 7
                )
            );

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($toRoute));

        $this->controller->processComplaint($data);
    }

    public function testProcessComplaintAddAction()
    {
        $this->markTestIncomplete('This test needs attention');

        $functionData = [];
        $functionData['complaint-details'] = [['id => 1']];
        $functionData['case'] = ['vc'];
        $functionData['organisation-details'] = 1;
        $functionData['driver-details'] = ['dd'];
        $functionData['complainant-details'] = ['cnd'];

        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('action', 'licence', 'case'))
            ->will($this->returnValue(array('licence' => 7, 'case' => 54, 'action' => 'add')));

        $returnData = ['id' => 1];

        unset($functionData['complaint-details']['version']);
        unset($functionData['organisation-details']['version']);
        $addData = $functionData['complaint-details'];
        $addData['cases'][] = $functionData['case'];
        $addData['value'] = '';
        $addData['vehicle_id'] = 1;
        $addData['organisation'] = $functionData['organisation-details'];

        $addData['driver']['contactDetails']['contactType'] = 'ct_driver';
        $addData['driver']['contactDetails']['is_deleted'] = 0;
        $addData['driver']['contactDetails']['person'] = $functionData['driver-details'];
        unset($addData['driver']['contactDetails']['person']['version']);

        $addData['complainant']['contactType'] = 'ct_complainant';
        $addData['complainant']['is_deleted'] = 0;
        $addData['complainant']['person'] = $functionData['complainant-details'];
        unset($addData['complainant']['person']['version']);

        $toRoute = $this->getMock('\stdClass', array('toRoute'));

        $toRoute->expects($this->once())
            ->method('toRoute')
            ->with(
                'case_complaints', array(
                    'case' =>  54, 'licence' => 7
                )
            );

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($toRoute));

        $this->controller->expects($this->once())
            ->method('processAdd')
            ->with($addData)
            ->will($this->returnValue(['id' => 1]));

        $this->controller->processComplaint($functionData);
    }

    private function getAddData()
    {
        return array(
            array(
                'id => 1'
            ),
            'cases' => array(
                array(
                    'vc'
                ),
            ),
            'value' => '',
            'vehicle_id' => 1,
            'organisation' => array(
                'od',
            ),
            'driver' => array(
                'contactDetails' => array(
                    'contactType' => 'ct_driver',
                    'is_deleted' => 0,
                    'version' => 1,
                    'person' => array(
                        'contactDetails' => array(
                            'contactType' => 'ct_driver',
                            'is_deleted' => 0,
                            'version' => 1,
                            'person' => array(
                                0 => 'dd'
                            ),
                        ),
                    ),
                ),
            ),
            'complainant' => array(
                'contactType' => 'ct_complainant',
                'is_deleted' => 0,
                'version' => 1,
                'person' => array(
                    'contactType' => 'ct_complainant',
                    'is_deleted' => 0,
                    'version' => 1,
                    'person' => array(
                        0 => 'cnd'
                    )
                )
            )
        );
    }

    public function getComplaintBundleForUpdates()
    {
        return array(
            'complaint' => array(
                'properties' => array('ALL'),
            ),
            'children' => array(
                'status' => array(
                    'properties' => array('id')
                ),
                'complaintType' => array(
                    'properties' => array('id')
                ),
                'driver' => array(
                    'properties' => array('id', 'version'),
                    'children' => array(
                        'contactDetails' => array(
                            'properties' => array('id', 'version'),
                            'children' => array(
                                'person' => array(
                                    'properties' => array(
                                        'id',
                                        'version',
                                        'forename',
                                        'familyName',
                                    )
                                )
                            )
                        )
                    )
                ),
                'complainantContactDetails' => array(
                    'children' => array(
                        'person' => array(
                            'properties' => array(
                                'id',
                                'version',
                                'forename',
                                'familyName',
                            )
                        )
                    )
                ),
                'organisation' => array(
                    'properties' => array('id', 'version', 'name'),
                )
            )
        );
    }

    public function getComplaintBundle()
    {
        return array(
            'properties' => array(
                'id'
            ),
            'children' => array(
                'complaintCases' => array(
                    'children' => array(
                        'complaint' => array(
                            'properties' => array(
                                'id',
                                'complaintDate',
                                'description'
                            ),
                            'children' => array(
                                'complainantContactDetails' => array(
                                    'properties' => array(
                                        'id',
                                    ),
                                   'children' => array(
                                       'person' => array(
                                           'properties' => array(
                                               'forename',
                                               'familyName'
                                            )
                                        )
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
