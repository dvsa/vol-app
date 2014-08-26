<?php

/**
 * Licence Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace OlcsTest\Controller\Application\TaxiPhv;

use OlcsTest\Controller\Application\AbstractApplicationControllerTestCase;
use Common\Controller\Application\ApplicationController;

/**
 * Licence Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceControllerTest extends AbstractApplicationControllerTestCase
{
    protected $controllerName =  '\Common\Controller\Application\TaxiPhv\LicenceController';

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
     * Test indexAction With submit
     */
    public function testIndexActionWithSubmitWithRows()
    {
        $this->setUpAction(
            'index',
            null,
            array(
                'table' => array(
                    'rows' => 1
                )
            )
        );

        $this->controller->setEnabledCsrf(false);

        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexAction With submit
     */
    public function testIndexActionWithSubmitWithoutRows()
    {
        $this->setUpAction(
            'index',
            null,
            array(
                'table' => array(
                    'rows' => 0
                )
            )
        );

        $this->controller->setEnabledCsrf(false);

        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test indexAction With Add Crud Action
     */
    public function testIndexActionWithAddCrudAction()
    {
        $this->setUpAction(
            'index', null, array(
                'table' => array(
                    'rows' => 0,
                    'action' => 'Add'
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
        $this->setUpAction(
            'index', null, array(
                'table' => array(
                    'rows' => 1,
                    'action' => 'Edit'
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
        $this->setUpAction(
            'index', null, array(
                'table' => array(
                    'rows' => 1,
                    'action' => 'Edit',
                    'id' => 2
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
        $this->setUpAction(
            'index', null, array(
                'table' => array(
                    'rows' => 1,
                    'action' => array('edit' => array('2' => 'String')),
                    'id' => 2
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
                    'privateHireLicenceNo' => 'AB12345',
                    'licence' => 1
                ),
                'contactDetails' => array(
                    'id' => '',
                    'version' => '',
                    'description' => 'Some Council',
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
     * Test addAction with submit
     *
     * @expectedException Exception
     */
    public function testAddActionWithSubmitWithFailedContactDetails()
    {
        $this->setUpAction(
            'add',
            null,
            array(
                'data' => array(
                    'id' => '',
                    'version' => '',
                    'privateHireLicenceNo' => 'AB12345',
                    'licence' => 1
                ),
                'contactDetails' => array(
                    'id' => '',
                    'version' => '',
                    'description' => 'Some Council',
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

        $this->setRestResponse('ContactDetails', 'POST', array());

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->addAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
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
     * Test editAction
     */
    public function testEditAction()
    {
        $this->setUpAction('edit', 1);

        $this->setRestResponse(
            'PrivateHireLicence',
            'GET',
            array(
                'id' => 1,
                'version' => 1,
                'privateHireLicenceNo' => 'AB12345',
                'contactDetails' => array(
                    'id' => 2,
                    'version' => 2,
                    'description' => 'DMBC',
                    'address' => array(
                        'id' => 3,
                        'version' => 3,
                        'addressLine1' => '1 Test Street',
                        'addressLine2' => 'Testtown',
                        'addressLine3' => '',
                        'addressLine4' => '',
                        'postcode' => 'AB12 1AB',
                        'town' => 'Doncaster',
                        'countryCode' => array('id' => 'GB')
                    )
                )
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->editAction();

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test edit action with submit
     */
    public function testEditActionWithSubmit()
    {
        $this->setUpAction(
            'edit',
            null,
            array(
                'data' => array(
                    'id' => '1',
                    'version' => '1',
                    'privateHireLicenceNo' => 'AB12345',
                    'licence' => 1
                ),
                'contactDetails' => array(
                    'id' => '1',
                    'version' => '1',
                    'description' => 'Some Council',
                ),
                'address' => array(
                    'id' => '1',
                    'version' => '1',
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

            return $this->getLicenceData('psv', 'ltyp_sr');
        }

        if ($service == 'ApplicationCompletion' && $method == 'GET') {

            return $this->getApplicationCompletionData();
        }

        if ($service == 'ContactDetails' && $method == 'POST') {
            return array(
                'id' => 1
            );
        }

        if ($service == 'PrivateHireLicence' && $method == 'GET') {
            return array(
                'Results' => array(
                    array(
                        'id' => 1,
                        'version' => 1,
                        'privateHireLicenceNo' => 'AB12345',
                        'contactDetails' => array(
                            'id' => 2,
                            'version' => 2,
                            'description' => 'DMBC',
                            'address' => array(
                                'id' => 3,
                                'version' => 3,
                                'addressLine1' => '1 Test Street',
                                'addressLine2' => 'Testtown',
                                'addressLine3' => '',
                                'addressLine4' => '',
                                'postcode' => 'AB12 1AB',
                                'town' => 'Doncaster',
                                'countryCode' => array(
                                    'id' => 'GB'
                                )
                            )
                        )
                    )
                )
            );
        }
    }
}
