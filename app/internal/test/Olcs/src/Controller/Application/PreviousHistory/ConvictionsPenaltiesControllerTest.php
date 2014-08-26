<?php

/**
 * ConvictionsPenaltiesControllerTest
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace OlcsTest\Controller\Application\PreviousHistory;

use OlcsTest\Controller\Application\AbstractApplicationControllerTestCase;
use Common\Controller\Application\ApplicationController;

/**
 * ConvictionsPenaltiesControllerTest
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ConvictionsPenaltiesControllerTest extends AbstractApplicationControllerTestCase
{
    protected $controllerName =  '\Common\Controller\Application\PreviousHistory\ConvictionsPenaltiesController';

    protected $defaultRestResponse = array();

    protected $previousConviction;

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
        $this->previousConviction = true;

        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);

    }

    /**
     * Test indexAction with no conviction flag
     */
    public function testIndexActionWithNoConvictionFlag()
    {
        $this->setUpAction('index');
        $this->previousConviction = false;

        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);

    }

    /**
     * Test indexAction with undefined conviction flag
     */
    public function testIndexActionWithUndefinedConvictionFlag()
    {
        $this->setUpAction('index');
        $this->previousConviction = null;

        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);

    }

    /**
     * Test indexAction with submit
     */
    public function testIndexActionWithSubmit()
    {
        $this->setUpAction('index', null, array('foo' => 'bar'));

        $this->controller->setEnabledCsrf(false);

        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test indexAction With Add Crud Action
     */
    public function testIndexActionWithAddCrudAction()
    {
        $this->setUpAction(
            'index', null, array(
                'data' => array(
                    'prevConviction' => 'Y',
                    'id' => 1,
                    'version' => 1
                ),
                'table' => array(
                    'rows' => 1,
                    'action' => 'Add'
                ),
                'convictionsConfirmation' => array(
                    'convictionsConfirmation' => 'Y',
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
                'data' => array(
                    'prevConviction' => 'Y',
                    'id' => 1,
                    'version' => 1
                ),
                'table' => array(
                    'rows' => 1,
                    'action' => 'Edit'
                ),
                'convictionsConfirmation' => array(
                    'convictionsConfirmation' => 'Y',
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
                'data' => array(
                    'prevConviction' => 'Y',
                    'id' => 1,
                    'version' => 1
                ),
                'table' => array(
                    'rows' => 1,
                    'action' => 'Edit',
                    'id' => 1
                ),
                'convictionsConfirmation' => array(
                    'convictionsConfirmation' => 'Y',
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
                'data' => array(
                    'prevConviction' => 'Y',
                    'id' => 1,
                    'version' => 1
                ),
                'table' => array(
                    'rows' => 1,
                    'action' => array('Edit' => array('2' => 'String')),
                    'id' => 1
                ),
                'convictionsConfirmation' => array(
                    'convictionsConfirmation' => 'Y',
                )
            )
        );

        $this->controller->setEnabledCsrf(false);

        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
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
            'edit', 1, array(
                'data' => array(
                    'id' => 1,
                    'version' => 1,
                    'title' => 'Mr',
                    'forename' => 'Alex',
                    'familyName' => 'P',
                    'convictionDate' => array(
                        'month' => 1,
                        'day'   => 1,
                        'year'  => 2014
                     ),
                    'categoryText' => 'Offence',
                    'notes' => 'No MOT',
                    'courtFpn' => 'Leeds court',
                    'penalty' => '100£'
                )
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->editAction();

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
            'add', null, array(
                'data' => array(
                    'id' => '',
                    'version' => '',
                    'title' => 'Mr',
                    'forename' => 'Alex',
                    'familyName' => 'P',
                    'convictionDate' => array(
                        'month' => 1,
                        'day'   => 1,
                        'year'  => 2014
                     ),
                    'categoryText' => 'Offence',
                    'notes' => 'No MOT',
                    'courtFpn' => 'Leeds court',
                    'penalty' => '100£'
                ),
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
        $this->setUpAction(
            'add', null, array(
                'data' => array(
                    'id' => '',
                    'version' => '',
                    'title' => 'Mr',
                    'forename' => 'Alex',
                    'familyName' => 'P',
                    'convictionDate' => array(
                        'month' => 1,
                        'day'   => 1,
                        'year'  => 2014
                     ),
                    'categoryText' => 'Offence',
                    'notes' => 'No MOT',
                    'courtFpn' => 'Leeds court',
                    'penalty' => '100£'
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

        $convictionDataBundle = array(
            'properties' => array(
                'id',
                'convictionDate',
                'convictionCategory',
                'notes',
                'courtFpn',
                'categoryText',
                'penalty',
                'title',
                'forename',
                'familyName'
            )
        );

        if ($service == 'PreviousConviction' && $method === 'GET' && $bundle == $convictionDataBundle) {
            return array(
                'Count'  => 1,
                'Results' => array(
                    array(
                        'id' => 1,
                        'convictionDate' => '01/01/2014',
                        'convictionCategory' => 'Offence',
                        'notes' => 'No MOT',
                        'courtFpn' => 'Leeds court',
                        'penalty' => '100£',
                        'title' => 'Mr',
                        'forename' => 'Alex',
                        'familyName' => 'P'
                    )
                )
            );
        }

        if ($service == 'Application' && $method === 'GET') {
            return array(
                'id' => 1,
                'version' => 1,
                'prevConviction' => $this->previousConviction,
                'convictionsConfirmation' => 'Y'
            );
        }
    }
}
