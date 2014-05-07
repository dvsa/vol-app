<?php

/**
 * Test WorkshopController
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Controller;

use PHPUnit_Framework_TestCase;

/**
 * Test WorkshopController
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class WorkshopControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Build a mock controller
     *
     * @param array $methods
     */
    protected function getMockController($methods = array())
    {
        $this->controller = $this->getMock(
            'SelfServe\Controller\VehicleSafety\WorkshopController', $methods
        );
    }

    /**
     * Test addAction With Cancel Pressed
     */
    public function testAddActionWithCancelPressed()
    {
        $this->getMockController(array('isButtonPressed', 'backToSafety'));

        $this->controller->expects($this->once())
            ->method('isButtonPressed')
            ->with('cancel')
            ->will($this->returnValue(true));

        $this->controller->expects($this->once())
            ->method('backToSafety')
            ->will($this->returnValue('SAFETY'));

        $this->assertEquals('SAFETY', $this->controller->addAction());
    }

    /**
     * Test addAction
     */
    public function testAddAction()
    {
        $applicationId = 3;

        $this->getMockController(
            array('isButtonPressed', 'getApplicationId', 'generateFormWithData', 'getViewModel', 'renderLayoutWithSubSections')
        );

        $this->controller->expects($this->once())
            ->method('isButtonPressed')
            ->with('cancel')
            ->will($this->returnValue(false));

        $this->controller->expects($this->once())
            ->method('getApplicationId')
            ->will($this->returnValue($applicationId));

        $mockDataFieldset = $this->getMock('\stdClass', array('setLabel'));

        $mockDataFieldset->expects($this->once())
            ->method('setLabel')
            ->with('Add safety inspection provider');

        $mockForm = $this->getMock('\stdClass', array('get'));

        $mockForm->expects($this->once())
            ->method('get')
            ->with('data')
            ->will($this->returnValue($mockDataFieldset));

        $this->controller->expects($this->once())
            ->method('generateFormWithData')
            ->with('vehicle-safety-workshop')
            ->will($this->returnValue($mockForm));

        $mockView = $this->getMock('\stdClass', array('setTemplate'));

        $mockView->expects($this->once())
            ->method('setTemplate');

        $this->controller->expects($this->once())
            ->method('getViewModel')
            ->will($this->returnValue($mockView));

        $this->controller->expects($this->once())
            ->method('renderLayoutWithSubSections')
            ->with($mockView, 'safety')
            ->will($this->returnValue('RENDER'));

        $this->assertEquals('RENDER', $this->controller->addAction());
    }

    /**
     * Test editAction With Cancel Pressed
     */
    public function testEditActionWithCancelPressed()
    {
        $this->getMockController(array('isButtonPressed', 'backToSafety'));

        $this->controller->expects($this->once())
            ->method('isButtonPressed')
            ->with('cancel')
            ->will($this->returnValue(true));

        $this->controller->expects($this->once())
            ->method('backToSafety')
            ->will($this->returnValue('SAFETY'));

        $this->assertEquals('SAFETY', $this->controller->editAction());
    }

    /**
     * Test editAction
     */
    public function testEditAction()
    {
        $id = 4;

        $result = array(
            'id' => $id,
            'version' => 1,
            'isExternal' => true,
            'contactDetails' => array(
                'fao' => 'FAO',
                'id' => 3,
                'version' => 4,
                'address' => array(
                    'country' => 'country.1'
                )
            )
        );

        $this->getMockController(
            array('isButtonPressed', 'makeRestCall', 'getFromRoute', 'getApplicationId', 'generateFormWithData', 'getViewModel', 'renderLayoutWithSubSections')
        );

        $this->controller->expects($this->once())
            ->method('isButtonPressed')
            ->with('cancel')
            ->will($this->returnValue(false));

        $this->controller->expects($this->once())
            ->method('getFromRoute')
            ->will($this->returnValue($id));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with('Workshop', 'GET')
            ->will($this->returnValue($result));

        $mockDataFieldset = $this->getMock('\stdClass', array('setLabel'));

        $mockDataFieldset->expects($this->once())
            ->method('setLabel')
            ->with('Update safety inspection provider');

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

        $this->controller->expects($this->once())
            ->method('generateFormWithData')
            ->with('vehicle-safety-workshop')
            ->will($this->returnValue($mockForm));

        $mockView = $this->getMock('\stdClass', array('setTemplate'));

        $mockView->expects($this->once())
            ->method('setTemplate');

        $this->controller->expects($this->once())
            ->method('getViewModel')
            ->will($this->returnValue($mockView));

        $this->controller->expects($this->once())
            ->method('renderLayoutWithSubSections')
            ->with($mockView, 'safety')
            ->will($this->returnValue('RENDER'));

        $this->assertEquals('RENDER', $this->controller->editAction());
    }

    /**
     * Test deleteAction
     */
    public function testDeleteAction()
    {
        $id = 4;

        $this->getMockController(array('getFromRoute', 'makeRestCall', 'backToSafety'));

        $this->controller->expects($this->once())
            ->method('getFromRoute')
            ->will($this->returnValue($id));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with('Workshop', 'DELETE');

        $this->controller->expects($this->once())
            ->method('backToSafety')
            ->will($this->returnValue('SAFETY'));

        $this->assertEquals('SAFETY', $this->controller->deleteAction());
    }

    /**
     * Test processAddWorkshop With failed create
     */
    public function testProcessAddWorkshopWithFailedCreate()
    {
        $data = array(
            'data' => array(
                'applicationId' => 1,
                'isExternal' => true,
                'fao' => 'FAO'
            ),
            'address' => array(
                'country' => 'country.1'
            )
        );

        $contactDetails = array(
            'id' => 1
        );

        $workshop = array(

        );

        $this->getMockController(array('makeRestCall'));

        $this->controller->expects($this->at(0))
            ->method('makeRestCall')
            ->with('ContactDetails', 'POST')
            ->will($this->returnValue($contactDetails));

        $this->controller->expects($this->at(1))
            ->method('makeRestCall')
            ->with('Workshop', 'POST')
            ->will($this->returnValue($workshop));

        $this->controller->processAddWorkshop($data);
    }

    /**
     * Test processAddWorkshop With Add Another
     */
    public function testProcessAddWorkshopWithAddAnother()
    {
        $data = array(
            'data' => array(
                'applicationId' => 1,
                'isExternal' => true,
                'fao' => 'FAO'
            ),
            'address' => array(
                'country' => 'country.1'
            )
        );

        $contactDetails = array(
            'id' => 1
        );

        $workshop = array(
            'id' => 2
        );

        $this->getMockController(array('makeRestCall', 'isButtonPressed', 'redirectToRoute'));

        $this->controller->expects($this->at(0))
            ->method('makeRestCall')
            ->with('ContactDetails', 'POST')
            ->will($this->returnValue($contactDetails));

        $this->controller->expects($this->at(1))
            ->method('makeRestCall')
            ->with('Workshop', 'POST')
            ->will($this->returnValue($workshop));

        $this->controller->expects($this->once())
            ->method('isButtonPressed')
            ->with('addAnother')
            ->will($this->returnValue(true));

        $this->controller->expects($this->once())
            ->method('redirectToRoute')
            ->with(null)
            ->will($this->returnValue('REDIRECT'));

        $this->assertEquals('REDIRECT', $this->controller->processAddWorkshop($data));
    }

    /**
     * Test processAddWorkshop
     */
    public function testProcessAddWorkshop()
    {
        $data = array(
            'data' => array(
                'applicationId' => 1,
                'isExternal' => true,
                'fao' => 'FAO'
            ),
            'address' => array(
                'country' => 'country.1'
            )
        );

        $contactDetails = array(
            'id' => 1
        );

        $workshop = array(
            'id' => 2
        );

        $this->getMockController(array('makeRestCall', 'isButtonPressed', 'backToSafety'));

        $this->controller->expects($this->at(0))
            ->method('makeRestCall')
            ->with('ContactDetails', 'POST')
            ->will($this->returnValue($contactDetails));

        $this->controller->expects($this->at(1))
            ->method('makeRestCall')
            ->with('Workshop', 'POST')
            ->will($this->returnValue($workshop));

        $this->controller->expects($this->once())
            ->method('isButtonPressed')
            ->with('addAnother')
            ->will($this->returnValue(false));

        $this->controller->expects($this->once())
            ->method('backToSafety')
            ->will($this->returnValue('SAFETY'));

        $this->assertEquals('SAFETY', $this->controller->processAddWorkshop($data));
    }

    /**
     * Test processEditWorkshop
     */
    public function testProcessEditWorkshop()
    {
        $data = array(
            'data' => array(
                'id' => 3,
                'version' => 2,
                'contactDetails.id' => 2,
                'contactDetails.version' => 3,
                'applicationId' => 1,
                'isExternal' => true,
                'fao' => 'FAO'
            ),
            'address' => array(
                'country' => 'country.1'
            )
        );

        $this->getMockController(array('makeRestCall', 'backToSafety'));

        $this->controller->expects($this->at(0))
            ->method('makeRestCall')
            ->with('ContactDetails', 'PUT');

        $this->controller->expects($this->at(1))
            ->method('makeRestCall')
            ->with('Workshop', 'PUT');

        $this->controller->expects($this->once())
            ->method('backToSafety')
            ->will($this->returnValue('SAFETY'));

        $this->assertEquals('SAFETY', $this->controller->processEditWorkshop($data));
    }
}
