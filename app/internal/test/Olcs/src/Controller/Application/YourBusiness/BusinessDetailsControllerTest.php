<?php

/**
 * BusinessDetails Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace OlcsTest\Controller\Application\YourBusiness;

use OlcsTest\Controller\Application\AbstractApplicationControllerTestCase;
use Common\Controller\Application\ApplicationController;

/**
 * BusinessDetails Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class BusinessDetailsControllerTest extends AbstractApplicationControllerTestCase
{

    protected $controllerName = '\Common\Controller\Application\YourBusiness\BusinessDetailsController';

    protected $defaultRestResponse = [];

    // must be set if you want to use it
    protected $mockOrganisationData = [];

    // defaults are okay in the main for this one...
    protected $mockLicenceData = [
        'licence' => [
            'organisation' => [
                'type' => [
                    'id' => ApplicationController::ORG_TYPE_REGISTERED_COMPANY
                ],
                'companyOrLlpNo' => 12345678,
                'name' => 'A Co Ltd',
                'tradingNames' => []
            ]
        ]
    ];

    protected $mockCompaniesHouseData = [];

    protected $subsidiaryCompanyData = [
        'data' => [
            'id' => 1,
            'version' => 1,
            'name' => 'name',
            'companyNo' => '12345678'
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

    /**
     * Refactored index action tests using a provider
     *
     * @dataProvider indexActionProvider
     */
    public function testIndexActionFromProvider($type, $present = array(), $missing = array())
    {
        $this->setUpAction('index');

        $this->setOrganisationType($type);

        $response = $this->controller->indexAction();

        $fieldset = $this->getFormFromView($response)->get('data');

        foreach ($present as $element) {
            $this->assertTrue($fieldset->has($element));
        }

        foreach ($missing as $element) {
            $this->assertFalse($fieldset->has($element));
        }
    }

    /**
     * Provider for the index action test
     *
     * @return array
     */
    public function indexActionProvider()
    {
        return array(
            array(
                'rc',
                array('name', 'companyNumber', 'tradingNames')
            ),
            array(
                'llp',
                array('name', 'companyNumber', 'tradingNames')
            ),
            array(
                'st',
                array('tradingNames'),
                array('name', 'companyNumber')
            ),
            array(
                'p',
                array('name', 'tradingNames'),
                array('companyNumber')
            ),
            array(
                'pa',
                array('name'),
                array('companyNumber', 'tradingNames')
            )
        );
    }

    public function testIndexActionWithMultipleTradingNamesPresent()
    {
        $this->setUpAction('index');
        $this->setOrganisationType('rc');

        $this->mockLicenceData = [
            'licence' => [
                'organisation' => [
                    'type' => [
                        'id' => ApplicationController::ORG_TYPE_REGISTERED_COMPANY
                    ],
                    'companyOrLlpNo' => 12345678,
                    'name' => 'A Co Ltd',
                    'tradingNames' => [
                        ['name' => 'foo'],
                        ['name' => 'bar'],
                    ]
                ]
            ]
        ];

        $response = $this->controller->indexAction();

        $tradingNames = $this->getFormFromView($response)->get('data')->get('tradingNames')->get('trading_name');

        // always one extra, a blank placeholder
        $this->assertCount(3, $tradingNames->getFieldsets());
    }

    public function testFullSubmitForLimitedCompany()
    {
        $this->setOrganisationType('rc');
        $post = [
            'data' => [
                'type' => null,
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
        $this->setOrganisationType('rc');
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

        $response = $this->controller->indexAction();

        $companyName = $this->getFormFromView($response)->get('data')->get('name');

        $this->assertEquals('A TEST CO LTD', $companyName->getValue());
    }

    public function testFailedCompaniesHouseLookup()
    {
        $this->mockCompaniesHouseData = [
            'Count' => 0
        ];
        $this->setOrganisationType('rc');
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

        $response = $this->controller->indexAction();

        $fieldset = $this->getFormFromView($response)->get('data');

        $companyNumber = $fieldset->get('companyNumber');
        $companyName = $fieldset->get('name');

        $this->assertCount(1, $companyNumber->getMessages());
        $this->assertEquals(null, $companyName->getValue());
    }

    public function testFailedCompaniesHouseLookupTooLong()
    {
        $this->mockCompaniesHouseData = [
            'Count' => 0
        ];
        $this->setOrganisationType('rc');
        $post = [
            'data' => [
                'companyNumber' => [
                    'company_number' => '123456789',
                    'submit_lookup_company' => '',
                ]
            ]
        ];
        $this->setUpAction('index', null, $post);

        $this->controller->setEnabledCsrf(false);

        $response = $this->controller->indexAction();

        $fieldset = $this->getFormFromView($response)->get('data');

        $companyNumber = $fieldset->get('companyNumber');
        $companyName = $fieldset->get('name');

        $this->assertCount(1, $companyNumber->getMessages());
        $this->assertEquals(null, $companyName->getValue());
    }

    /**
     * @group current
     */
    public function testAddAnotherTradingName()
    {
        $this->setOrganisationType('rc');

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

        $isValid = $this->getFormFromView($response)->isValid($post);

        $this->assertTrue($isValid);
        $data = $this->getFormFromView($response)->getData()['data']['tradingNames']['trading_name'];

        $this->assertEquals($expectedTradingNames, $data);
    }

    protected function setOrganisationType($type)
    {
        $this->mockOrganisationData = array(
            'licence' => array(
                'organisation' => array(
                    'type' => array(
                        'id' => 'org_t_' . $type
                    )
                )
            )
        );
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
     * @group acurrent
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
     * @group acurrent
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
     * @group acurrent
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

            $fullBundle = array(
                'children' => array(
                    'licence' => array(
                        'children' => array(
                            'organisation' => array(
                                'children' => array(
                                    'type' => array(
                                        'properties' => array(
                                            'id'
                                        )
                                    ),
                                    'tradingNames' => array(
                                        'properties' => array(
                                            'id',
                                            'name'
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            );

            $orgBundle = [
                'children' => [
                    'licence' => [
                        'children' => [
                            'organisation' => [
                                'properties' => [
                                    'id',
                                    'version'
                                ],
                                'children' => array(
                                    'type' => array(
                                        'properties' => array(
                                            'id'
                                        )
                                    )
                                )
                            ]
                        ]
                    ]
                ]
            ];

            if ($bundle == ApplicationController::$licenceDataBundle) {

                return $this->getLicenceData('goods');
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

            return $this->getApplicationCompletionData();
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
}
