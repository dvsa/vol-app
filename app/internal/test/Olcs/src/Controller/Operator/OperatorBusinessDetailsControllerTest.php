<?php

/**
 * Operator business details controller tests
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Controller\Operator;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use OlcsTest\Bootstrap;
use Common\Service\Entity\OrganisationEntityService;
use Common\Service\Entity\AddressEntityService;

/**
 * Operator controller tests
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class OperatorBusinessDetailsControllerTest extends AbstractHttpControllerTestCase
{
    /**
     * @var array
     */
    protected $mockMethods = [
        'params',
        'getServiceLocator',
        'getRequest',
        'isButtonPressed',
        'redirectToRoute',
        'getResponse',
        'getViewWithOrganisation',
        'renderView',
        'getForm',
        'loadScripts',
        'processCompanyLookup'
    ];

    /**
     * @var string
     */
    protected $request;

    /**
     * @var int
     */
    protected $statusCode = 200;

    /**
     * @var string
     */
    protected $controllerName = '\Olcs\Controller\Operator\OperatorBusinessDetailsController';

    /**
     * @var bool
     */
    protected $newAddress = false;

    /**
     * @var bool
     */
    protected $newOrganisation = false;

    /**
     * @var bool
     */
    protected $newPerson = false;

    /**
     * @var array
     */
    protected $person = [
        'forename' => 'John',
        'familyName' => 'Doo',
        'id' => 1,
        'version' => 1
    ];

    /**
     * @var string
     */
    protected $organisatioType = OrganisationEntityService::ORG_TYPE_REGISTERED_COMPANY;

    /**
     * @var array
     */
    protected $post = [
        'operator-details' => [
            'id' => 1,
            'version' => 1,
            'name' => 'name',
            'companyNumber' => [
                'company_number' => '12345678'
            ],
            'firstName' => 'first',
            'lastName' => 'last',
            'personId' => '1',
            'personVersion' => '1',
            'natureOfBusiness' => [1]
        ],
        'operator-business-type' => [
            'type' => OrganisationEntityService::ORG_TYPE_REGISTERED_COMPANY,
        ],
        'registeredAddress' => [
            'addressLine1' => 'addressLine1',
            'addressLine2' => 'addressLine2',
            'addressLine3' => 'addressLine3',
            'addressLine4' => 'addressLine4',
            'town' => 'town',
            'postcode' => 'postocde',
            'id' => 1,
            'version' => 1
        ],
        'form-actions' => ['save' => '']
    ];

    /**
     * Set up action
     */
    public function setUpAction($operator, $isPost = false, $isButtonCancelPressed = false)
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../../config/application.config.php'
        );

        $this->controller = $this->getMock($this->controllerName, $this->mockMethods);

        $organisation = [
            'name' => 'name',
            'id' => 1,
            'version' => 1,
            'companyOrLlpNo' => '12345678',
            'contactDetails' => [[
                'address' => [
                    'id' => 1,
                    'version' => 1,
                    'addressLine1' => 'addressLine1',
                    'addressLine2' => 'addressLine2',
                    'addressLine3' => 'addressLine3',
                    'addressLine4' => 'addressLine4',
                    'town' => 'town',
                    'postcode' => 'postcode'
                ],
                'contactType' => [
                    'id' => AddressEntityService::CONTACT_TYPE_REGISTERED_ADDRESS
                ]
            ]],
            'type' => [
                'id' => $this->organisationType
            ]
        ];

        $mockForm = $this->getMock('\StdClass', ['setData', 'isValid', 'getData']);
        $mockForm->expects($this->any())
            ->method('isValid')
            ->will($this->returnValue(true));

        $mockForm->expects($this->any())
            ->method('setData')
            ->will($this->returnValue(null));

        $mockForm->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($this->post));

        $mockOrganisation = $this->getMock('\StdClass', ['getBusinessDetailsData', 'save']);
        $mockOrganisation->expects($this->any())
            ->method('getBusinessDetailsData')
            ->with($this->equalTo(1))
            ->will($this->returnValue($organisation));

        if ($this->newOrganisation) {
            $mockOrganisation->expects($this->any())
                ->method('save')
                ->will($this->returnValue(['id' => 1]));
        }

        $mockPerson = $this->getMock('\StdClass', ['getFirstForOrganisation', 'save']);
        $mockPerson->expects($this->any())
            ->method('getFirstForOrganisation')
            ->with($this->equalTo(1))
            ->will($this->returnValue($this->person));

        if ($this->newPerson) {
            $mockPerson->expects($this->any())
                ->method('save')
                ->will($this->returnValue(['id' => 1]));
        }

        $mockAddress = $this->getMock('\StdClass', ['save']);
        if ($this->newAddress) {
            $mockAddress->expects($this->any())
                ->method('save')
                ->will($this->returnValue(['id' => 1]));
        }

        $mockContactDetails = $this->getMock('\StdClass', ['save']);
        $mockOrganisationPerson = $this->getMock('\StdClass', ['save']);

        $mockCompaniesHouse = $this->getMock('\StdClass', ['search']);
        $mockCompaniesHouse->expects($this->any())
            ->method('search')
            ->will($this->returnValue(['Results' => [['CompanyName' => 'Company Name']], 'Count' => 1]));

        $mockOrgNob = $this->getMock(
            '\StdClass',
            ['getAllForOrganisationForSelect', 'getAllForOrganisation', 'deleteByOrganisationAndIds', 'save']
        );

        $nob = [[
            'id' => 1,
            'version' => 1,
            'organisation' => ['id' => 1],
            'refData' => ['id' => '1', 'description' => 'desc1']
        ]];
        $mockOrgNob->expects($this->any())
            ->method('getAllForOrganisation')
            ->will($this->returnValue($nob));

        $mockOrgNob->expects($this->any())
            ->method('getAllForOrganisationForSelect')
            ->will($this->returnValue([1]));

        $mockTranslator = $this->getMock('\StdClass', ['translate']);
        $mockTranslator->expects($this->any())
            ->method('translate')
            ->with($this->equalTo('internal-operator-create-new-operator'))
            ->will($this->returnValue('some translated text'));

        $mockParams = $this->getMock('\StdClass', ['fromRoute', 'fromPost']);
        $mockParams->expects($this->any())
            ->method('fromRoute')
            ->with($this->equalTo('operator'))
            ->will($this->returnValue($operator));

        $mockParams->expects($this->any())
            ->method('fromPost')
            ->will($this->returnValue($this->post));

        $mockResponse = $this->getMock('Zend\Http\Response', ['getStatusCode']);
        $mockResponse->expects($this->any())
            ->method('getStatusCode')
            ->will($this->returnValue($this->statusCode));

        $this->request = $this->getMock('\StdClass', ['isPost', 'getPost', 'getUri', 'isXmlHttpRequest']);

        $mockUri = $this->getMock('\StdClass', ['getPath']);
        $mockUri->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/'));

        $this->request->expects($this->any())
            ->method('isPost')
            ->will($this->returnValue($isPost));

        $this->request->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue($this->post));

        $this->request->expects($this->any())
            ->method('getUri')
            ->will($this->returnValue($mockUri));

        $this->request->expects($this->any())
            ->method('isXmlHttpRequest')
            ->will($this->returnValue(false));

        $this->controller->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->request));

        $mockFormHelper = $this->getMock('\StdClass', ['remove', 'processCompanyNumberLookupForm']);
        $mockFormHelper->expects($this->any())
            ->method('remove')
            ->will($this->returnValue(null));

        $mockFormHelper->expects($this->any())
            ->method('processCompanyNumberLookupForm')
            ->will($this->returnValue(null));

        $mockView = $this->getMock('\StdClass', ['setTemplate']);
        $mockView->expects($this->any())
            ->method('setTemplate')
            ->with('partials/form')
            ->will($this->returnValue(null));

        $this->controller->expects($this->any())
            ->method('getViewWithOrganisation')
            ->will($this->returnValue($mockView));

        $this->controller->expects($this->any())
            ->method('renderView')
            ->will($this->returnValue('view'));

        $this->controller->expects($this->any())
            ->method('getForm')
            ->with('operator')
            ->will($this->returnValue($mockForm));

        $this->controller->expects($this->once())
            ->method('loadScripts')
            ->with(['operator-profile'])
            ->will($this->returnValue(null));

        $this->serviceManager = Bootstrap::getServiceManager();
        $this->serviceManager->setAllowOverride(true);
        $this->serviceManager->setService('translator', $mockTranslator);
        $this->serviceManager->setService('Entity\Organisation', $mockOrganisation);
        $this->serviceManager->setService('Entity\Person', $mockPerson);
        $this->serviceManager->setService('Entity\Address', $mockAddress);
        $this->serviceManager->setService('Entity\ContactDetails', $mockContactDetails);
        $this->serviceManager->setService('Entity\OrganisationPerson', $mockOrganisationPerson);
        $this->serviceManager->setService('Data\CompaniesHouse', $mockCompaniesHouse);
        $this->serviceManager->setService('Entity\OrganisationNatureOfBusiness', $mockOrgNob);
        $this->serviceManager->setService('Helper\Form', $mockFormHelper);

        $this->controller->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($mockResponse));

        $this->controller->expects($this->any())
            ->method('isButtonPressed')
            ->with('cancel')
            ->will($this->returnValue($isButtonCancelPressed));

        $this->controller->expects($this->any())
            ->method('redirectToRoute')
            ->will($this->returnValue($mockResponse));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($this->serviceManager));

        $this->controller->setEnabledCsrf(false);

    }

    /**
     * Test index action with edit operator
     *
     * @dataProvider organisationTypesProvider
     * @group operatorBusinessDetailsController
     */
    public function testIndexActionWithEditOperator($organisationType)
    {
        $this->organisationType = $organisationType;
        $this->setUpAction(1);
        $response = $this->controller->indexAction();
        $this->assertEquals('view', $response);
    }

    /**
     * Organisation types provider
     */
    public function organisationTypesProvider()
    {
        return [
            [OrganisationEntityService::ORG_TYPE_REGISTERED_COMPANY],
            [OrganisationEntityService::ORG_TYPE_SOLE_TRADER],
            [OrganisationEntityService::ORG_TYPE_PARTNERSHIP],
            [OrganisationEntityService::ORG_TYPE_LLP],
            [OrganisationEntityService::ORG_TYPE_OTHER]
        ];
    }

    /**
     * Test index action with add operator
     *
     * @dataProvider organisationTypesProvider
     * @group operatorBusinessDetailsController
     */
    public function testIndexActionWithAddOperator($organisationType)
    {
        $this->organisationType = $organisationType;
        $this->setUpAction(null);
        $response = $this->controller->indexAction();
        $this->assertEquals('view', $response);
    }

    /**
     * Test index action with post add operator
     *
     * @dataProvider organisationTypesProvider
     * @group operatorBusinessDetailsController
     */
    public function testIndexActionWithPostAddOperator($organisationType)
    {
        $this->organisationType = $organisationType;
        $this->post['operator-business-type']['type'] = $organisationType;
        $this->statusCode = 302;
        $this->setUpAction(null, true);
        $response = $this->controller->indexAction();
        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test index action with post edit operator
     *
     * @dataProvider organisationTypesProvider
     * @group operatorBusinessDetailsController
     */
    public function testIndexActionWithPostEditOperator($organisationType)
    {
        $this->organisationType = $organisationType;
        $this->post['operator-business-type']['type'] = $organisationType;
        $this->setUpAction(1, true);
        $response = $this->controller->indexAction();
        $this->assertEquals('view', $response);
    }

    /**
     * Test index action with post edit operator - testing new person / organisation record for Sole Trader
     *
     * @group operatorBusinessDetailsController
     */
    public function testIndexActionWithPostEditOperatorSoleTraderNewPerson()
    {
        $this->organisationType = OrganisationEntityService::ORG_TYPE_SOLE_TRADER;
        $this->post['operator-business-type']['type'] = OrganisationEntityService::ORG_TYPE_SOLE_TRADER;
        $this->post['operator-details']['personId'] = '';
        $this->setUpAction(1, true);
        $response = $this->controller->indexAction();
        $this->assertEquals('view', $response);
    }

    /**
     * Test index action with change business type (js-enabled form)
     *
     * @group operatorBusinessDetailsController
     */
    public function testIndexActionWithChangeBusinessTypeJs()
    {
        $this->organisationType = OrganisationEntityService::ORG_TYPE_REGISTERED_COMPANY;
        $this->post['operator-business-type']['type'] = OrganisationEntityService::ORG_TYPE_REGISTERED_COMPANY;
        $this->post['operator-business-type']['typeChanged'] = 1;
        $this->setUpAction(1, true);
        $response = $this->controller->indexAction();
        $this->assertEquals('view', $response);
    }

    /**
     * Test index action with change business type (js-disabled form)
     *
     * @group operatorBusinessDetailsController
     */
    public function testIndexActionWithChangeBusinessTypeNoJs()
    {
        $this->organisationType = OrganisationEntityService::ORG_TYPE_REGISTERED_COMPANY;
        $this->post['operator-business-type']['type'] = OrganisationEntityService::ORG_TYPE_REGISTERED_COMPANY;
        $this->post['operator-business-type']['refresh'] = 'refresh';
        $this->setUpAction(1, true);
        $response = $this->controller->indexAction();
        $this->assertEquals('view', $response);
    }

    /**
     * Test company lookup
     *
     * @group operatorBusinessDetailsController
     */
    public function testIndexActionCompanyLookup()
    {
        $this->organisationType = OrganisationEntityService::ORG_TYPE_REGISTERED_COMPANY;
        $this->post['operator-business-type']['type'] = OrganisationEntityService::ORG_TYPE_REGISTERED_COMPANY;
        $this->post['operator-details']['companyNumber']['submit_lookup_company'] = 'lookup';
        $this->setUpAction(1, true);
        $response = $this->controller->indexAction();
        $this->assertEquals('view', $response);
    }

    /**
     * Test index action with add operator and cancel button pressed
     *
     * @dataProvider organisationTypesProvider
     * @group operatorBusinessDetailsController
     */
    public function testIndexActionWithAddOperatorAndCancelButtonPressed($organisationType)
    {
        $this->organisationType = $organisationType;
        $this->setUpAction(null, true, true);
        $response = $this->controller->indexAction();
        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test index action with edit operator and cancel button pressed
     *
     * @dataProvider organisationTypesProvider
     * @group operatorBusinessDetailsController
     */
    public function testIndexActionWithEditOperatorAndCancelButtonPressed($organisationType)
    {
        $this->organisationType = $organisationType;
        $this->setUpAction(1, true, true);
        $response = $this->controller->indexAction();
        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test index action with nature of business added
     *
     * @group operatorBusinessDetailsController
     */
    public function testIndexActionWithNatureOfBusinessAdded()
    {
        $this->organisationType = OrganisationEntityService::ORG_TYPE_REGISTERED_COMPANY;
        $this->post['operator-business-type']['type'] = OrganisationEntityService::ORG_TYPE_REGISTERED_COMPANY;
        $this->post['operator-details']['natureOfBusiness'] = [1,2];
        $this->setUpAction(1, true);
        $response = $this->controller->indexAction();
        $this->assertEquals('view', $response);
    }
}
