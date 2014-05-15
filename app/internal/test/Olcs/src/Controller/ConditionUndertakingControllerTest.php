<?php

/**
 * ConditionUndertaking controller form post tests
 *
 * @author Shaun Lizzio  <shaun.lizzio@valtech.co.uk>
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * ConditionUndertaking controller form post tests
 *
 * @author Shaun Lizzio  <shaun.lizzio@valtech.co.uk>
 */
class ConditionUndertakingControllerTest extends AbstractHttpControllerTestCase
{

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../' . 'config/application.config.php'
        );
        $this->controller = $this->getMock(
            '\Olcs\Controller\ConditionUndertakingController',
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
        $caseId = 24;
        $licenceId = 7;
        $operatingCentreId = 21;

        $type = 'condition';
        $routeParams = ['case' => $caseId, 'licence' => $licenceId, 'type' => $type];

        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('case', 'licence', 'type'))
            ->will($this->returnValue(array('case' => $caseId, 'licence' => $licenceId, 'type' => $type)));

        $this->controller->expects($this->at(1))
            ->method('makeRestCall')
            ->with('VosaCase', 'GET', array('id' => $caseId))
            ->will($this->returnValue(array('id' => $caseId)));

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
            'conditionType' => $routeParams['type'],
            'isDraft' => 0,
            'vosaCase' => $routeParams['case'],
            'licence' => $routeParams['licence']
        );
        $this->controller->expects($this->once())
            ->method('generateFormWithData')
            ->with('condition-undertaking-form', 'processConditionUndertaking', $data)
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
                        'country' => 'UK',
                     ]
                 ]
             ]
        ];

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

        $type = 'condition';
        $routeParams = ['case' => $caseId, 'licence' => $licenceId, 'type' => $type];

        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('case', 'licence', 'type'))
            ->will($this->returnValue(array('case' => $caseId, 'licence' => $licenceId, 'type' => $type)));

        $this->controller->expects($this->at(1))
            ->method('makeRestCall')
            ->with('VosaCase', 'GET', array('id' => $caseId))
            ->will($this->returnValue(null));

        $this->controller->addAction();

    }

    public function testAddConditionActionCancelled()
    {
        $caseId = 24;
        $licenceId = 7;
        $operatingCentreId = 21;

        $type = 'condition';
        $routeParams = ['case' => $caseId, 'licence' => $licenceId, 'type' => $type];

        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('case', 'licence', 'type'))
            ->will($this->returnValue(array('case' => $caseId, 'licence' => $licenceId, 'type' => $type)));

        $this->controller->expects($this->at(1))
            ->method('makeRestCall')
            ->with('VosaCase', 'GET', array('id' => $caseId))
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
            ->with('VosaCase', 'GET', array('id' => $caseId))
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
            'addedVia' => 'Case',
            'conditionType' => $routeParams['type'],
            'vosaCase' => ['id' => $caseId],
            'licence' => ['id' => $licenceId],
            'isDraft' => 1,
            'attachedTo' => 21,
            'operatingCentre' => ['id' => 21]
        ];

        $bundle = $this->controller->getConditionUndertakingBundle();

        $this->controller->expects($this->at(4))
            ->method('makeRestCall')
            ->with(
                $this->equalTo('ConditionUndertaking'),
                $this->equalTo('GET'),
                $this->equalTo(array('id' => $conditionId, 'bundle' => json_encode($bundle)))
            )
            ->will($this->returnValue($mockResults));

        $form = $this->getMock('\stdClass', array('setData'));

        $data['condition-undertaking'] = array(
            'addedVia' => 'Case',
            'conditionType' => $routeParams['type'],
            'isDraft' => 1,
            'vosaCase' => $routeParams['case'],
            'licence' => $routeParams['licence'],
            'attachedTo' => 21,
            'operatingCentre' => ['id' => 21]
        );

        $this->controller->expects($this->once())
            ->method('generateFormWithData')
            ->with('condition-undertaking-form', 'processConditionUndertaking', $data, true)
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
                        'country' => 'UK',
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

        $type = 'condition';
        $routeParams = ['case' => $caseId, 'licence' => $licenceId, 'type' => $type, 'id' => $conditionId];

        $this->controller->expects($this->at(0))
            ->method('getParams')
            ->with(array('case', 'licence', 'type', 'id'))
            ->will($this->returnValue($routeParams));

        $this->controller->expects($this->at(1))
            ->method('makeRestCall')
            ->with('VosaCase', 'GET', array('id' => $caseId))
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
            'addedVia' => 'Case',
            'conditionType' => $routeParams['type'],
            'vosaCase' => ['id' => $caseId],
            'licence' => ['id' => $licenceId],
            'isDraft' => 1,
            'attachedTo' => 21,
            'operatingCentre' => ['iddoesntexist' => 21]
        ];

        $bundle = $this->controller->getConditionUndertakingBundle();

        $this->controller->expects($this->at(4))
            ->method('makeRestCall')
            ->with(
                $this->equalTo('ConditionUndertaking'),
                $this->equalTo('GET'),
                $this->equalTo(array('id' => $conditionId, 'bundle' => json_encode($bundle)))
            )
            ->will($this->returnValue($mockResults));

        $form = $this->getMock('\stdClass', array('setData'));

        $data['condition-undertaking'] = array(
            'addedVia' => 'Case',
            'conditionType' => $routeParams['type'],
            'isDraft' => 1,
            'vosaCase' => $routeParams['case'],
            'licence' => $routeParams['licence'],
            'attachedTo' => '',
            'operatingCentre' => ['iddoesntexist' => 21]
        );

        $this->controller->expects($this->once())
            ->method('generateFormWithData')
            ->with('condition-undertaking-form', 'processConditionUndertaking', $data, true)
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
                        'country' => 'UK',
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

        $type = 'condition';
        $routeParams = ['case' => $caseId, 'licence' => $licenceId, 'type' => $type, 'id' => $conditionId];

        $this->controller->expects($this->at(0))
            ->method('getParams')
            ->with(array('case', 'licence', 'type', 'id'))
            ->will($this->returnValue($routeParams));

        $this->controller->expects($this->at(1))
            ->method('makeRestCall')
            ->with('VosaCase', 'GET', array('id' => $caseId))
            ->will($this->returnValue(null));

        $this->controller->editAction();

    }

    public function testEditConditionActionCancelled()
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
            ->with('VosaCase', 'GET', array('id' => $caseId))
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
                        'country' => 'UK',
                     ]
                 ]
             ]
        ];

        $operatingCentreAddressBundle = $this->controller->getOCAddressBundle();

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with(
                'OperatingCentre', 'GET', array(
                'licence' => $licenceId,
                'bundle' => json_encode($operatingCentreAddressBundle)
                )
            )
            ->will($this->returnValue($mockOcAddressResults));

        $this->controller->getOCAddressByLicence($licenceId);
    }

    public function testConfigureFormForConditionType()
    {
        $this->controller = $this->getMock(
            '\Olcs\Controller\ConditionUndertakingController',
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
        $type = 'condition';

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
                        'country' => 'UK',
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
            ->with($addData, 'ConditionUndertaking')
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
            ->with($addData, 'ConditionUndertaking')
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
            ->with($editData, 'ConditionUndertaking')
            ->will($this->returnValue(['id' => 1]));

        $this->controller->processConditionUndertaking($functionData);
    }
}
