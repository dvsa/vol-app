<?php

/**
 * Vehicle Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace OlcsTest\Controller\Application\VehicleSafety;

use OlcsTest\Controller\Application\AbstractApplicationControllerTestCase;
use Common\Controller\Application\ApplicationController;

/**
 * Vehicle Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehicleControllerTest extends AbstractApplicationControllerTestCase
{

    protected $controllerName = '\Common\Controller\Application\VehicleSafety\VehicleController';
    protected $defaultRestResponse = array();

    /**
     * Test back button
     */
    public function testBackButton()
    {
        $this->setUpAction('index', null, array('form-actions' => array('back' => 'Back')));

        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexAction
     */
    public function testIndexAction()
    {
        $this->setUpAction('index');

        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test indexAction with crud action
     */
    public function testIndexActionWithCrudAction()
    {
        $this->setUpAction('index', null, array('action' => 'Add'));

        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexAction with submit
     */
    public function testIndexActionWithSubmit()
    {
        $this->setUpAction('index', null, array('foo' => 'bar'));

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test addAction
     */
    public function testAddAction()
    {
        $this->setUpAction('add');

        $response = $this->controller->addAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test addAction with cancel
     */
    public function testAddActionWithCancel()
    {
        $post = array(
            'form-actions' => array(
                'cancel' => 'Cancel'
            )
        );

        $this->setUpAction('add', null, $post);

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->addAction();

        $this->assertInstanceOf('\Zend\Http\Response', $response);
    }

    /**
     * Test editAction with cancel
     */
    public function testEditActionWithCancel()
    {
        $post = array(
            'form-actions' => array(
                'cancel' => 'Cancel'
            )
        );

        $this->setUpAction('edit', 1, $post);

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->editAction();

        $this->assertInstanceOf('\Zend\Http\Response', $response);
    }

    /**
     * Test addAction with submit
     */
    public function testAddActionWithSubmit()
    {
        $this->setUpAction(
            'add',
            null,
            array(
                'data' => array(
                    'id' => '',
                    'version' => '',
                    'vrm' => 'AB12 CVB',
                    'platedWeight' => 100
                )
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->addAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test addAction with submit with addAnother
     */
    public function testAddActionWithSubmitWithAddAnother()
    {
        $this->setUpAction(
            'add',
            null,
            array(
                'data' => array(
                    'id' => '',
                    'version' => '',
                    'vrm' => 'AB12 CVB',
                    'platedWeight' => 100
                ),
                'form-actions' => array(
                    'addAnother' => 'Add another'
                )
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->addAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test addAction with submit with failure
     *
     * @expectedException \Exception
     */
    public function testAddActionWithSubmitWithFailuer()
    {
        $this->setUpAction(
            'add',
            null,
            array(
                'data' => array(
                    'id' => '',
                    'version' => '',
                    'vrm' => 'AB12 CVB',
                    'platedWeight' => 100
                )
            )
        );

        $this->setRestResponse('Vehicle', 'POST', '');

        $this->controller->setEnabledCsrf(false);
        $this->controller->addAction();
    }

    /**
     * Test editAction
     */
    public function testEditAction()
    {
        $this->setUpAction('edit', 1);

        $response = $this->controller->editAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test editAction with submit
     */
    public function testEditActionWithSubmit()
    {
        $this->setUpAction(
            'edit',
            1, array(
                'data' => array(
                    'id' => 1,
                    'version' => 1,
                    'vrm' => 'AB12 CVB',
                    'platedWeight' => 100
                )
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->editAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test deleteAction
     */
    public function testDeleteAction()
    {
        $this->setUpAction('delete', 1);

        $response = $this->controller->deleteAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test deleteAction without id
     */
    public function testDeleteActionWithoutId()
    {
        $this->setUpAction('delete');

        $this->setRestResponse('LicenceVehicle', 'GET', array('Count' => 0, 'Results' => array()));

        $response = $this->controller->deleteAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Mock the rest call
     *
     * @param string $service
     * @param string $method
     * @param array $data
     * @param array $bundle
     */
    protected function mockRestCalls($service, $method, $data = array(), $bundle = array())
    {
        if ($service == 'Application' && $method == 'GET' && $bundle == ApplicationController::$licenceDataBundle) {

            return $this->getLicenceData('goods');
        }

        if ($service == 'ApplicationCompletion' && $method == 'GET') {

            return $this->getApplicationCompletionData();
        }

        if ($service == 'Vehicle' && $method == 'POST') {
            return array('id' => 1);
        }

        if ($service == 'LicenceVehicle' && $method == 'POST') {
            return array('id' => 1);
        }

        $tableDataBundle = array(
            'properties' => null,
            'children' => array(
                'licenceVehicles' => array(
                    'properties' => null,
                    'children' => array(
                        'vehicle' => array(
                            'properties' => array(
                                'id',
                                'vrm',
                                'platedWeight'
                            )
                        )
                    )
                )
            )
        );

        if ($service == 'Licence' && $method == 'GET' && $bundle == $tableDataBundle) {
            return array(
                'licenceVehicles' => array(
                    array(
                        'vehicle' => array(
                            'id' => 1,
                            'vrm' => 'AB12 ABG',
                            'platedWeight' => 100
                        )
                    ),
                    array(
                        'vehicle' => array(
                            'id' => 2,
                            'vrm' => 'DB12 ABG',
                            'platedWeight' => 150
                        )
                    )
                )
            );
        }

        if ($service == 'Vehicle' && $method == 'GET') {
            return array(
                'id' => 1,
                'version' => 1,
                'vrm' => 'AB12 ABC',
                'platedWeight' => 100
            );
        }

        if ($service == 'LicenceVehicle' && $method == 'GET') {
            return array(
                'Count' => 1,
                'Results' => array(
                    array('id' => 1)
                )
            );
        }
    }
}
