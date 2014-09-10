<?php

/**
 * Vehicle Controller Test
 */
namespace OlcsTest\Controller\Licence\Details;

/**
 * Vehicle Controller Test
 */
class VehicleControllerTest extends AbstractLicenceDetailsControllerTestCase
{
    protected $controllerName = 'Olcs\Controller\Licence\Details\VehicleController';
    protected $routeName = 'licence/details/vehicle';

    protected $otherLicencesBundle = array(
        'properties' => array(),
        'children' => array(
            'licenceVehicles' => array(
                'properties' => array(),
                'children' => array(
                    'licence' => array(
                        'properties' => array(
                            'id',
                            'licNo'
                        ),
                        'children' => array(
                            'applications' => array(
                                'properties' => array(
                                    'id'
                                )
                            )
                        )
                    )
                )
            )
        )
    );

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
     * Test indexAction with crud action
     */
    public function testIndexActionWithEditCrudAction()
    {
        $this->setUpAction('index', null, array('action' => 'Edit', 'id' => 1));

        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexAction with crud action
     */
    public function testIndexActionWithAddCrudActionWithTooManyVehicles()
    {
        $this->setUpAction('index', null, array('action' => 'Add'));

        $bundle = array(
            'properties' => array(
                'totAuthVehicles'
            )
        );

        $response = array(
            'totAuthVehicles' => 1
        );

        $this->setRestResponse('Licence', 'GET', $response, $bundle);

        $totalNumberOfVehiclesResponse = array(
            'licenceVehicles' => array(
                array(
                    'id' => 1
                )
            )
        );

        $totalNumberOfVehiclesBundle = array(
            'properties' => array(),
            'children' => array(
                'licenceVehicles' => array(
                    'properties' => array('id')
                )
            )
        );

        $this->setRestResponse('Licence', 'GET', $totalNumberOfVehiclesResponse, $totalNumberOfVehiclesBundle);

        $response = $this->controller->indexAction();

        $flashMessenger = $this->controller->plugin('FlashMessenger');

        $this->assertEquals(1, count($flashMessenger->getCurrentMessagesFromNamespace('error')));

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexAction with crud action
     */
    public function testIndexActionWithAddCrudActionWithNotEnoughVehicles()
    {
        $this->setUpAction('index', null, array('action' => 'Add'));

        $bundle = array(
            'properties' => array(
                'totAuthVehicles'
            )
        );

        $response = array(
            'totAuthVehicles' => 2
        );

        $this->setRestResponse('Licence', 'GET', $response, $bundle);

        $totalNumberOfVehiclesResponse = array(
            'licenceVehicles' => array(
                array(
                    'id' => 1
                )
            )
        );

        $totalNumberOfVehiclesBundle = array(
            'properties' => array(),
            'children' => array(
                'licenceVehicles' => array(
                    'properties' => array('id')
                )
            )
        );

        $this->setRestResponse('Licence', 'GET', $totalNumberOfVehiclesResponse, $totalNumberOfVehiclesBundle);

        $response = $this->controller->indexAction();

        $flashMessenger = $this->controller->plugin('FlashMessenger');

        $this->assertEquals(0, count($flashMessenger->getCurrentMessagesFromNamespace('error')));

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
     * Test addAction with submit
     */
    public function testAddActionWithSubmitWithVehicleOnAnotherLicence()
    {
        $this->setUpAction(
            'add',
            null,
            array(
                'data' => array(
                    'id' => '',
                    'version' => '',
                    'vrm' => 'AB12',
                    'platedWeight' => 100
                )
            )
        );

        $response = array(
            'Count' => 2,
            'Results' => array(
                array(
                    'licenceVehicles' => array(
                        array(
                            'licence' => array(
                                'id' => 20,
                                'licNo' => 'AB123'
                            )
                        )
                    )
                ),
                array(
                    'licenceVehicles' => array(
                        array(
                            'licence' => array(
                                'id' => 21,
                                'licNo' => '',
                                'applications' => array(
                                    array(
                                        'id' => 123
                                    )
                                )
                            )
                        )
                    )
                )
            )
        );

        $this->setRestResponse('Vehicle', 'GET', $response, $this->otherLicencesBundle);

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->addAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test addAction with submit
     */
    public function testAddActionWithSubmitWithVehicleOnAnotherLicenceWithConfirm()
    {
        $this->setUpAction(
            'add',
            null,
            array(
                'data' => array(
                    'id' => '',
                    'version' => '',
                    'vrm' => 'AB12',
                    'platedWeight' => 100
                ),
                'licence-vehicle' => array(
                    'confirm-add' => 'y',
                    'receivedDate' => array('day' => '01', 'month' => '01', 'year' => '2014')
                )
            )
        );

        $response = array(
            'Count' => 2,
            'Results' => array(
                array(
                    'licenceVehicles' => array(
                        array(
                            'licence' => array(
                                'id' => 20,
                                'licNo' => 'AB123'
                            )
                        )
                    )
                ),
                array(
                    'licenceVehicles' => array(
                        array(
                            'licence' => array(
                                'id' => 21,
                                'licNo' => '',
                                'applications' => array(
                                    array(
                                        'id' => 123
                                    )
                                )
                            )
                        )
                    )
                )
            )
        );

        $this->setRestResponse('Vehicle', 'GET', $response, $this->otherLicencesBundle);

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->addAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
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
        if ($service == 'Application' && $method == 'GET'
            && $bundle == ApplicationController::$applicationLicenceDataBundle) {

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

        if ($service == 'Vehicle' && $method == 'GET' && $bundle == $this->otherLicencesBundle) {
            return array(
                'Count' => 0,
                'Results' => array(
                )
            );
        }

        $tableDataBundle = array(
            'properties' => null,
            'children' => array(
                'licenceVehicles' => array(
                    'properties' => array(
                        'id',
                        'receivedDate',
                        'specifiedDate',
                        'deletedDate'
                    ),
                    'children' => array(
                        'goodsDiscs' => array(
                            'ceasedDate',
                            'discNo'
                        ),
                        'vehicle' => array(
                            'properties' => array(
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
                        'id' => 1,
                        'receivedDate' => null,
                        'specifiedDate' => null,
                        'deletedDate' => null,
                        'goodsDisc' => array(
                            array(
                                'ceasedDate' => null,
                                'discNo' => 123
                            )
                        ),
                        'vehicle' => array(
                            'vrm' => 'AB12 ABG',
                            'platedWeight' => 100
                        )
                    ),
                    array(
                        'id' => 2,
                        'receivedDate' => null,
                        'specifiedDate' => null,
                        'deletedDate' => null,
                        'goodsDisc' => array(
                            array(
                                'ceasedDate' => null,
                                'discNo' => 1234
                            )
                        ),
                        'vehicle' => array(
                            'vrm' => 'DB12 ABG',
                            'platedWeight' => 150
                        )
                    )
                )
            );
        }

        $actionDataBundle = array(
            'properties' => array(
                'id',
                'version',
                'receivedDate',
                'deletedDate',
                'specifiedDate'
            ),
            'children' => array(
                'goodsDiscs' => array(
                    'properties' => array(
                        'discNo'
                    )
                ),
                'vehicle' => array(
                    'properties' => array(
                        'id',
                        'version',
                        'platedWeight',
                        'vrm'
                    )
                )
            )
        );

        if ($service == 'LicenceVehicle' && $method == 'GET' && $bundle == $actionDataBundle) {
            return array(
                'id' => 1,
                'version' => 1,
                'receivedDate' => null,
                'deletedDate' => null,
                'specifiedDate' => null,
                'goodsDiscs' => array(
                    array(
                        'discNo' => 123
                    )
                ),
                'vehicle' => array(
                    'id' => 1,
                    'version' => 1,
                    'platedWeight' => 100,
                    'vrm' => 'AB12 ABC'
                )
            );
        }

        $licenceVehicleBundle = array(
            'properties' => array(),
            'children' => array(
                'vehicle' => array(
                    'properties' => array('vrm')
                )
            )
        );

        if ($service == 'LicenceVehicle' && $method == 'GET' && $bundle = $licenceVehicleBundle) {
            return array(
                'Count' => 1,
                'Results' => array(
                    array(
                        'vehicle' => array(
                            'vrm' => 'RANDOM'
                        )
                    )
                )
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
