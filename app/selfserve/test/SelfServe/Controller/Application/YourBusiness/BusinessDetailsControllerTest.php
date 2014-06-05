<?php

/**
 * BusinessDetails Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace SelfServe\Test\Controller\Application\YourBusiness;

use SelfServe\Test\Controller\Application\AbstractApplicationControllerTestCase;
use SelfServe\Controller\Application\ApplicationController;

/**
 * BusinessDetails Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class BusinessDetailsControllerTest extends AbstractApplicationControllerTestCase
{

    protected $controllerName = '\SelfServe\Controller\Application\YourBusiness\BusinessDetailsController';

    protected $defaultRestResponse = [];

    // must be set if you want to use it
    protected $mockOrganisationData = [];

    // defaults are okay in the main for this one...
    protected $mockLicenceData = [
        'licence' => [
            'organisation' => [
                'organisationType' => 'org_type.lc',
                'registeredCompanyNumber' => 12345678,
                'name' => 'A Co Ltd'
            ],
            'tradingNames' => []
        ]
    ];

    protected $mockCompaniesHouseData = [];

    protected $subsidiaryCompanyData = [
        'data' => [
            'id' => 1,
            'version' => 1,
            'name' => 'name',
            'companyNo' => ['company_number' => '12345678']
        ]
    ];

    /**
     * Test back button
     */
    public function testBackButton()
    {
        $this->setUpAction('index', null, array('form-actions' => array('back' => 'Back')));

        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    public function testIndexActionWithLimitedCompanyShowsFullForm()
    {
        $this->assertFormElements('lc', ['name', 'companyNumber', 'tradingNames']);
    }


    public function testIndexActionWithLlpShowsFullForm()
    {
        $this->assertFormElements('llp', ['name', 'companyNumber', 'tradingNames']);
    }

    public function testIndexActionWithSoleTraderShowsLimitedForm()
    {
        $this->assertFormElements('st', ['tradingNames'], ['name', 'companyNumber']);
    }

    public function testIndexActionWithPartnershipShowsLimitedForm()
    {
        $this->assertFormElements('p', ['name', 'tradingNames'], ['companyNumber']);
    }

    public function testIndexActionWithOtherShowsLimitedForm()
    {
        $this->assertFormElements('o', ['name'], ['companyNumber', 'tradingNames']);
    }

    public function testIndexActionWithMultipleTradingNamesPresent()
    {
        $this->setUpAction('index');
        $this->setOrganisationType('lc');

        $this->mockLicenceData = [
            'licence' => [
                'organisation' => [
                    'organisationType' => 'org_type.lc',
                    'registeredCompanyNumber' => 12345678,
                    'name' => 'A Co Ltd'
                ],
                'tradingNames' => [
                    ['tradingName' => 'foo'],
                    ['tradingName' => 'bar'],
                ]
            ]
        ];

        $tradingNames = $this->getFormFromResponse(
            $this->controller->indexAction()
        )->get('data')->get('tradingNames')->get('trading_name');

        // always one extra, a blank placeholder
        $this->assertCount(3, $tradingNames->getFieldsets());
    }

    public function testFullSubmitForLimitedCompany()
    {
        $this->setOrganisationType('lc');
        $post = [
            'data' => [
                'organisationType' => null,
                'companyNumber' => [
                    'company_number' => '12345678',
                ],
                'name' => 'Company Ltd',
                'tradingNames' => [
                    'trading_name' => [
                        ['text' => 'A Name'],
                        ['text' => '']
                    ]
                ]
            ]
        ];
        $this->setUpAction('index', null, $post);

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    public function testSuccessfulCompaniesHouseLookupPopulatesCompanyName()
    {
        $this->mockCompaniesHouseData = [
            'Count' => 1,
            'Results' => [
                ['CompanyName' => 'A TEST CO LTD']
            ]
        ];
        $this->setOrganisationType('lc');
        $post = [
            'data' => [
                'companyNumber' => [
                    'company_number' => '12345678',
                    'submit_lookup_company' => '',
                ]
            ]
        ];
        $this->setUpAction('index', null, $post);

        $this->controller->setEnabledCsrf(false);
        $this->controller->indexAction();

        $companyName = $this->getFormFromResponse(
            $this->controller->indexAction()
        )->get('data')->get('name');

        $this->assertEquals('A TEST CO LTD', $companyName->getValue());
    }

    public function testFailedCompaniesHouseLookup()
    {
        $this->mockCompaniesHouseData = [
            'Count' => 0
        ];
        $this->setOrganisationType('lc');
        $post = [
            'data' => [
                'companyNumber' => [
                    'company_number' => '12345678',
                    'submit_lookup_company' => '',
                ]
            ]
        ];
        $this->setUpAction('index', null, $post);

        $this->controller->setEnabledCsrf(false);

        $fieldset = $this->getFormFromResponse(
            $this->controller->indexAction()
        )->get('data');

        $companyNumber = $fieldset->get('companyNumber');
        $companyName = $fieldset->get('name');

        $this->assertCount(1, $companyNumber->getMessages());
        $this->assertEquals(null, $companyName->getValue());
    }


    public function testAddAnotherTradingName()
    {
        $this->setOrganisationType('lc');

        $testTradingNames = [
            ['text' => 'string one'],
            ['text' => 'string two'],
            ['text' => ''],
        ];

        $expectedTradingNames = [
            ['text' => 'string one'],
            ['text' => 'string two'],
            ['text' => ''],
        ];

        $post = [
            'data' => [
                'tradingNames' => [
                    'trading_name' => $testTradingNames,
                    'submit_add_trading_name' => '',
                ]
            ]
        ];
        $this->setUpAction('index', null, $post);

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->indexAction();

        $isValid = $this->getFormFromResponse($response)->isValid($post);

        $this->assertTrue($isValid);
        $data = $this->getFormFromResponse($response)->getData()['data']['tradingNames']['trading_name'];

        $this->assertEquals($expectedTradingNames, $data);
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
        if ($service == 'Application' && $method == 'GET') {

            $fullBundle = [
                'children' => [
                    'licence' => [
                        'children' => [
                            'organisation',
                            'tradingNames',
                        ]
                    ],
                ],
            ];
            $orgBundle = [
                'children' => [
                    'licence' => [
                        'children' => [
                            'organisation' => [
                                'properties' => ['id', 'version', 'organisationType']
                            ]
                        ]
                    ]
                ]
            ];

            if ($bundle == ApplicationController::$licenceDataBundle) {

                return array(
                    'licence' => array(
                        'id' => 10,
                        'version' => 1,
                        'goodsOrPsv' => 'goods',
                        'niFlag' => 0,
                        'licenceType' => 'standard-national'
                    )
                );
            }

            if ($bundle == $fullBundle) {

                return $this->mockLicenceData;
            }

            if ($bundle == $orgBundle) {

                return $this->mockOrganisationData;
            }
        }

        if ($service === 'CompaniesHouse') {

            return $this->mockCompaniesHouseData;
        }

        if ($service == 'ApplicationCompletion' && $method == 'GET') {

            return array(
                'Count' => 1,
                'Results' => array(
                    array(
                        'id' => 1,
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

        if ($service == 'CompanySubsidiary' && $method == 'GET') {
            $companySubsidiariesBundle = [
                'properties' => [
                    'id',
                    'version',
                    'name',
                    'companyNo'
                ]
            ];

            if ($bundle == $companySubsidiariesBundle) {
                return array(
                    'id' => 1,
                    'version' => 1,
                    'name' => 'name',
                    'companyNo' => '12345678'
                );
            }
        }

    }

    protected function setOrganisationType($type)
    {
        $this->mockOrganisationData = [
            'licence' => [
                'organisation' => [
                    'organisationType' => 'org_type.' . $type
                ]
            ]
        ];
    }

    protected function assertFormElements($type, $present = array(), $missing = array())
    {
        $this->setUpAction('index');
        $this->setOrganisationType($type);

        $fieldset = $this->getFormFromResponse(
            $this->controller->indexAction()
        )->get('data');

        foreach ($present as $element) {
            $this->assertTrue($fieldset->has($element));
        }

        foreach ($missing as $element) {
            $this->assertFalse($fieldset->has($element));
        }
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
            'edit', 1, $this->subsidiaryCompanyData
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
            'add', null, $this->subsidiaryCompanyData
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
        $data = array_merge(
            $this->subsidiaryCompanyData,
            array('form-actions' => array('addAnother' => 'Add another'))
        );
        $this->setUpAction(
            'add', null, $data
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->addAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }
}
