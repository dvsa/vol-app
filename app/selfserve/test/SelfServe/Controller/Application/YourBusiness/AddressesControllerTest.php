<?php

/**
 * Addresses Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace SelfServe\Test\Controller\Application\YourBusiness;

use SelfServe\Test\Controller\Application\AbstractApplicationControllerTestCase;
use SelfServe\Controller\Application\ApplicationController;
use SelfServe\Controller\Application\YourBusiness\AddressesController;

/**
 * Addresses Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AddressesControllerTest extends AbstractApplicationControllerTestCase
{

    protected $controllerName = '\SelfServe\Controller\Application\YourBusiness\AddressesController';
    protected $defaultRestResponse = array();

    private $orgType = 'org_type.lc';
    private $licType = 'standard-national';

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

        $form = $this->getFormFromResponse($response);

        $this->assertInstanceOf('Zend\Form\Fieldset', $form->get('registered_office'));
        $this->assertInstanceOf('Zend\Form\Fieldset', $form->get('establishment'));
    }

    /**
     * Test indexAction
     */
    public function testIndexWithRemovedFieldsetsAction()
    {
        $this->orgType = 'org_type.o';
        $this->licType = 'restricted';

        $this->setUpAction('index');

        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);

        $form = $this->getFormFromResponse($response);

        $this->assertFalse($form->has('registered_office'));
        $this->assertFalse($form->has('establishment'));
    }

    /**
     * Test indexAction with submit
     *
     */
    public function testIndexActionWithSubmit()
    {
        $address = [
            'addressLine1' => 'addressLine1',
            'postcode' => 'LS8 4DW',
            'town' => 'Leeds',
            'countryCode' => 'GB',
        ];

        $post = [
            'contact' => [
                'phone-validator' => true,
                'email' => 'test@mail.com',
                'phone_business' => '123123123',
            ],
            'correspondence_address' => $address,
            'registered_office_address' => $address,
            'establishment_address' => $address,
        ];

        $this->setUpAction('index', null, $post);

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexAction with submit
     *
     */
    public function testIndexActionWithSubmitWithDeletedPhone()
    {
        $address = [
            'addressLine1' => 'addressLine1',
            'postcode' => 'LS8 4DW',
            'town' => 'Leeds',
            'countryCode' => 'GB',
        ];

        $post = [
            'contact' => [
                'phone-validator' => true,
                'email' => 'test@mail.com',
                'phone_business' => '123123123',
                'phone_home' => '',
                'phone_home_id' => '1',
            ],
            'correspondence_address' => $address,
            'registered_office_address' => $address,
            'establishment_address' => $address,
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

            $address = [
                'id' => 1,
                'version' => 1,
                'addressLine1' => 'addressLine1',
                'postcode' => 'LS8 4DW',
                'town' => 'Leeds',
                'countryCode' => 'GB',
            ];

            return [
                'licence' => [
                    'id' => 10,
                    'version' => 1,
                    'goodsOrPsv' => 'goods',
                    'niFlag' => 0,
                    'licenceType' => $this->licType,
                    'organisation' => [
                        'id' => 1,
                        'type' => $this->orgType,
                        'contactDetails' => [
                            [
                                'id' => 1,
                                'version' => 1,
                                'contactType' => 'ct_reg',
                                'address' => $address
                            ]
                        ]
                    ],
                    'contactDetails' => [
                        [
                            'id' => 1,
                            'version' => 1,
                            'contactType' => 'ct_corr',
                            'emailAddress' => 'dummy@mail.com',
                            'address' => $address,
                            'phoneContacts' => [
                                [
                                    'id' => 1,
                                    'version' => 1,
                                    'type' => 'business',
                                    'number' => 22091986,
                                ],
                                [
                                    'id' => 2,
                                    'version' => 1,
                                    'type' => 'home',
                                    'number' => 22091986,
                                ],
                                [
                                    'id' => 3,
                                    'version' => 1,
                                    'type' => 'mobile',
                                    'number' => 22091986,
                                ]
                            ]
                        ],
                        [
                            'id' => 1,
                            'version' => 1,
                            'contactType' => 'establishment',
                            'emailAddress' => 'dummy@mail.com',
                            'address' => $address,
                        ]
                    ]
                ]
            ];
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
    }
}
