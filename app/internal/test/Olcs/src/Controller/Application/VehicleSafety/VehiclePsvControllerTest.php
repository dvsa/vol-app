<?php

/**
 * VehiclePsv Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace OlcsTest\Controller\Application\VehiclePsvSafety;

use OlcsTest\Controller\Application\AbstractApplicationControllerTestCase;
use Common\Controller\Application\ApplicationController;

/**
 * VehiclePsv Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehiclePsvControllerTest extends AbstractApplicationControllerTestCase
{
    private $largeVehicles = 5;

    protected $controllerName = '\Common\Controller\Application\VehicleSafety\VehiclePsvController';
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
        $this->setUpAction('index', null, array('large' => array('action' => 'medium-add')));

        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexAction with crud action
     */
    public function testIndexActionWithSmallCrudAction()
    {
        $this->setUpAction('index', null, array('large' => array('action' => 'small-add')));

        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexAction with submit
     */
    public function testIndexActionWithSubmit()
    {
        $this->setUpAction('index', null, array('data' => array('hasEnteredReg' => 'N')));

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexAction with submit
     */
    public function testIndexActionWithSubmitWithInvalid()
    {
        $this->setUpAction(
            'index',
            null,
            array(
                'data' => array('hasEnteredReg' => 'Y'),
                'large' => array('rows' => 0)
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test indexAction with submit
     */
    public function testIndexActionWithSubmitWithEnterReg()
    {
        $this->setUpAction(
            'index',
            null,
            array(
                'data' => array('hasEnteredReg' => 'Y'),
                'large' => array('rows' => 1),
                'medium' => array('rows' => 2),
                'small' => array('rows' => 5)
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->indexAction();

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
                    'isNovelty' => 'Y',
                    'makeModel' => 'German whip'
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
                    'isNovelty' => 'Y',
                    'makeModel' => 'German whip'
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
                    'isNovelty' => 'Y',
                    'makeModel' => 'German whip'
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
                    'isNovelty' => 'Y',
                    'makeModel' => 'German whip'
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

        if ($service == 'Vehicle' && $method == 'GET') {
            return array(
                'id' => 1,
                'version' => 1,
                'vrm' => 'AB12 ABC',
                'isNovelty' => 'Y',
                'makeModel' => 'German whip'
            );
        }

        $dataBundle = array(
            'properties' => array(
                'id',
                'version',
                'totAuthSmallVehicles',
                'totAuthMediumVehicles',
                'totAuthLargeVehicles',
                'hasEnteredReg'
            ),
            'children' => array(
                'licence' => array(
                    'properties' => null,
                    'children' => array(
                        'licenceVehicles' => array(
                            'properties' => null,
                            'children' => array(
                                'vehicle' => array(
                                    'properties' => array(
                                        'id',
                                        'vrm',
                                        'makeModel',
                                        'isNovelty'
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
                )
            )
        );

        if ($service == 'Application' && $method == 'GET' && $bundle == $dataBundle) {
            return array(
                'id' => 1,
                'version' => 1,
                'totAuthSmallVehicles' => 10,
                'totAuthMediumVehicles' => 10,
                'totAuthLargeVehicles' => $this->largeVehicles,
                'hasEnteredReg' => 'Y',
                'licence' => array(
                    'licenceVehicles' => array(
                        array(
                            'vehicle' => array(
                                'id' => 1,
                                'vrm' => 'AB12 ABC',
                                'isNovelty' => 'Y',
                                'makeModel' => 'German whip',
                                'psvType' => array(
                                    'id' => 'vhl_t_a'
                                )
                            )
                        ),
                        array(
                            'vehicle' => array(
                                'id' => 2,
                                'vrm' => 'AB13 ABC',
                                'isNovelty' => null,
                                'makeModel' => null,
                                'psvType' => array(
                                    'id' => 'vhl_t_b'
                                )
                            )
                        ),
                        array(
                            'vehicle' => array(
                                'id' => 3,
                                'vrm' => 'AB11 ABC',
                                'isNovelty' => null,
                                'makeModel' => null,
                                'psvType' => array(
                                    'id' => 'vhl_t_c'
                                )
                            )
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
