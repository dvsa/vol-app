<?php

/**
 * Addresses Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace OlcsTest\Controller\Application\YourBusiness;

use OlcsTest\Controller\Application\AbstractApplicationControllerTestCase;
use Common\Controller\Application\ApplicationController;

/**
 * Addresses Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AddressesControllerTest extends AbstractApplicationControllerTestCase
{
    protected $controllerName = '\Common\Controller\Application\YourBusiness\AddressesController';
    protected $defaultRestResponse = array();

    private $orgType = ApplicationController::ORG_TYPE_REGISTERED_COMPANY;
    private $licType = ApplicationController::LICENCE_TYPE_STANDARD_NATIONAL;

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

        $form = $this->getFormFromView($response);

        $this->assertInstanceOf('Zend\Form\Fieldset', $form->get('registered_office'));
        $this->assertInstanceOf('Zend\Form\Fieldset', $form->get('establishment'));
    }

    /**
     * Test indexAction
     */
    public function testIndexWithRemovedFieldsetsAction()
    {
        $this->orgType = ApplicationController::ORG_TYPE_OTHER;
        $this->licType = ApplicationController::LICENCE_TYPE_RESTRICTED;

        $this->setUpAction('index');

        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);

        $form = $this->getFormFromView($response);

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
                'countryCode' => array(
                    'id' => 'GB'
                ),
            ];

            return [
                'licence' => [
                    'id' => 10,
                    'version' => 1,
                    'goodsOrPsv' => array(
                        'id' => 'lcat_gv'
                    ),
                    'niFlag' => 0,
                    'licenceType' => array(
                        'id' => $this->licType
                    ),
                    'organisation' => [
                        'id' => 1,
                        'type' => array(
                            'id' => $this->orgType
                        ),
                        'contactDetails' => [
                            [
                                'id' => 1,
                                'version' => 1,
                                'contactType' => array(
                                    'id' => 'ct_reg'
                                ),
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

            return $this->getApplicationCompletionData();
        }
    }
}
