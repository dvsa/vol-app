<?php

/**
 * BusinessDetails Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace SelfServe\Test\Controller\Application\YourBusiness;

use SelfServe\Test\Controller\Application\AbstractApplicationControllerTestCase;
use SelfServe\Controller\Application\ApplicationController;

/**
 * BusinessDetails Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class BusinessDetailsControllerTest extends AbstractApplicationControllerTestCase
{

    protected $controllerName = '\SelfServe\Controller\Application\YourBusiness\BusinessDetailsController';
    protected $defaultRestResponse = [];
    protected $mockOrganisationData = [];

    /**
     * Test back button
     */
    public function testBackButton()
    {
        $this->setUpAction('index', null, array('form-actions' => array('back' => 'Back')));

        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    public function testIndexActionShowsCorrectBackLink()
    {
        $this->setUpAction('index');
        $this->setOrganisationType('lc');   // not important for this test

        $response = $this->controller->indexAction();
        $fieldset = $this->getFormFromResponse($response)->get('data');

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);

        $this->assertEquals(
            '/application/1/your-business/business-type/',
            $fieldset->get('edit_business_type')->getValue()
        );
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

    /**
     * Test indexAction with submit
     */
    public function testIndexActionWithSubmit()
    {
        $this->markTestIncomplete('not refactored yet');
        $post = [
            'data' => [
                // @TODO: various scenarios
            ]
        ];
        $this->setUpAction('index', null, $post);

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->indexAction();

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

                return [
                    'licence' => [
                        'organisation' => [
                            'organisationType' => 'org_type.lc',
                            'registeredCompanyNumber' => 12345678,
                            'name' => 'A Co Ltd'
                        ],
                        'tradingNames' => []
                    ]
                ];
            }

            if ($bundle == $orgBundle) {
                return $this->mockOrganisationData;
            }
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
}
