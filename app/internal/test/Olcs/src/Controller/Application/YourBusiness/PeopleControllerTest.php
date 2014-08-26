<?php

/**
 * People Controller Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace OlcsTest\Controller\Application\YourBusiness;

use OlcsTest\Controller\Application\AbstractApplicationControllerTestCase;
use Common\Controller\Application\ApplicationController;
use Common\Controller\Application\YourBusiness\PeopleController;

/**
 * People Controller Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PeopleControllerTest extends AbstractApplicationControllerTestCase
{

    protected $controllerName = '\Common\Controller\Application\YourBusiness\PeopleController';
    protected $defaultRestResponse = array();
    protected $organisation = PeopleController::ORG_TYPE_REGISTERED_COMPANY;

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
     * Test indexAction - organisation's type - limited company
     */
    public function testIndexActionOrgTypeLc()
    {
        $this->setUpAction('index');

        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test indexAction - organisation's type - LLP
     */
    public function testIndexActionOrgTypeLlp()
    {
        $this->setUpAction('index');
        $this->organisation = PeopleController::ORG_TYPE_LLP;
        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test indexAction - organisation's type - partners
     */
    public function testIndexActionOrgTypePartners()
    {
        $this->setUpAction('index');
        $this->organisation = PeopleController::ORG_TYPE_PARTNERSHIP;
        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test indexAction - organisation's type not defined
     */
    public function testIndexActionOrgTypeNotDefined()
    {
        $this->setUpAction('index');
        $this->organisation = '';
        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test indexAction - organisation's type - other
     */
    public function testIndexActionOrgTypeOther()
    {
        $this->setUpAction('index');
        $this->organisation = PeopleController::ORG_TYPE_OTHER;
        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test indexAction with submit
     */
    public function testIndexActionWithSubmit()
    {
        $this->setUpAction('index', null, array('action' => 'add'));

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexAction With Add Crud Action
     */
    public function testIndexActionWithAddCrudAction()
    {
        $this->setUpAction(
            'index', null, array(
                'data' => array(
                    'id' => 1,
                    'title' => 'Mr',
                    'forename' => 'A',
                    'familyName' => 'B',
                    'birthDate' => '2014-01-',
                    'otherName' => 'other',
                    'position' => 'position'
                ),
                'table' => array(
                    'rows' => 1,
                    'action' => 'Add'
                ),
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
                    'id' => 1,
                    'title' => 'Mr',
                    'forename' => 'A',
                    'familyName' => 'B',
                    'birthDate' => '2014-01-',
                    'otherName' => 'other',
                    'position' => 'position'
                ),
                'table' => array(
                    'rows' => 1,
                    'action' => 'Edit'
                ),
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
                    'id' => 1,
                    'title' => 'Mr',
                    'forename' => 'A',
                    'familyName' => 'B',
                    'birthDate' => '2014-01-',
                    'otherName' => 'other',
                    'position' => 'position'
                ),
                'table' => array(
                    'rows' => 1,
                    'action' => 'Edit',
                    'id' => 1
                ),
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
                    'id' => 1,
                    'title' => 'Mr',
                    'forename' => 'A',
                    'familyName' => 'B',
                    'birthDate' => '2014-01-01',
                    'otherName' => 'other',
                    'position' => 'position'
                ),
                'table' => array(
                    'rows' => 1,
                    'action' => array('Edit' => array('2' => 'String')),
                    'id' => 1
                ),
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
                    'title' => 'Mr',
                    'forename' => 'A',
                    'familyName' => 'B',
                    'otherName' => 'other',
                    'position' => 'position',
                    'birthDate' => array(
                        'month' => 1,
                        'day'   => 1,
                        'year'  => 2014
                     ),
                ),
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
                    'id' => 1,
                    'title' => 'Mr',
                    'forename' => 'A',
                    'familyName' => 'B',
                    'otherName' => 'other',
                    'position' => 'position',
                    'birthDate' => array(
                        'month' => 1,
                        'day'   => 1,
                        'year'  => 2014
                     ),
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
                    'id' => 1,
                    'title' => 'Mr',
                    'forename' => 'A',
                    'familyName' => 'B',
                    'otherName' => 'other',
                    'position' => 'position',
                    'birthDate' => array(
                        'month' => 1,
                        'day'   => 1,
                        'year'  => 2014
                     ),
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

        $personDataBundle = array(
            'properties' => null,
            'children' => array(
                'person' => array(
                    'properties' => array(
                        'id',
                        'title',
                        'forename',
                        'familyName',
                        'birthDate',
                        'otherName',
                        'position'
                    )
                )
            )
        );

        if ($service == 'OrganisationPerson' && $method == 'GET' && $bundle == $personDataBundle) {
            return array(
                'Count'  => 1,
                'Results' => array(
                    array(
                        'person' => array(
                            'id' => 1,
                            'title' => 'Mr',
                            'forename' => 'A',
                            'familyName' => 'P',
                            'birthDate' => '2014-01-01',
                            'otherName' => 'other names',
                            'position' => 'position'
                        )
                    )
                )
            );
        }

        $organisationTypeBundle = array(
            'children' => array(
                'licence' => array(
                    'children' => array(
                        'organisation' => array(
                            'properties' => array(
                                'id',
                                'version',
                                'type',
                                'companyOrLlpNo'
                            )
                        )
                    )
                )
             )
        );
        if ($service == 'Application' && $method == 'GET' && $bundle == $organisationTypeBundle) {
            return array(
                'licence' => array(
                    'organisation' => array(
                        'id' => 1,
                        'version' => 1,
                        'type' => array(
                            'id' => PeopleController::ORG_TYPE_REGISTERED_COMPANY
                        ),
                        'companyOrLlpNo' => '12345678'
                    )
                )
            );
        }
        $personsExistsBundle = array(
            'properties' => array(
                'id'
            )
        );
        if ($service == 'Person' && $method == 'GET' && $bundle == $personsExistsBundle) {
            return array(
                'Count' => 0,
                'Results' => array()
            );
        }
        $companiesHouseData = array(
            'type'  => 'currentCompanyOfficers',
            'value' => '12345678'
        );
        if ($service == 'CompaniesHouse' && $method == 'GET' && $data == $companiesHouseData) {
            return array(
                'Count' => 1,
                'Results' => array(
                    array(
                        'title'       => 'Title',
                        'forename'   => 'Firstname',
                        'familyName'     => 'Surname',
                        'birthDate' => 'DOB'
                    )
                )
            );
        }
        $organisationDataBundle = array(
            'children' => array(
                'licence' => array(
                    'children' => array(
                        'organisation' => array(
                            'properties' => array(
                                'id',
                                'version',
                                'type',
                            )
                        )
                    )
                )
             )
        );
        if ($service == 'Application' && $method == 'GET' && $bundle == $organisationDataBundle) {
            return array(
                'licence' => array(
                    'organisation' => array(
                        'type' => $this->organisation
                    )
                )
            );
        }

    }

    /**
     * Test populatePeople method
     */
    public function testPopulatePeople()
    {
        $this->setUpAction('index');
        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }
}
