<?php

/**
 * Safety Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace OlcsTest\Controller\Application\VehicleSafety;

use OlcsTest\Controller\Application\AbstractApplicationControllerTestCase;
use Common\Controller\Application\ApplicationController;

/**
 * Safety Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SafetyControllerTest extends AbstractApplicationControllerTestCase
{

    protected $controllerName = '\Common\Controller\Application\VehicleSafety\SafetyController';
    protected $defaultRestResponse = array();
    private $goodsOrPsv;

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
     *
     * @dataProvider psvProvider
     */
    public function testIndexAction($goodsOrPsv, $hasTrailers)
    {
        $this->goodsOrPsv = $goodsOrPsv;

        $this->setUpAction('index');

        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);

        $form = $this->getFormFromView($response);

        $this->assertEquals($hasTrailers, $form->get('licence')->has('safetyInsTrailers'));

        $label = $form->get('licence')->get('safetyInsVaries')->getLabel();

        $this->assertEquals(!$hasTrailers, (boolean) strstr($label, 'psv'));

        $table = $form->get('table')->get('table')->getTable();

        $emptyMessage = $table->getVariable('empty_message');

        $this->assertEquals(!$hasTrailers, (boolean) strstr($emptyMessage, 'psv'));
    }

    /**
     * Test indexAction With submit
     */
    public function testIndexActionWithSubmit()
    {
        $this->goodsOrPsv = 'goods';

        $this->setUpAction(
            'index',
            null,
            array(
                'licence' => array(
                    'safetyInsVehicles' => 'inspection_interval_vehicle.1',
                    'safetyInsTrailers' => 'inspection_interval_trailer.1',
                    'safetyInsVaries' => 'Y',
                    'tachographIns' => 'tach_external'
                ),
                'table' => array(
                    'rows' => 1
                ),
                'application' => array(
                    'id' => 1,
                    'version' => 1,
                    'safetyConfirmation' => 'Y'
                )
            )
        );

        $this->controller->setEnabledCsrf(false);

        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexAction With Add Crud Action
     */
    public function testIndexActionWithAddCrudAction()
    {
        $this->goodsOrPsv = 'goods';

        $this->setUpAction(
            'index', null, array(
                'licence' => array(
                    'safetyInsVehicles' => 'inspection_interval_vehicle.1',
                    'safetyInsTrailers' => 'inspection_interval_trailer.1',
                    'safetyInsVaries' => 'Y',
                    'tachographIns' => 'tach_external'
                ),
                'table' => array(
                    'rows' => 1,
                    'action' => 'Add'
                ),
                'application' => array(
                    'id' => 1,
                    'version' => 1,
                    'safetyConfirmation' => 'Y'
                )
            )
        );

        $this->controller->setEnabledCsrf(false);

        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexAction With Edit Crud Action without id
     */
    public function testIndexActionWithEditCrudActionWithoutId()
    {
        $this->goodsOrPsv = 'goods';

        $this->setUpAction(
            'index', null, array(
                'licence' => array(
                    'safetyInsVehicles' => 'inspection_interval_vehicle.1',
                    'safetyInsTrailers' => 'inspection_interval_trailer.1',
                    'safetyInsVaries' => 'Y',
                    'tachographIns' => 'tach_external'
                ),
                'table' => array(
                    'rows' => 1,
                    'action' => 'Edit'
                ),
                'application' => array(
                    'id' => 1,
                    'version' => 1,
                    'safetyConfirmation' => 'Y'
                )
            )
        );

        $this->controller->setEnabledCsrf(false);

        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexAction With Edit Crud Action
     */
    public function testIndexActionWithEditCrudAction()
    {
        $this->goodsOrPsv = 'goods';

        $this->setUpAction(
            'index', null, array(
                'licence' => array(
                    'safetyInsVehicles' => 'inspection_interval_vehicle.1',
                    'safetyInsTrailers' => 'inspection_interval_trailer.1',
                    'safetyInsVaries' => 'Y',
                    'tachographIns' => 'tach_external'
                ),
                'table' => array(
                    'rows' => 1,
                    'action' => 'Edit',
                    'id' => 2
                ),
                'application' => array(
                    'id' => 1,
                    'version' => 1,
                    'safetyConfirmation' => 'Y'
                )
            )
        );

        $this->controller->setEnabledCsrf(false);

        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexAction With Edit Link Crud action
     */
    public function testIndexActionWithEditLinkCrudAction()
    {
        $this->goodsOrPsv = 'goods';

        $this->setUpAction(
            'index', null, array(
                'licence' => array(
                    'safetyInsVehicles' => 'inspection_interval_vehicle.1',
                    'safetyInsTrailers' => 'inspection_interval_trailer.1',
                    'safetyInsVaries' => 'Y',
                    'tachographIns' => 'tach_external'
                ),
                'table' => array(
                    'rows' => 1,
                    'action' => array('edit' => array('2' => 'String')),
                    'id' => 2
                ),
                'application' => array(
                    'id' => 1,
                    'version' => 1,
                    'safetyConfirmation' => 'Y'
                )
            )
        );

        $this->controller->setEnabledCsrf(false);

        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test addAction
     */
    public function testAddAction()
    {
        $this->goodsOrPsv = 'goods';

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

        $this->goodsOrPsv = 'goods';

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

        $this->goodsOrPsv = 'goods';

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
        $this->goodsOrPsv = 'goods';

        $this->setUpAction(
            'add',
            null,
            array(
                'data' => array(
                    'isExternal' => 'Y'
                ),
                'contactDetails' => array(
                    'fao' => 'Someone'
                ),
                'address' => array(
                    'id' => '',
                    'version' => '',
                    'addressLine1' => 'Address 1',
                    'town' => 'City',
                    'countryCode' => 'GB',
                    'postcode' => 'AB1 1BA'
                )
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->addAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test addAction with submit with add another
     */
    public function testAddActionWithSubmitWithAddAnother()
    {
        $this->goodsOrPsv = 'goods';

        $this->setUpAction(
            'add', null, array(
                'data' => array(
                    'isExternal' => 'Y'
                ),
                'contactDetails' => array(
                    'fao' => 'Someone'
                ),
                'address' => array(
                    'id' => '',
                    'version' => '',
                    'addressLine1' => 'Address 1',
                    'town' => 'City',
                    'countryCode' => 'GB',
                    'postcode' => 'AB1 1BA'
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
     * Test addAction with submit with failures
     *
     * @expectedException Exception
     */
    public function testAddActionWithSubmitWithFailure()
    {
        $this->goodsOrPsv = 'goods';

        $this->setUpAction(
            'add',
            null,
            array(
                'data' => array(
                    'isExternal' => 'Y'
                ),
                'contactDetails' => array(
                    'fao' => 'Someone'
                ),
                'address' => array(
                    'id' => '',
                    'version' => '',
                    'addressLine1' => 'Address 1',
                    'town' => 'City',
                    'countryCode' => 'GB',
                    'postcode' => 'AB1 1BA'
                )
            )
        );

        $this->setRestResponse('ContactDetails', 'POST', '');

        $this->controller->setEnabledCsrf(false);
        $this->controller->addAction();
    }

    /**
     * Test editAction
     */
    public function testEditAction()
    {
        $this->goodsOrPsv = 'goods';

        $this->setUpAction('edit', 1);

        $response = $this->controller->editAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test editAction with submit
     */
    public function testEditActionWithSubmit()
    {
        $this->goodsOrPsv = 'goods';

        $this->setUpAction(
            'edit',
            1,
            array(
                'data' => array(
                    'id' => 1,
                    'version' => 1,
                    'isExternal' => 'Y'
                ),
                'contactDetails' => array(
                    'id' => 3,
                    'version' => 1,
                    'fao' => 'Someone'
                ),
                'address' => array(
                    'id' => 5,
                    'version' => 1,
                    'addressLine1' => 'Address 1',
                    'town' => 'City',
                    'countryCode' => 'GB',
                    'postcode' => 'AB1 1BA'
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
        $this->goodsOrPsv = 'goods';

        $this->setUpAction('delete', 1);

        $response = $this->controller->deleteAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test deleteAction without id
     */
    public function testDeleteActionWithoutId()
    {
        $this->goodsOrPsv = 'goods';

        $this->setUpAction('delete');

        $response = $this->controller->deleteAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Psv provider
     *
     * @return array
     */
    public function psvProvider()
    {
        return array(
            array('psv', false),
            array('goods', true)
        );
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

            return $this->getLicenceData($this->goodsOrPsv);
        }

        if ($service == 'ApplicationCompletion' && $method == 'GET') {

            return $this->getApplicationCompletionData();
        }

        if ($service == 'Licence' && $method == 'POST') {
            return array('id' => 1);
        }

        if ($service == 'ContactDetails' && $method == 'POST') {
            return array('id' => 7);
        }

        if ($service == 'Workshop' && $method == 'POST') {
            return array('id' => 6);
        }

        $dataBundle = array(
            'properties' => array(
                'id',
                'version',
                'safetyConfirmation'
            ),
            'children' => array(
                'licence' => array(
                    'properties' => array(
                        'id',
                        'version',
                        'safetyInsVehicles',
                        'safetyInsTrailers',
                        'safetyInsVaries',
                        'tachographInsName'
                    ),
                    'children' => array(
                        'tachographIns' => array(
                            'properties' => array('id')
                        ),
                        'workshops' => array(
                            'properties' => array(
                                'id',
                                'isExternal'
                            ),
                            'children' => array(
                                'contactDetails' => array(
                                    'properties' => array(
                                        'fao'
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
                'safetyConfirmation' => 1,
                'licence' => array(
                    'id' => 2,
                    'version' => 2,
                    'safetyInsVehicles' => null,
                    'safetyInsTrailers' => null,
                    'safetyInsVaries' => null,
                    'tachographInsName' => null,
                    'tachographIns' => null,
                    'workshops' => array(
                        array(
                            'id' => 1,
                            'isExternal' => 1,
                            'contactDetails' => array(
                                'fao' => 'Someone',
                                'address' => array(
                                    'addressLine1' => 'Address 1',
                                    'addressLine2' => 'Address 2',
                                    'addressLine3' => 'Address 3',
                                    'addressLine4' => 'Address 4',
                                    'town' => 'City',
                                    'countryCode' => array(
                                        'id' => 'GB'
                                    ),
                                    'postcode' => 'AB1 1AB'
                                )
                            )
                        )
                    )
                )
            );
        }

        $actionDataBundle = array(
            'properties' => array(
                'id',
                'version',
                'isExternal'
            ),
            'children' => array(
                'contactDetails' => array(
                    'properties' => array(
                        'id',
                        'version',
                        'fao'
                    ),
                    'children' => array(
                        'address' => array(
                            'properties' => array(
                                'id',
                                'version',
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
                )
            )
        );

        if ($service == 'Workshop' && $method == 'GET' && $bundle == $actionDataBundle) {

            return array(
                'id' => 7,
                'version' => 1,
                'isExternal' => 1,
                'contactDetails' => array(
                    'id' => 6,
                    'version' => 2,
                    'fao' => 'Someone',
                    'address' => array(
                        'id' => 100,
                        'version' => 1,
                        'addressLine1' => 'Address Line 1',
                        'addressLine2' => '',
                        'addressLine3' => '',
                        'addressLine4' => '',
                        'town' => 'City',
                        'countryCode' => array(
                            'id' => 'GB'
                        ),
                        'postcode' => 'AB1 1AB'
                    )
                )
            );
        }
    }
}
