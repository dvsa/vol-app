<?php

/**
 * Vehicle Psv Controller Test
 */
namespace OlcsTest\Controller\Licence\Details;

/**
 * Vehicle Psv Controller Test
 */
class VehiclePsvControllerTest extends AbstractLicenceDetailsControllerTestCase
{
    protected $controllerName = 'Olcs\Controller\Licence\Details\VehiclePsvController';
    protected $routeName = 'licence/details/vehicle_psv';

    protected $largeVehicles = 5;

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

        $form = $this->getFormFromView($response);

        $this->assertTrue($form->has('large'));
    }

    /**
     * Test indexAction
     */
    public function testIndexActionWithRemovedTable()
    {
        $this->largeVehicles = 0;

        $this->setUpAction('index');

        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);

        $form = $this->getFormFromView($response);

        $this->assertFalse($form->has('large'));
    }

    /**
     * Test indexAction with crud action
     */
    public function testIndexActionWithLargeCrudAction()
    {
        $this->setUpAction('index', null, array('large' => array('action' => 'large-add')));

        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexAction with crud action
     */
    public function testIndexActionWithMediumCrudAction()
    {
        $this->setUpAction('index', null, array('medium' => array('action' => 'medium-add')));

        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexAction with crud action
     */
    public function testIndexActionWithSmallCrudAction()
    {
        $this->setUpAction('index', null, array('small' => array('action' => 'small-add')));

        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexAction with crud action
     */
    public function testIndexActionWithSmallAddCrudActionWithTooManyVehicles()
    {
        $this->setUpAction('index', null, array('action' => 'small-add'));

        $bundle = array(
            'properties' => array(
                'totAuthSmallVehicles'
            )
        );

        $response = array(
            'totAuthSmallVehicles' => 1
        );

        $this->setRestResponse('Licence', 'GET', $response, $bundle);

        $totalNumberOfVehiclesResponse = array(
            'licenceVehicles' => array(
                array(
                    'vehicle' => array(
                        'id' => 1,
                        'psvType' => array(
                            'id' => 'vhl_t_a'
                        )
                    )
                )
            )
        );

        $totalNumberOfVehiclesBundle = array(
            'properties' => array(),
            'children' => array(
                'licenceVehicles' => array(
                    'properties' => array(),
                    'children' => array(
                        'vehicle' => array(
                            'properties' => array(
                                'id'
                            ),
                            'children' => array(
                                'psvType' => array(
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

        $this->setRestResponse('Licence', 'GET', $totalNumberOfVehiclesResponse, $totalNumberOfVehiclesBundle);

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->indexAction();

        $flashMessenger = $this->controller->plugin('FlashMessenger');

        $this->assertEquals(1, count($flashMessenger->getCurrentMessagesFromNamespace('error')));

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexAction with crud action
     *
     * @group here
     */
    public function testIndexActionWithSmallAddCrudActionWithNotEnoughVehicles()
    {
        $this->setUpAction('index', null, array('action' => 'small-add'));

        $bundle = array(
            'properties' => array(
                'totAuthSmallVehicles'
            )
        );

        $response = array(
            'totAuthSmallVehicles' => 10
        );

        $this->setRestResponse('Licence', 'GET', $response, $bundle);

        $totalNumberOfVehiclesResponse = array(
            'licenceVehicles' => array(
                array(
                    'vehicle' => array(
                        'id' => 1,
                        'psvType' => array(
                            'id' => 'vhl_t_a'
                        )
                    )
                )
            )
        );

        $totalNumberOfVehiclesBundle = array(
            'properties' => array(),
            'children' => array(
                'licenceVehicles' => array(
                    'properties' => array(),
                    'children' => array(
                        'vehicle' => array(
                            'properties' => array(
                                'id'
                            ),
                            'children' => array(
                                'psvType' => array(
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

        $this->setRestResponse('Licence', 'GET', $totalNumberOfVehiclesResponse, $totalNumberOfVehiclesBundle);

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->indexAction();

        $flashMessenger = $this->controller->plugin('FlashMessenger');

        $this->assertEquals(0, count($flashMessenger->getCurrentMessagesFromNamespace('error')));

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexAction with crud action
     */
    public function testIndexActionWithMediumAddCrudActionWithTooManyVehicles()
    {
        $this->setUpAction('index', null, array('action' => 'medium-add'));

        $bundle = array(
            'properties' => array(
                'totAuthMediumVehicles'
            )
        );

        $response = array(
            'totAuthMediumVehicles' => 1
        );

        $this->setRestResponse('Licence', 'GET', $response, $bundle);

        $totalNumberOfVehiclesResponse = array(
            'licenceVehicles' => array(
                array(
                    'vehicle' => array(
                        'id' => 1,
                        'psvType' => array(
                            'id' => 'vhl_t_b'
                        )
                    )
                )
            )
        );

        $totalNumberOfVehiclesBundle = array(
            'properties' => array(),
            'children' => array(
                'licenceVehicles' => array(
                    'properties' => array(),
                    'children' => array(
                        'vehicle' => array(
                            'properties' => array(
                                'id'
                            ),
                            'children' => array(
                                'psvType' => array(
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

        $this->setRestResponse('Licence', 'GET', $totalNumberOfVehiclesResponse, $totalNumberOfVehiclesBundle);

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->indexAction();

        $flashMessenger = $this->controller->plugin('FlashMessenger');

        $this->assertEquals(1, count($flashMessenger->getCurrentMessagesFromNamespace('error')));

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexAction with crud action
     */
    public function testIndexActionWithMediumAddCrudActionWithNotEnoughVehicles()
    {
        $this->setUpAction('index', null, array('action' => 'medium-add'));

        $bundle = array(
            'properties' => array(
                'totAuthMediumVehicles'
            )
        );

        $response = array(
            'totAuthMediumVehicles' => 10
        );

        $this->setRestResponse('Licence', 'GET', $response, $bundle);

        $totalNumberOfVehiclesResponse = array(
            'licenceVehicles' => array(
                array(
                    'vehicle' => array(
                        'id' => 1,
                        'psvType' => array(
                            'id' => 'vhl_t_b'
                        )
                    )
                )
            )
        );

        $totalNumberOfVehiclesBundle = array(
            'properties' => array(),
            'children' => array(
                'licenceVehicles' => array(
                    'properties' => array(),
                    'children' => array(
                        'vehicle' => array(
                            'properties' => array(
                                'id'
                            ),
                            'children' => array(
                                'psvType' => array(
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

        $this->setRestResponse('Licence', 'GET', $totalNumberOfVehiclesResponse, $totalNumberOfVehiclesBundle);

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->indexAction();

        $flashMessenger = $this->controller->plugin('FlashMessenger');

        $this->assertEquals(0, count($flashMessenger->getCurrentMessagesFromNamespace('error')));

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexAction with crud action
     */
    public function testIndexActionWithLargeAddCrudActionWithTooManyVehicles()
    {
        $this->setUpAction('index', null, array('action' => 'large-add'));

        $bundle = array(
            'properties' => array(
                'totAuthLargeVehicles'
            )
        );

        $response = array(
            'totAuthLargeVehicles' => 1
        );

        $this->setRestResponse('Licence', 'GET', $response, $bundle);

        $totalNumberOfVehiclesResponse = array(
            'licenceVehicles' => array(
                array(
                    'vehicle' => array(
                        'id' => 1,
                        'psvType' => array(
                            'id' => 'vhl_t_c'
                        )
                    )
                )
            )
        );

        $totalNumberOfVehiclesBundle = array(
            'properties' => array(),
            'children' => array(
                'licenceVehicles' => array(
                    'properties' => array(),
                    'children' => array(
                        'vehicle' => array(
                            'properties' => array(
                                'id'
                            ),
                            'children' => array(
                                'psvType' => array(
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

        $this->setRestResponse('Licence', 'GET', $totalNumberOfVehiclesResponse, $totalNumberOfVehiclesBundle);

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->indexAction();

        $flashMessenger = $this->controller->plugin('FlashMessenger');

        $this->assertEquals(1, count($flashMessenger->getCurrentMessagesFromNamespace('error')));

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexAction with crud action
     */
    public function testIndexActionWithLargeAddCrudActionWithNotEnoughVehicles()
    {
        $this->setUpAction('index', null, array('action' => 'large-add'));

        $bundle = array(
            'properties' => array(
                'totAuthLargeVehicles'
            )
        );

        $response = array(
            'totAuthLargeVehicles' => 10
        );

        $this->setRestResponse('Licence', 'GET', $response, $bundle);

        $totalNumberOfVehiclesResponse = array(
            'licenceVehicles' => array(
                array(
                    'vehicle' => array(
                        'id' => 1,
                        'psvType' => array(
                            'id' => 'vhl_t_c'
                        )
                    )
                )
            )
        );

        $totalNumberOfVehiclesBundle = array(
            'properties' => array(),
            'children' => array(
                'licenceVehicles' => array(
                    'properties' => array(),
                    'children' => array(
                        'vehicle' => array(
                            'properties' => array(
                                'id'
                            ),
                            'children' => array(
                                'psvType' => array(
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

        $this->setRestResponse('Licence', 'GET', $totalNumberOfVehiclesResponse, $totalNumberOfVehiclesBundle);

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->indexAction();

        $flashMessenger = $this->controller->plugin('FlashMessenger');

        $this->assertEquals(0, count($flashMessenger->getCurrentMessagesFromNamespace('error')));

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test addAction
     */
    public function testLargeAddAction()
    {
        $this->setUpAction('large-add');

        $response = $this->controller->largeAddAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test addAction
     */
    public function testMediumAddAction()
    {
        $this->setUpAction('medium-add');

        $response = $this->controller->mediumAddAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test addAction
     */
    public function testSmallAddAction()
    {
        $this->setUpAction('small-add');

        $response = $this->controller->smallAddAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test addAction with cancel
     */
    public function testLargeAddActionWithCancel()
    {
        $post = array(
            'form-actions' => array(
                'cancel' => 'Cancel'
            )
        );

        $this->setUpAction('large-add', null, $post);

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->largeAddAction();

        $this->assertInstanceOf('\Zend\Http\Response', $response);
    }

    /**
     * Test addAction with cancel
     */
    public function testMediumAddActionWithCancel()
    {
        $post = array(
            'form-actions' => array(
                'cancel' => 'Cancel'
            )
        );

        $this->setUpAction('medium-add', null, $post);

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->mediumAddAction();

        $this->assertInstanceOf('\Zend\Http\Response', $response);
    }

    /**
     * Test addAction with cancel
     */
    public function testSmallAddActionWithCancel()
    {
        $post = array(
            'form-actions' => array(
                'cancel' => 'Cancel'
            )
        );

        $this->setUpAction('small-add', null, $post);

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->smallAddAction();

        $this->assertInstanceOf('\Zend\Http\Response', $response);
    }

    /**
     * Test editAction with cancel
     */
    public function testLargeEditActionWithCancel()
    {
        $post = array(
            'form-actions' => array(
                'cancel' => 'Cancel'
            )
        );

        $this->setUpAction('large-edit', 1, $post);

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->largeEditAction();

        $this->assertInstanceOf('\Zend\Http\Response', $response);
    }

    /**
     * Test editAction with cancel
     */
    public function testMediumEditActionWithCancel()
    {
        $post = array(
            'form-actions' => array(
                'cancel' => 'Cancel'
            )
        );

        $this->setUpAction('medium-edit', 1, $post);

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->mediumEditAction();

        $this->assertInstanceOf('\Zend\Http\Response', $response);
    }

    /**
     * Test editAction with cancel
     */
    public function testSmallEditActionWithCancel()
    {
        $post = array(
            'form-actions' => array(
                'cancel' => 'Cancel'
            )
        );

        $this->setUpAction('small-edit', 1, $post);

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->smallEditAction();

        $this->assertInstanceOf('\Zend\Http\Response', $response);
    }

    /**
     * Test addAction with submit
     */
    public function testLargeAddActionWithSubmit()
    {
        $this->setUpAction(
            'large-add',
            null,
            array(
                'data' => array(
                    'id' => '',
                    'version' => '',
                    'vrm' => 'AB12 CVB'
                )
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->largeAddAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test addAction with submit
     */
    public function testMediumAddActionWithSubmit()
    {
        $this->setUpAction(
            'medium-add',
            null,
            array(
                'data' => array(
                    'id' => '',
                    'version' => '',
                    'vrm' => 'AB12 CVB'
                )
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->mediumAddAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test addAction with submit
     */
    public function testSmallAddActionWithSubmit()
    {
        $this->setUpAction(
            'small-add',
            null,
            array(
                'data' => array(
                    'id' => '',
                    'version' => '',
                    'vrm' => 'AB12 CVB',
                    'isNovelty' => 'Y'
                )
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->smallAddAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test addAction with submit with addAnother
     */
    public function testLargeAddActionWithSubmitWithAddAnother()
    {
        $this->setUpAction(
            'large-add',
            null,
            array(
                'data' => array(
                    'id' => '',
                    'version' => '',
                    'vrm' => 'AB12 CVB'
                ),
                'form-actions' => array(
                    'addAnother' => 'Add another'
                )
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->largeAddAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test addAction with submit with addAnother
     */
    public function testMediumAddActionWithSubmitWithAddAnother()
    {
        $this->setUpAction(
            'medium-add',
            null,
            array(
                'data' => array(
                    'id' => '',
                    'version' => '',
                    'vrm' => 'AB12 CVB'
                ),
                'form-actions' => array(
                    'addAnother' => 'Add another'
                )
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->mediumAddAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test addAction with submit with addAnother
     */
    public function testSmallAddActionWithSubmitWithAddAnother()
    {
        $this->setUpAction(
            'small-add',
            null,
            array(
                'data' => array(
                    'id' => '',
                    'version' => '',
                    'vrm' => 'AB12 CVB',
                    'isNovelty' => 'Y'
                ),
                'form-actions' => array(
                    'addAnother' => 'Add another'
                )
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->smallAddAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test addAction with submit with failure
     *
     * @expectedException \Exception
     */
    public function testLargeAddActionWithSubmitWithFailure()
    {
        $this->setUpAction(
            'large-add',
            null,
            array(
                'data' => array(
                    'id' => '',
                    'version' => '',
                    'vrm' => 'AB12 CVB'
                )
            )
        );

        $this->setRestResponse('Vehicle', 'POST', '');

        $this->controller->setEnabledCsrf(false);
        $this->controller->largeAddAction();
    }

    /**
     * Test addAction with submit with failure
     *
     * @expectedException \Exception
     */
    public function testMediumAddActionWithSubmitWithFailure()
    {
        $this->setUpAction(
            'medium-add',
            null,
            array(
                'data' => array(
                    'id' => '',
                    'version' => '',
                    'vrm' => 'AB12 CVB'
                )
            )
        );

        $this->setRestResponse('Vehicle', 'POST', '');

        $this->controller->setEnabledCsrf(false);
        $this->controller->mediumAddAction();
    }

    /**
     * Test addAction with submit with failure
     *
     * @expectedException \Exception
     */
    public function testSmallAddActionWithSubmitWithFailure()
    {
        $this->setUpAction(
            'small-add',
            null,
            array(
                'data' => array(
                    'id' => '',
                    'version' => '',
                    'vrm' => 'AB12 CVB',
                    'isNovelty' => 'Y'
                )
            )
        );

        $this->setRestResponse('Vehicle', 'POST', '');

        $this->controller->setEnabledCsrf(false);
        $this->controller->smallAddAction();
    }

    /**
     * Test editAction
     */
    public function testLargeEditAction()
    {
        $this->setUpAction('large-edit', 1);

        $response = $this->controller->largeEditAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test editAction
     */
    public function testMediumEditAction()
    {
        $this->setUpAction('medium-edit', 1);

        $response = $this->controller->mediumEditAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test editAction
     */
    public function testSmallEditAction()
    {
        $this->setUpAction('small-edit', 1);

        $response = $this->controller->smallEditAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test editAction with submit
     */
    public function testLargeEditActionWithSubmit()
    {
        $this->setUpAction(
            'large-edit',
            1, array(
                'data' => array(
                    'id' => 1,
                    'version' => 1,
                    'vrm' => 'AB12 CVB'
                )
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->largeEditAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test editAction with submit
     */
    public function testMediumEditActionWithSubmit()
    {
        $this->setUpAction(
            'medium-edit',
            1, array(
                'data' => array(
                    'id' => 1,
                    'version' => 1,
                    'vrm' => 'AB12 CVB'
                )
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->mediumEditAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test editAction with submit
     */
    public function testSmallEditActionWithSubmit()
    {
        $this->setUpAction(
            'small-edit',
            1, array(
                'data' => array(
                    'id' => 1,
                    'version' => 1,
                    'vrm' => 'AB12 CVB',
                    'isNovelty' => 'Y'
                )
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->smallEditAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test deleteAction
     */
    public function testLargeDeleteAction()
    {
        $this->setUpAction('large-delete', 1);

        $response = $this->controller->largeDeleteAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test deleteAction
     */
    public function testMediumDeleteAction()
    {
        $this->setUpAction('medium-delete', 1);

        $response = $this->controller->mediumDeleteAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test deleteAction
     */
    public function testSmallDeleteAction()
    {
        $this->setUpAction('small-delete', 1);

        $response = $this->controller->smallDeleteAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test deleteAction without id
     */
    public function testLargeDeleteActionWithoutId()
    {
        $this->setUpAction('large-delete');

        $this->setRestResponse('LicenceVehicle', 'GET', array('Count' => 0, 'Results' => array()));

        $response = $this->controller->largeDeleteAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test deleteAction without id
     */
    public function testMediumDeleteActionWithoutId()
    {
        $this->setUpAction('medium-delete');

        $this->setRestResponse('LicenceVehicle', 'GET', array('Count' => 0, 'Results' => array()));

        $response = $this->controller->mediumDeleteAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test deleteAction without id
     */
    public function testSmallDeleteActionWithoutId()
    {
        $this->setUpAction('small-delete');

        $this->setRestResponse('LicenceVehicle', 'GET', array('Count' => 0, 'Results' => array()));

        $response = $this->controller->smallDeleteAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test addAction with submit
     */
    public function testLargeAddActionWithSubmitWithVehicleOnAnotherLicence()
    {
        $this->setUpAction(
            'large-add',
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
        $response = $this->controller->largeAddAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test addAction with submit
     */
    public function testMediumAddActionWithSubmitWithVehicleOnAnotherLicence()
    {
        $this->setUpAction(
            'medium-add',
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
        $response = $this->controller->mediumAddAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test addAction with submit
     */
    public function testSmallAddActionWithSubmitWithVehicleOnAnotherLicence()
    {
        $this->setUpAction(
            'small-add',
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
        $response = $this->controller->smallAddAction();

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
        if ($service == 'Application' && $method == 'GET'
            && $bundle == ApplicationController::$applicationLicenceDataBundle) {

            return $this->getLicenceData('psv');
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

        if ($service == 'Vehicle' && $method == 'GET') {
            return array(
                'id' => 1,
                'version' => 1,
                'vrm' => 'AB12 ABC',
                'isNovelty' => 'Y'
            );
        }

        $dataBundle = array(
            'properties' => array(
                'id',
                'version',
                'totAuthSmallVehicles',
                'totAuthMediumVehicles',
                'totAuthLargeVehicles'
            ),
            'children' => array(
                'licenceVehicles' => array(
                    'properties' => array(
                        'id',
                        'receivedDate',
                        'specifiedDate',
                        'deletedDate'
                    ),
                    'children' => array(
                        'vehicle' => array(
                            'properties' => array(
                                'vrm',
                                'isNovelty',
                            ),
                            'children' => array(
                                'psvType' => array(
                                    'properties' => array('id')
                                )
                            )
                        )
                    )
                )
            )
        );

        if ($service == 'Licence' && $method == 'GET' && $bundle == $dataBundle) {
            return array(
                'id' => 1,
                'version' => 1,
                'totAuthSmallVehicles' => 1,
                'totAuthMediumVehicles' => 1,
                'totAuthLargeVehicles' => $this->largeVehicles,
                'licenceVehicles' => array(
                    array(
                        'id' => 1,
                        'receivedDate' => null,
                        'specifiedDate' => null,
                        'deletedDate' => null,
                        'vehicle' => array(
                            'vrm' => 'AB12 ABC',
                            'isNovelty' => 'Y',
                            'psvType' => array(
                                'id' => 'vhl_t_a'
                            )
                        )
                    ),
                    array(
                        'id' => 2,
                        'receivedDate' => null,
                        'specifiedDate' => '2014-01-01',
                        'deletedDate' => null,
                        'vehicle' => array(
                            'vrm' => 'AB13 ABC',
                            'isNovelty' => null,
                            'psvType' => array(
                                'id' => 'vhl_t_b'
                            )
                        )
                    ),
                    array(
                        'id' => 3,
                        'receivedDate' => null,
                        'specifiedDate' => null,
                        'deletedDate' => null,
                        'vehicle' => array(
                            'vrm' => 'AB11 ABC',
                            'isNovelty' => null,
                            'psvType' => array(
                                'id' => 'vhl_t_c'
                            )
                        )
                    )
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
