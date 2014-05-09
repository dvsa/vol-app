<?php

/**
 * Test vehicleControllerTest
 */

namespace OlcsTest\Controller;

use PHPUnit_Framework_TestCase;

/**
 * Test vehicleControllerTest
 */
class VehicleControllerTest extends PHPUnit_Framework_TestCase
{

    protected function setUpMockController($methods)
    {
        $this->controller = $this->getMock(
            '\SelfServe\Controller\VehicleSafety\VehicleController', $methods
        );
    }

    public function testIndexAction()
    {
        $applicationId = 2;

        $application = array(
            'licence' => array(
                'id' => 3
            )
        );

        $this->setUpMockController(
            array(
                'checkForCrudAction',
                'getApplicationId',
                'makeRestCall',
                'generateVehicleTable',
                'getViewModel',
                'renderLayoutWithSubSections'
            )
        );

        $this->controller->expects($this->any())
            ->method('getApplicationId')
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with('Application', 'GET', array('id' => $applicationId))
            ->will($this->returnValue($application));

        $this->controller->expects($this->once())
            ->method('generateVehicleTable')
            ->with($application['licence'])
            ->will($this->returnValue('<table></table>'));

        $mockView = $this->getMock('\stdClass', array('setTemplate'));

        $mockView->expects($this->once())
            ->method('setTemplate');

        $this->controller->expects($this->once())
            ->method('getViewModel')
            ->will($this->returnValue($mockView));

        $this->controller->expects($this->once())
            ->method('renderLayoutWithSubSections')
            ->with($mockView)
            ->will($this->returnValue('RENDER'));

        $this->assertEquals('RENDER', $this->controller->indexAction());

    }

    public function testAddActionWithCancel()
    {
        $this->setUpMockController(array('isButtonPressed', 'redirectToVehicles'));

        $this->controller->expects($this->once())
            ->method('isButtonPressed')
            ->with('cancel')
            ->will($this->returnValue(true));

        $this->controller->expects($this->once())
            ->method('redirectToVehicles')
            ->will($this->returnValue('REDIRECT'));

        $this->assertEquals('REDIRECT', $this->controller->addAction());
    }

    public function testAddAction()
    {
        $this->setUpMockController(
            array(
                'generateForm',
                'renderLayoutWithSubSections'
            )
        );

        $mockDataFieldset = $this->getMock('\stdClass', array('setLabel'));

        $mockDataFieldset->expects($this->once())
            ->method('setLabel')
            ->with('Add vehicle');

        $mockForm = $this->getMock('\stdClass', array('get'));

        $mockForm->expects($this->once())
            ->method('get')
            ->with('data')
            ->will($this->returnValue($mockDataFieldset));

        $this->controller->expects($this->once())
            ->method('generateForm')
            ->with($this->equalTo('vehicle'), $this->equalTo('processGoodsVehicleForm'))
            ->will($this->returnValue($mockForm));

        $this->controller->expects($this->once())
            ->method('renderLayoutWithSubSections')
            ->will($this->returnValue('LAYOUT'));

        $this->assertEquals('LAYOUT', $this->controller->addAction());
    }

    public function testEditActionWithValidVehicle()
    {
        $this->setUpMockController(
            [
                'getFromRoute',
                'makeRestCall',
                'notFoundAction',
                'generateFormWithData',
                'renderLayoutWithSubSections'
            ]
        );

        $vehicleId = 1;
        $restData = array(
            'id' => $vehicleId,
        );
        $vehicleResult = array(
            'id' => 1,
            'version' => 1,
            'vrm' => 'VRM1',
            'platedWeight' => 1000,
            'bodyType' => 'vehicleBodyType'
        );

        $mockDataFieldset = $this->getMock('\stdClass', array('setLabel'));

        $mockDataFieldset->expects($this->once())
            ->method('setLabel')
            ->with('Edit vehicle');

        $mockFormActions = $this->getMock('\stdClass', array('remove'));

        $mockFormActions->expects($this->once())
            ->method('remove')
            ->with('addAnother');

        $mockForm = $this->getMock('\stdClass', array('get'));

        $mockForm->expects($this->at(0))
            ->method('get')
            ->with('data')
            ->will($this->returnValue($mockDataFieldset));

        $mockForm->expects($this->at(1))
            ->method('get')
            ->with('form-actions')
            ->will($this->returnValue($mockFormActions));

        $vehicleData = array(
            'data' => array(
                'id' => $vehicleResult['id'],
                'version' => $vehicleResult['version'],
                'vrm' => $vehicleResult['vrm'],
                'plated_weight' => $vehicleResult['platedWeight'],
                'body_type' => $vehicleResult['bodyType']
            )
        );

        $this->controller->expects($this->once())
            ->method('getFromRoute')
            ->with($this->equalTo('id'))
            ->will($this->returnValue($vehicleId));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with($this->equalTo('Vehicle'), $this->equalTo('GET'), $this->equalTo($restData))
            ->will($this->returnValue($vehicleResult));

        $this->controller->expects($this->once())
            ->method('generateFormWithData')
            ->with(
                $this->equalTo('vehicle'),
                $this->equalTo('processGoodsVehicleForm'),
                $this->equalTo($vehicleData)
            )
            ->will($this->returnValue($mockForm));

        $this->controller->expects($this->once())
            ->method('renderLayoutWithSubSections')
            ->will($this->returnValue('LAYOUT'));

        $this->assertEquals('LAYOUT', $this->controller->editAction());
    }

    public function testEditActionWithInvalidVehicle()
    {
        $this->setUpMockController(
            [
                'generateForm',
                'getFromRoute',
                'makeRestCall',
                'notFoundAction',
                'generateFormWithData',
                'getViewModel'
            ]
        );

        $vehicleId = 1;
        $restData = array(
            'id' => $vehicleId,
        );
        $vehicleResult = null;

        $this->controller->expects($this->once())
            ->method('getFromRoute')
            ->with($this->equalTo('id'))
            ->will($this->returnValue($vehicleId));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with($this->equalTo('Vehicle'), $this->equalTo('GET'), $this->equalTo($restData))
            ->will($this->returnValue($vehicleResult));

        $this->controller->expects($this->once())
            ->method('notFoundAction');

        $this->controller->editAction();
    }

    /**
     * Test deleteAction with missing licence vehicle
     */
    public function testDeleteActionWithMissingLicenceVehicle()
    {
        $applicationId = 2;

        $application = array(
            'licence' => array(
                'id' => 3
            )
        );

        $vehicleId = 2;

        $licenceVehicle = array(

        );

        $this->setUpMockController(array('getFromRoute', 'getApplicationId', 'makeRestCall', 'notFoundAction'));

        $this->controller->expects($this->once())
            ->method('getFromRoute')
            ->with('id')
            ->will($this->returnValue($vehicleId));

        $this->controller->expects($this->any())
            ->method('getApplicationId')
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->at(2))
            ->method('makeRestCall')
            ->with('Application', 'GET', array('id' => $applicationId))
            ->will($this->returnValue($application));

        $this->controller->expects($this->at(3))
            ->method('makeRestCall')
            ->with('LicenceVehicle', 'GET')
            ->will($this->returnValue($licenceVehicle));

        $this->controller->expects($this->once())
            ->method('notFoundAction')
            ->will($this->returnValue(404));

        $this->assertEquals(404, $this->controller->deleteAction());
    }

    /**
     * Test deleteAction
     */
    public function testDeleteAction()
    {
        $applicationId = 2;

        $application = array(
            'licence' => array(
                'id' => 3
            )
        );

        $vehicleId = 2;

        $licenceVehicle = array(
            'Count' => 1,
            'Results' => array(
                array(
                    'id' => 3
                )
            )
        );

        $this->setUpMockController(array('getFromRoute', 'getApplicationId', 'makeRestCall', 'redirectToVehicles'));

        $this->controller->expects($this->once())
            ->method('getFromRoute')
            ->with('id')
            ->will($this->returnValue($vehicleId));

        $this->controller->expects($this->any())
            ->method('getApplicationId')
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->at(2))
            ->method('makeRestCall')
            ->with('Application', 'GET', array('id' => $applicationId))
            ->will($this->returnValue($application));

        $this->controller->expects($this->at(3))
            ->method('makeRestCall')
            ->with('LicenceVehicle', 'GET')
            ->will($this->returnValue($licenceVehicle));

        $this->controller->expects($this->at(4))
            ->method('makeRestCall')
            ->with('LicenceVehicle', 'DELETE');

        $this->controller->expects($this->once())
            ->method('redirectToVehicles')
            ->will($this->returnValue('REDIRECT'));

        $this->assertEquals('REDIRECT', $this->controller->deleteAction());
    }

    /**
     * Test processGoodsVehicleForm without ID With Add Another
     */
    public function testProcessGoodsVehicleFormWithoutIdWithAddAnother()
    {
        $validData = array(
            'data' => array(
                'id' => '',
                'version' => 1,
                'vrm' => 'AB23CF',
                'plated_weight' => 300
            )
        );

        $application = array(
            'licence' => array(
                'id' => 3
            )
        );

        $vehicle = array(
            'id' => 3
        );

        $applicationId = 4;

        $mockForm = $this->getMock('\Zend\Form\Form', array());

        $this->setUpMockController(array('makeRestCall', 'getApplicationId', 'isButtonPressed', 'redirectToRoute'));

        $this->controller->expects($this->at(0))
            ->method('makeRestCall')
            ->with('Vehicle', 'POST')
            ->will($this->returnValue($vehicle));

        $this->controller->expects($this->any())
            ->method('getApplicationId')
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->at(2))
            ->method('makeRestCall')
            ->with('Application', 'GET')
            ->will($this->returnValue($application));

        $this->controller->expects($this->at(3))
            ->method('makeRestCall')
            ->with('LicenceVehicle', 'POST');

        $this->controller->expects($this->once())
            ->method('isButtonPressed')
            ->with('addAnother')
            ->will($this->returnValue(true));

        $this->controller->expects($this->once())
            ->method('redirectToRoute');

        $this->assertEquals($mockForm, $this->controller->processGoodsVehicleForm($validData, $mockForm));
    }

    /**
     * Test processGoodsVehicleForm without ID
     */
    public function testProcessGoodsVehicleFormWithoutId()
    {
        $validData = array(
            'data' => array(
                'id' => '',
                'version' => 1,
                'vrm' => 'AB23CF',
                'plated_weight' => 300
            )
        );

        $application = array(
            'licence' => array(
                'id' => 3
            )
        );

        $vehicle = array(
            'id' => 3
        );

        $applicationId = 4;

        $mockForm = $this->getMock('\Zend\Form\Form', array());

        $this->setUpMockController(array('makeRestCall', 'getApplicationId', 'isButtonPressed', 'redirectToVehicles'));

        $this->controller->expects($this->at(0))
            ->method('makeRestCall')
            ->with('Vehicle', 'POST')
            ->will($this->returnValue($vehicle));

        $this->controller->expects($this->any())
            ->method('getApplicationId')
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->at(2))
            ->method('makeRestCall')
            ->with('Application', 'GET')
            ->will($this->returnValue($application));

        $this->controller->expects($this->at(3))
            ->method('makeRestCall')
            ->with('LicenceVehicle', 'POST');

        $this->controller->expects($this->once())
            ->method('isButtonPressed')
            ->with('addAnother')
            ->will($this->returnValue(false));

        $this->controller->expects($this->once())
            ->method('redirectToVehicles');

        $this->assertEquals($mockForm, $this->controller->processGoodsVehicleForm($validData, $mockForm));
    }

    /**
     * Test processGoodsVehicleForm
     */
    public function testProcessGoodsVehicleForm()
    {
        $validData = array(
            'data' => array(
                'id' => '1',
                'version' => 1,
                'vrm' => 'AB23CF',
                'plated_weight' => 300
            )
        );

        $application = array(
            'licence' => array(
                'id' => 3
            )
        );

        $vehicle = array(
            'id' => 3
        );

        $applicationId = 4;

        $mockForm = $this->getMock('\Zend\Form\Form', array());

        $this->setUpMockController(array('makeRestCall', 'getApplicationId', 'isButtonPressed', 'redirectToVehicles'));

        $this->controller->expects($this->at(0))
            ->method('makeRestCall')
            ->with('Vehicle', 'PUT')
            ->will($this->returnValue($vehicle));

        $this->controller->expects($this->any())
            ->method('getApplicationId')
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->once())
            ->method('isButtonPressed')
            ->with('addAnother')
            ->will($this->returnValue(false));

        $this->controller->expects($this->once())
            ->method('redirectToVehicles');

        $this->assertEquals($mockForm, $this->controller->processGoodsVehicleForm($validData, $mockForm));
    }

    /**
     * Test generateVehicleTable
     */
    public function testGenerateVehicleTable()
    {
        $licence = array(
            'id' => 1
        );

        $results = array(
            'licenceVehicles' => array(
                array(
                    'vehicle' => array(
                        'id' => 1
                    )
                )
            )
        );

        $this->setUpMockController(array('makeRestCall', 'buildTable'));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with('Licence', 'GET', array('id' => $licence['id']))
            ->will($this->returnValue($results));

        $this->controller->expects($this->once())
            ->method('buildTable')
            ->with('vehicle')
            ->will($this->returnValue('<table></table>'));

        $this->assertEquals('<table></table>', $this->controller->generateVehicleTable($licence));
    }
}
