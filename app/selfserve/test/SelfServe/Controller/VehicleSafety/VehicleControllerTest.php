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
                'params',
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

        $mockParams = $this->getMock('\StdClass', ['fromRoute']);
        $mockParams->expects($this->once())
            ->method('fromRoute')
            ->with($this->equalTo('vehicleId'))
            ->will($this->returnValue($vehicleId));

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($mockParams));

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
                'params',
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

        $mockParams = $this->getMock('\StdClass', ['fromRoute']);
        $mockParams->expects($this->once())
            ->method('fromRoute')
            ->with($this->equalTo('vehicleId'))
            ->will($this->returnValue($vehicleId));

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with($this->equalTo('Vehicle'), $this->equalTo('GET'), $this->equalTo($restData))
            ->will($this->returnValue($vehicleResult));

        $this->controller->expects($this->once())
            ->method('notFoundAction');

        $this->controller->editAction();
    }
}
