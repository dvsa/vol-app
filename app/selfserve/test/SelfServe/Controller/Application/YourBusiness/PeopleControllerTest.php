<?php

/**
 * People Controller Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace SelfServe\Test\Controller\Application\YourBusiness;

use SelfServe\Test\Controller\Application\AbstractApplicationControllerTestCase;
use SelfServe\Controller\Application\ApplicationController;

/**
 * People Controller Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PeopleControllerTest extends AbstractApplicationControllerTestCase
{

    protected $controllerName = '\SelfServe\Controller\Application\YourBusiness\PeopleController';
    protected $defaultRestResponse = array();
    protected $organisation = 'org_type.lc';

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
        $this->organisation = 'org_type.llp';
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
        $this->organisation = 'org_type.p';
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
        $this->organisation = 'org_type.o';
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
                    'firstName' => 'A',
                    'surname' => 'B',
                    'dateOfBirth' => '2014-01-',
                    'otherNames' => 'other',
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
                    'firstName' => 'A',
                    'surname' => 'B',
                    'dateOfBirth' => '2014-01-',
                    'otherNames' => 'other',
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
                    'firstName' => 'A',
                    'surname' => 'B',
                    'dateOfBirth' => '2014-01-',
                    'otherNames' => 'other',
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
                    'firstName' => 'A',
                    'surname' => 'B',
                    'dateOfBirth' => '2014-01-01',
                    'otherNames' => 'other',
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
                    'firstName' => 'A',
                    'surname' => 'B',
                    'otherNames' => 'other',
                    'position' => 'position',
                    'dateOfBirth' => array(
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
                    'firstName' => 'A',
                    'surname' => 'B',
                    'otherNames' => 'other',
                    'position' => 'position',
                    'dateOfBirth' => array(
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
                    'firstName' => 'A',
                    'surname' => 'B',
                    'otherNames' => 'other',
                    'position' => 'position',
                    'dateOfBirth' => array(
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

            return array(
                'licence' => array(
                    'id' => 10,
                    'version' => 1,
                    'goodsOrPsv' => 'goods',
                    'niFlag' => 0,
                    'licenceType' => 'standard-national',
                    'organisation' => array(
                        'type' => 'org_type.lc'
                    )
                )
            );
        }

        if ($service == 'ApplicationCompletion' && $method == 'GET') {

            return array(
                'Count' => 1,
                'Results' => array(
                    array(
                        'version' => 1,
                        'application' => '1',
                        'sectionTypeOfLicenceStatus' => 2,
                        'sectionTypeOfLicenceOperatorLocationStatus' => 2,
                        'sectionTypeOfLicenceOperatorTypeStatus' => 2,
                        'sectionTypeOfLicenceLicenceTypeStatus' => 2,
                        'sectionYourBusinessStatus' => 2,
                        'sectionYourBusinessBusinessTypeStatus' => 2,
                        'sectionYourBusinessBusinessDetailsStatus' => 2,
                        'sectionYourBusinessAddressesStatus' => 2,
                        'sectionYourBusinessPeopleStatus' => 2,
                        'sectionTaxiPhvStatus' => 2,
                        'sectionOperatingCentresStatus' => 2,
                        'sectionOperatingCentresAuthorisationStatus' => 2,
                        'sectionOperatingCentresFinancialEvidenceStatus' => 2,
                        'sectionTransportManagersStatus' => 2,
                        'sectionVehicleSafetyStatus' => 2,
                        'sectionVehicleSafetyVehicleStatus' => 2,
                        'sectionVehicleSafetySafetyStatus' => 2,
                        'sectionPreviousHistoryStatus' => 2,
                        'sectionPreviousHistoryFinancialHistoryStatus' => 2,
                        'sectionPreviousHistoryLicenceHistoryStatus' => 2,
                        'sectionPreviousHistoryConvictionPenaltiesStatus' => 2,
                        'sectionReviewDeclarationsStatus' => 2,
                        'sectionPaymentSubmissionStatus' => 2,
                        'sectionPaymentSubmissionPaymentStatus' => 0,
                        'sectionPaymentSubmissionSummaryStatus' => 0,
                        'lastSection' => ''
                    )
                )
            );
        }

        $personDataBundle = array(
            'properties' => array(
                'id',
                'title',
                'firstName',
                'surname',
                'dateOfBirth',
                'otherNames',
                'position'
            ),
        );
        if ($service == 'Person' && $method == 'GET' && $bundle == $personDataBundle) {
            return array(
                'Count'  => 1,
                'Results' => array(
                    array(
                        'id' => 1,
                        'title' => 'Mr',
                        'firstName' => 'A',
                        'surname' => 'P',
                        'dateOfBirth' => '2014-01-01',
                        'otherNames' => 'other names',
                        'position' => 'position'
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
                                'registeredCompanyNumber'
                            )
                        )
                    )
                )
             )
        );
        if ($service == 'Application' && $method == 'GET' && $bundle == $organisationTypeBundle) {
            return array(
                'licence' => array(
                    'id' => 10,
                    'version' => 1,
                    'goodsOrPsv' => 'goods',
                    'niFlag' => 0,
                    'licenceType' => 'standard-national',
                    'organisation' => array(
                        'type' => 'org_type.lc',
                        'registeredCompanyNumber' => '12345678'
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
                        'firstName'   => 'Firstname',
                        'surname'     => 'Surname',
                        'dateOfBirth' => 'DOB'
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
