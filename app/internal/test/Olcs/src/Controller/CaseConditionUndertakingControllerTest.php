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

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../' . 'config/application.config.php'
        );
        $this->controller = $this->getMock(
            '\Olcs\Controller\CaseConditionUndertakingController',
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
                'getPluginManager',
                'configureFormForConditionType',
                'getLoggedInUser'
            )
        );
        parent::setUp();
    }

    public function testAddConditionAction()
    {
        $routeParams = ['case' => 24, 'licence' => 7, 'type' => 'condition'];

        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('case', 'licence', 'type'))
            ->will($this->returnValue($routeParams));

        $this->controller->expects($this->at(1))
            ->method('makeRestCall')
            ->with('Cases', 'GET', array('id' => $routeParams['case']))
            ->will($this->returnValue(array('id' => $routeParams['case'])));

        $mockParams = $this->getMock('\stdClass', array('fromPost'));

        $mockParams->expects($this->once())
            ->method('fromPost')
            ->with('cancel-conditionUndertaking')
            ->will($this->returnValue(null));

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $form = $this->getMock('\stdClass', array('setData'));

        $data['condition-undertaking'] = array(
            'addedVia' => 'Case',
            'conditionType' => 'cdt_con',
            'isDraft' => 0,
            'case' => $routeParams['case'],
            'licence' => $routeParams['licence']
        );
        $this->controller->expects($this->once())
            ->method('generateFormWithData')
            ->with('condition-undertaking-form', 'processConditionUndertaking', $data)
            ->will($this->returnValue($form));

        $this->controller->expects($this->once())
            ->method('configureFormForConditionType')
            ->with($form, $routeParams['licence'], $routeParams['type'])
            ->will($this->returnValue($form));

        $this->controller->addAction();
    }

    public function testAddConditionActionCaseInvalid()
    {
        $caseId = 24;
        $licenceId = 7;
        $operatingCentreId = 21;

        $type = 'cdt_con';
        $routeParams = ['case' => $caseId, 'licence' => $licenceId, 'type' => $type];

        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('case', 'licence', 'type'))
            ->will($this->returnValue(array('case' => $caseId, 'licence' => $licenceId, 'type' => $type)));

        $this->controller->expects($this->at(1))
            ->method('makeRestCall')
            ->with('Cases', 'GET', array('id' => $caseId))
            ->will($this->returnValue(null));

        $this->controller->addAction();

    }

    public function testAddConditionActionCancelled()
    {
        $caseId = 24;
        $licenceId = 7;
        $operatingCentreId = 21;

        $type = 'cdt_con';
        $routeParams = ['case' => $caseId, 'licence' => $licenceId, 'type' => $type];

        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('case', 'licence', 'type'))
            ->will($this->returnValue(array('case' => $caseId, 'licence' => $licenceId, 'type' => $type)));

        $this->controller->expects($this->at(1))
            ->method('makeRestCall')
            ->with('Cases', 'GET', array('id' => $caseId))
            ->will($this->returnValue(array('id' => $caseId)));

        $mockParams = $this->getMock('\stdClass', array('fromPost'));

        $mockParams->expects($this->once())
            ->method('fromPost')
            ->with('cancel-conditionUndertaking')
            ->will($this->returnValue('cancel'));

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $mockRedirect = $this->getMock('\stdClass', array('toRoute'));

        $mockRedirect->expects($this->once())
            ->method('toRoute')
            ->with(
                'case_conditions_undertakings', array(
                    'licence' => 7,
                    'case' => 24)
            )
            ->will($this->returnValue('mockUrl'));

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($mockRedirect));

        $this->controller->addAction();
    }

    public function testEditAction()
    {
        $caseId = 24;
        $licenceId = 7;
        $operatingCentreId = 21;
        $conditionId = 1;

        $type = 'condition';
        $routeParams = ['case' => $caseId, 'licence' => $licenceId, 'type' => $type, 'id' => $conditionId];

        $this->controller->expects($this->at(0))
            ->method('getParams')
            ->with(array('case', 'licence', 'type', 'id'))
            ->will($this->returnValue($routeParams));

        $this->controller->expects($this->at(1))
            ->method('makeRestCall')
            ->with('Cases', 'GET', array('id' => $caseId))
            ->will($this->returnValue(array('id' => $caseId)));

        $mockParams = $this->getMock('\stdClass', array('fromPost'));

        $mockParams->expects($this->once())
            ->method('fromPost')
            ->with('cancel-conditionUndertaking')
            ->will($this->returnValue(null));

        $this->controller->expects($this->at(2))
            ->method('params')
            ->will($this->returnValue($mockParams));

        $mockResults = [
            'addedVia' => array(
                'id' => 'Case'
            ),
            'conditionType' => array('id' => 'cdt_con'),
            'case' => ['id' => $caseId],
            'licence' => ['id' => $licenceId],
            'isDraft' => 1,
            'attachedTo' => 21,
            'operatingCentre' => ['id' => 21]
        ];

        $bundle = $this->controller->getConditionUndertakingBundleForUpdate();

        $this->controller->expects($this->at(4))
            ->method('makeRestCall')
            ->with(
                $this->equalTo('ConditionUndertaking'),
                $this->equalTo('GET'),
                $this->equalTo(array('id' => $conditionId)),
                $bundle
            )
            ->will($this->returnValue($mockResults));

        $form = $this->getMock('\stdClass', array('setData'));

        $data['condition-undertaking'] = array(
            'addedVia' => 'Case',
            'conditionType' => 'cdt_con',
            'isDraft' => 1,
            'case' => $routeParams['case'],
            'licence' => $routeParams['licence'],
            'attachedTo' => 21,
            'operatingCentre' => ['id' => 21]
        );

        $this->controller->expects($this->once())
            ->method('generateFormWithData')
            ->with('condition-undertaking-form', 'processConditionUndertaking', $data, false)
            ->will($this->returnValue($form));

        $mockOcAddressResults = [
            'Count' => 1,
            'Results' => [
                0 => [
                    'id' => 16,
                    'address' => [
                        'id' => 8,
                        'addressLine1' => 'Unit 5',
                        'addressLine2' => '12 Albert Street',
                        'addressLine3' => 'Westpoint',
                        'addressLine4' => '',
                        'postcode' => 'LS9 6NA',
                        'countryCode' => 'UK',
                    ]
                ]
            ]
        ];

        $this->controller->expects($this->once())
            ->method('configureFormForConditionType')
            ->with($form, $routeParams['licence'], $routeParams['type'])
            ->will($this->returnValue($form));

        $this->controller->editAction();
    }

    public function testEditActionInvalidOperatingCentre()
    {
        $caseId = 24;
        $licenceId = 7;
        $operatingCentreId = 21;
        $conditionId = 1;

        $type = 'cdt_con';
        $routeParams = ['case' => $caseId, 'licence' => $licenceId, 'type' => $type, 'id' => $conditionId];

        $this->controller->expects($this->at(0))
            ->method('getParams')
            ->with(array('case', 'licence', 'type', 'id'))
            ->will($this->returnValue($routeParams));

        $this->controller->expects($this->at(1))
            ->method('makeRestCall')
            ->with('Cases', 'GET', array('id' => $caseId))
            ->will($this->returnValue(array('id' => $caseId)));

        $mockParams = $this->getMock('\stdClass', array('fromPost'));

        $mockParams->expects($this->once())
            ->method('fromPost')
            ->with('cancel-conditionUndertaking')
            ->will($this->returnValue(null));

        $this->controller->expects($this->at(2))
            ->method('params')
            ->will($this->returnValue($mockParams));

        $mockResults = [
            'addedVia' => array('id' => 'Case'),
            'conditionType' => array(
                'id' => $routeParams['type']
            ),
            'case' => ['id' => $caseId],
            'licence' => ['id' => $licenceId],
            'isDraft' => 1,
            'attachedTo' => 21,
            'operatingCentre' => ['iddoesntexist' => 21]
        ];

        $bundle = $this->controller->getConditionUndertakingBundleForUpdate();

        $this->controller->expects($this->at(4))
            ->method('makeRestCall')
            ->with(
                $this->equalTo('ConditionUndertaking'),
                $this->equalTo('GET'),
                $this->equalTo(array('id' => $conditionId)),
                $bundle
            )
            ->will($this->returnValue($mockResults));

        $form = $this->getMock('\stdClass', array('setData'));

        $data['condition-undertaking'] = array(
            'addedVia' => 'Case',
            'conditionType' => $routeParams['type'],
            'isDraft' => 1,
            'case' => $routeParams['case'],
            'licence' => $routeParams['licence'],
            'attachedTo' => '',
            'operatingCentre' => ['iddoesntexist' => 21]
        );

        $this->controller->expects($this->once())
            ->method('generateFormWithData')
            ->with('condition-undertaking-form', 'processConditionUndertaking', $data, false)
            ->will($this->returnValue($form));

        $mockOcAddressResults = [
            'Count' => 1,
            'Results' => [
                0 => [
                    'id' => 16,
                    'address' => [
                        'id' => 8,
                        'addressLine1' => 'Unit 5',
                        'addressLine2' => '12 Albert Street',
                        'addressLine3' => 'Westpoint',
                        'addressLine4' => '',
                        'postcode' => 'LS9 6NA',
                        'countryCode' => 'UK',
                    ]
                ]
            ]
        ];

        $this->controller->expects($this->once())
            ->method('configureFormForConditionType')
            ->with($form, $routeParams['licence'], $routeParams['type'])
            ->will($this->returnValue($form));

        $this->controller->editAction();
    }

    public function testEditConditionActionCaseInvalid()
    {
        $caseId = 24;
        $licenceId = 7;
        $operatingCentreId = 21;
        $conditionId = 1;

        $type = 'cdt_con';
        $routeParams = ['case' => $caseId, 'licence' => $licenceId, 'type' => $type, 'id' => $conditionId];

        $this->controller->expects($this->at(0))
            ->method('getParams')
            ->with(array('case', 'licence', 'type', 'id'))
            ->will($this->returnValue($routeParams));

        $this->controller->expects($this->at(1))
            ->method('makeRestCall')
            ->with('Cases', 'GET', array('id' => $caseId))
            ->will($this->returnValue(null));

        $this->controller->editAction();

    }

    public function testEditConditionActionCancelled()
    {
        $caseId = 24;
        $licenceId = 7;
        $operatingCentreId = 21;
        $conditionId = 1;

        $type = 'cdt_con';
        $routeParams = ['case' => $caseId, 'licence' => $licenceId, 'type' => $type, 'id' => $conditionId];

        $this->controller->expects($this->at(0))
            ->method('getParams')
            ->with(array('case', 'licence', 'type', 'id'))
            ->will($this->returnValue($routeParams));

        $this->controller->expects($this->at(1))
            ->method('makeRestCall')
            ->with('Cases', 'GET', array('id' => $caseId))
            ->will($this->returnValue(array('id' => $caseId)));

        $mockParams = $this->getMock('\stdClass', array('fromPost'));

        $mockParams->expects($this->once())
            ->method('fromPost')
            ->with('cancel-conditionUndertaking')
            ->will($this->returnValue('cancel'));

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $mockRedirect = $this->getMock('\stdClass', array('toRoute'));

        $mockRedirect->expects($this->once())
            ->method('toRoute')
            ->with(
                'case_conditions_undertakings', array(
                    'licence' => 7,
                    'case' => 24)
            )
            ->will($this->returnValue('mockUrl'));

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($mockRedirect));

        $this->controller->editAction();
    }

    /**
     * Method to test getting OC address list for a given licence
     */
    public function testGetOcAddressByLicence()
    {
        $licenceId = 7;

        $mockOcAddressResults = [
            'Count' => 1,
            'Results' => [
                0 => [
                    'id' => 16,
                    'address' => [
                        'id' => 8,
                        'addressLine1' => 'Unit 5',
                        'addressLine2' => '12 Albert Street',
                        'addressLine3' => 'Westpoint',
                        'addressLine4' => '',
                        'postcode' => 'LS9 6NA',
                        'countryCode' => 'UK',
                    ]
                ]
            ]
        ];

        $operatingCentreAddressBundle = $this->controller->getOCAddressBundle();

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with(
                'OperatingCentre',
                'GET',
                array(
                    'licence' => $licenceId
                ),
                $operatingCentreAddressBundle
            )
            ->will($this->returnValue($mockOcAddressResults));

        $this->controller->getOCAddressByLicence($licenceId);
    }

    public function testConfigureFormForConditionType()
    {
        $this->controller = $this->getMock(
            '\Olcs\Controller\CaseConditionUndertakingController',
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
                'getPluginManager',
                'getOCAddressByLicence'
            )
        );

        $licenceId = 7;
        $type = 'cdt_con';

        $mockOcAddressResults = [
            'Count' => 1,
            'Results' => [
                0 => [
                    'id' => 16,
                    'address' => [
                        'id' => 8,
                        'addressLine1' => 'Unit 5',
                        'addressLine2' => '12 Albert Street',
                        'addressLine3' => 'Westpoint',
                        'addressLine4' => '',
                        'postcode' => 'LS9 6NA',
                        'countryCode' => 'UK',
                    ]
                ]
            ]
        ];

        $this->controller->expects($this->once())
            ->method('getOCAddressByLicence')
            ->with($licenceId)
            ->will($this->returnValue($mockOcAddressResults));

        $mockForm = $this->getMock('StdClass', ['get']);
        $mockFieldset = $this->getMock('StdClass', ['get']);
        $mockElement = $this->getMock('StdClass', ['setLabel', 'setValueOptions']);

        $mockFieldset->expects($this->at(0))
            ->method('get')
            ->with($this->equalTo('notes'))
            ->will($this->returnValue($mockElement));

        $mockFieldset->expects($this->at(1))
            ->method('get')
            ->with($this->equalTo('attachedTo'))
            ->will($this->returnValue($mockElement));

        $mockElement->expects($this->at(0))
            ->method('setLabel')
            ->with($this->equalTo(ucfirst($type)))
            ->will($this->returnSelf());

        $mockElement->expects($this->at(1))
            ->method('setValueOptions')
            ->with($this->equalTo($mockOcAddressResults))
            ->will($this->returnSelf());

        $mockForm->expects($this->at(0))
            ->method('get')
            ->with($this->equalTo('condition-undertaking'))
            ->will($this->returnValue($mockFieldset));

        $mockForm->expects($this->at(1))
            ->method('get')
            ->with($this->equalTo('condition-undertaking'))
            ->will($this->returnValue($mockFieldset));

        $this->controller->configureFormForConditionType($mockForm, $licenceId, $type);
    }

    public function testProcessConditionUndertakingAddAttachedToLicenceAction()
    {

        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('action', 'licence', 'case'))
            ->will($this->returnValue(array ( 'licence' => 7, 'case' => 24, 'action' => 'add' )));

        $functionData = ['condition-undertaking' =>
            [
                'attachedTo' => 'Licence',
            ]
        ];

        $addData['createdOn'] = date('d-m-Y h:i:s');
        $addData['lastUpdatedOn'] = date('d-m-Y h:i:s');
        $addData['operatingCentre'] = null;
        $addData['attachedTo'] = 'Licence';
        $addData['createdBy'] = 1;

        $toRoute = $this->getMock('\stdClass', array('toRoute'));
        $toRoute->expects($this->once())
            ->method('toRoute')
            ->with(
                'case_conditions_undertakings', array(
                    'case' =>  24, 'licence' => 7
                )
            );

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($toRoute));

        $this->controller->expects($this->once())
            ->method('getLoggedInUser')
            ->will($this->returnValue(1));

        $this->controller->expects($this->once())
            ->method('processAdd')
            ->will($this->returnValue(['id' => 1]));

        $this->controller->processConditionUndertaking($functionData);
    }

    public function testAddAttachedToOcAction()
    {

        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('action', 'licence', 'case'))
            ->will($this->returnValue(array ( 'licence' => 7, 'case' => 24, 'action' => 'add' )));

        $functionData = ['condition-undertaking' =>
            [
                'attachedTo' => 21,
            ]
        ];

        $addData['createdOn'] = date('d-m-Y h:i:s');
        $addData['lastUpdatedOn'] = date('d-m-Y h:i:s');
        $addData['operatingCentre'] = 21;
        $addData['attachedTo'] = 'OC';
        $addData['createdBy'] = 1;

        $toRoute = $this->getMock('\stdClass', array('toRoute'));
        $toRoute->expects($this->once())
            ->method('toRoute')
            ->with(
                'case_conditions_undertakings', array(
                    'case' =>  24, 'licence' => 7
                )
            );

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($toRoute));

        $this->controller->expects($this->once())
            ->method('getLoggedInUser')
            ->will($this->returnValue(1));

        $this->controller->expects($this->once())
            ->method('processAdd')
            ->will($this->returnValue(['id' => 1]));

        $this->controller->processConditionUndertaking($functionData);
    }

    public function testProcessConditionUndertakingEditAttachedToLicenceAction()
    {

        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('action', 'licence', 'case'))
            ->will($this->returnValue(array ( 'licence' => 7, 'case' => 24, 'action' => 'edit' )));

        $functionData = ['condition-undertaking' =>
            [
                'attachedTo' => 'Licence',
            ]
        ];

        $editData['lastUpdatedOn'] = date('d-m-Y h:i:s');
        $editData['operatingCentre'] = null;
        $editData['attachedTo'] = 'Licence';
        $editData['createdBy'] = 1;

        $toRoute = $this->getMock('\stdClass', array('toRoute'));
        $toRoute->expects($this->once())
            ->method('toRoute')
            ->with(
                'case_conditions_undertakings', array(
                    'case' =>  24, 'licence' => 7
                )
            );

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($toRoute));

        $this->controller->expects($this->once())
            ->method('getLoggedInUser')
            ->will($this->returnValue(1));

        $this->controller->expects($this->once())
            ->method('processEdit')
            ->will($this->returnValue(['id' => 1]));

        $this->controller->processConditionUndertaking($functionData);
    }

    public function testEditAttachedToOcAction()
    {

        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('action', 'licence', 'case'))
            ->will($this->returnValue(array ( 'licence' => 7, 'case' => 24, 'action' => 'edit' )));

        $functionData = ['condition-undertaking' =>
            [
                'attachedTo' => 21,
            ]
        ];

        $editData['lastUpdatedOn'] = date('d-m-Y h:i:s');
        $editData['operatingCentre'] = 21;
        $editData['attachedTo'] = 'OC';
        $editData['createdBy'] = 1;

        $toRoute = $this->getMock('\stdClass', array('toRoute'));
        $toRoute->expects($this->once())
            ->method('toRoute')
            ->with(
                'case_conditions_undertakings', array(
                    'case' =>  24, 'licence' => 7
                )
            );

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($toRoute));

        $this->controller->expects($this->once())
            ->method('getLoggedInUser')
            ->will($this->returnValue(1));

        $this->controller->expects($this->once())
            ->method('processEdit')
            ->will($this->returnValue(['id' => 1]));

        $this->controller->processConditionUndertaking($functionData);
    }

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
                'getTable'
            )
        );

        $caseId = 24;

        $table = '<table></table>';

        $bundle = $this->getBundle('cdt_con');
        $restResults = array(
            'conditionUndertakings' => array(
                array(
                    'caseId' => 24,
                    'operatingCentre' => array(
                        'address' => 'Some Address'
                    ),
                    'attachedTo' => array(
                        'properties' => 'ALL'
                    )
                )
            )
        );

        $expectedTableData = array(
            array(
                'caseId' => 24,
                'operatingCentreAddress' => 'Some Address',
                'operatingCentre' => array(
                    'address' => 'Some Address'
                ),
                'attachedTo' => array(
                    'properties' => 'ALL'
                )
            )
        );

        $controller->expects($this->any())
            ->method('getConditionUndertakingBundle')
            ->with($this->equalTo('cdt_con'))
            ->willReturn($bundle);

        $controller->expects($this->once())
            ->method('makeRestCall')
            ->with(
                $this->equalTo('Cases'),
                $this->equalTo('GET'),
                $this->equalTo(
                    array(
                        'id' => $caseId
                    )
                ),
                $bundle
            )
            ->willReturn($restResults);

        $controller->expects($this->once())
                ->method('getTable')
                ->with(
                    $this->equalTo('conditions'),
                    $this->equalTo($expectedTableData)
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
                'getTable'
            )
        );

        $caseId = 24;

        $table = '<table></table>';

        $bundle = $this->getBundle('cdt_und');
        $restResults = array(
            'conditionUndertakings' => array(
                array(
                    'caseId' => 24,
                    'operatingCentre' => array(
                        'address' => 'Some Address'
                    )
                )
            )
        );

        $expectedTableData = array(
            array(
                'caseId' => 24,
                'operatingCentreAddress' => 'Some Address',
                'operatingCentre' => array(
                    'address' => 'Some Address'
                )
            )
        );

        $controller->expects($this->any())
            ->method('getConditionUndertakingBundle')
            ->with($this->equalTo('cdt_und'))
            ->willReturn($bundle);

        $controller->expects($this->once())
            ->method('makeRestCall')
            ->with(
                $this->equalTo('Cases'),
                $this->equalTo('GET'),
                $this->equalTo(
                    array(
                        'id' => $caseId
                    )
                ),
                $bundle
            )
            ->willReturn($restResults);

        $controller->expects($this->once())
                ->method('getTable')
                ->with(
                    $this->equalTo('undertakings'),
                    $this->equalTo($expectedTableData)
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
                                        'town',
                                        'postcode'
                                    ),
                                    'children' => array(
                                        'countryCode' => array(
                                            'properties' => array('id')
                                        )
                                    )
                                )
                            )
                        ),

                        'attachedTo' => array(
                            'properties' => 'ALL'
                        )
                    )
                )
            )
        );
    }
}
