<?php

/**
 * Safety Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace SelfServe\Test\Controller\Application\VehicleSafety;

use SelfServe\Test\Controller\Application\AbstractApplicationControllerTestCase;
use SelfServe\Controller\Application\ApplicationController;

/**
 * Safety Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SafetyControllerTest extends AbstractApplicationControllerTestCase
{

    protected $controllerName = '\SelfServe\Controller\Application\VehicleSafety\SafetyController';
    protected $defaultRestResponse = array();
    private $goodsOrPsv;

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
     *
     * @dataProvider psvProvider
     */
    public function testIndexAction($goodsOrPsv, $hasTrailers)
    {
        $this->setUpAction('index');

        $this->goodsOrPsv = $goodsOrPsv;

        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);

        $form = $this->getFormFromResponse($response);

        $this->assertEquals($hasTrailers, $form->get('licence')->has('safetyInsTrailers'));

        $label = $form->get('licence')->get('safetyInsVaries')->getLabel();

        $this->assertEquals(!$hasTrailers, (boolean) strstr($label, 'psv'));

        $table = $form->get('table')->get('table')->getTable();

        $emptyMessage = $table->getVariable('empty_message');

        $this->assertEquals(!$hasTrailers, (boolean) strstr($emptyMessage, 'psv'));
    }

    /**
     * Test indexAction With submit
     */
    public function testIndexActionWithSubmit()
    {
        $this->goodsOrPsv = 'goods';

        $this->setUpAction(
            'index', null, array(
            'licence' => array(
                'safetyInsVehicles' => 'inspection_interval_vehicle.1',
                'safetyInsTrailers' => 'inspection_interval_trailer.1',
                'safetyInsVaries' => 'Y',
                'tachographIns' => 'tachograph_analyser.2'
            ),
            'table' => array(
                'rows' => 1
            ),
            'application' => array(
                'id' => 1,
                'version' => 1,
                'safetyConfirmation' => '1'
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

        $this->goodsOrPsv = 'goods';

        $response = $this->controller->addAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test addAction with submit
     */
    public function testAddActionWithSubmit()
    {
        $this->setUpAction(
            'add', null, array(
            'data' => array(
                'isExternal' => 'Y'
            ),
            'contactDetails' => array(
                'fao' => 'Someone'
            ),
            'address' => array(
                'id' => '',
                'version' => '',
                'addressLine1' => 'Address 1',
                'city' => 'City',
                'country' => 'country.GB',
                'postcode' => 'AB1 1BA'
            )
            )
        );

        $this->goodsOrPsv = 'goods';

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
                    'isExternal' => 'Y'
                ),
                'contactDetails' => array(
                    'fao' => 'Someone'
                ),
                'address' => array(
                    'id' => '',
                    'version' => '',
                    'addressLine1' => 'Address 1',
                    'city' => 'City',
                    'country' => 'country.GB',
                    'postcode' => 'AB1 1BA'
                ),
                'form-actions' => array(
                    'addAnother' => 'Add another'
                )
            )
        );

        $this->goodsOrPsv = 'goods';

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->addAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test addAction with submit with failures
     *
     * @expectedException Exception
     */
    public function testAddActionWithSubmitWithFailure()
    {
        $this->setUpAction(
            'add', null, array(
            'data' => array(
                'isExternal' => 'Y'
            ),
            'contactDetails' => array(
                'fao' => 'Someone'
            ),
            'address' => array(
                'id' => '',
                'version' => '',
                'addressLine1' => 'Address 1',
                'city' => 'City',
                'country' => 'country.GB',
                'postcode' => 'AB1 1BA'
            )
            )
        );

        $this->setRestResponse('ContactDetails', 'POST', '');

        $this->goodsOrPsv = 'goods';

        $this->controller->setEnabledCsrf(false);
        $this->controller->addAction();
    }

    /**
     * Test editAction
     */
    public function testEditAction()
    {
        $this->setUpAction('edit', 1);

        $this->goodsOrPsv = 'goods';

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
                    'isExternal' => 'Y'
                ),
                'contactDetails' => array(
                    'id' => 3,
                    'version' => 1,
                    'fao' => 'Someone'
                ),
                'address' => array(
                    'id' => 5,
                    'version' => 1,
                    'addressLine1' => 'Address 1',
                    'city' => 'City',
                    'country' => 'country.GB',
                    'postcode' => 'AB1 1BA'
                )
            )
        );

        $this->goodsOrPsv = 'goods';

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

        $this->goodsOrPsv = 'goods';

        $response = $this->controller->deleteAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test deleteAction without id
     */
    public function testDeleteActionWithoutId()
    {
        $this->setUpAction('delete');

        $this->goodsOrPsv = 'goods';

        $response = $this->controller->deleteAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Psv provider
     *
     * @return array
     */
    public function psvProvider()
    {
        return array(
            array('psv', false),
            array('goods', true)
        );
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
                    'goodsOrPsv' => $this->goodsOrPsv,
                    'niFlag' => 0,
                    'licenceType' => 'standard-national'
                )
            );
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

        $dataBundle = array(
            'properties' => array(
                'id',
                'version',
                'safetyConfirmation'
            ),
            'children' => array(
                'licence' => array(
                    'properties' => array(
                        'id',
                        'version',
                        'safetyInsVehicles',
                        'safetyInsTrailers',
                        'safetyInsVaries',
                        'tachographIns',
                        'tachographInsName'
                    )
                ),
                'workshops' => array(
                    'properties' => array(
                        'id',
                        'isExternal'
                    ),
                    'children' => array(
                        'contactDetails' => array(
                            'properties' => array(
                                'fao'
                            ),
                            'children' => array(
                                'address' => array(
                                    'properties' => array(
                                        'addressLine1',
                                        'addressLine2',
                                        'addressLine3',
                                        'addressLine4',
                                        'city',
                                        'country',
                                        'postcode'
                                    )
                                )
                            )
                        )
                    )
                )
            )
        );

        if ($service == 'Licence' && $method == 'POST') {
            return array('id' => 1);
        }

        if ($service == 'ContactDetails' && $method == 'POST') {
            return array('id' => 7);
        }

        if ($service == 'Workshop' && $method == 'POST') {
            return array('id' => 6);
        }

        if ($service == 'Application' && $method == 'GET' && $bundle == $dataBundle) {
            return array(
                'id' => 1,
                'version' => 1,
                'safetyConfirmation' => 1,
                'licence' => array(
                    'id' => 2,
                    'version' => 2,
                    'safetyInsVehicles' => null,
                    'safetyInsTrailers' => null,
                    'safetyInsVaries' => null,
                    'tachographIns' => null,
                    'tachographInsName' => null
                ),
                'workshops' => array(
                    array(
                        'id' => 1,
                        'isExternal' => 1,
                        'contactDetails' => array(
                            'fao' => 'Someone',
                            'address' => array(
                                'addressLine1' => 'Address 1',
                                'addressLine2' => 'Address 2',
                                'addressLine3' => 'Address 3',
                                'addressLine4' => 'Address 4',
                                'city' => 'City',
                                'country' => 'GB',
                                'postcode' => 'AB1 1AB'
                            )
                        )
                    )
                )
            );
        }

        $actionDataBundle = array(
            'properties' => array(
                'id',
                'version',
                'isExternal'
            ),
            'children' => array(
                'contactDetails' => array(
                    'properties' => array(
                        'id',
                        'version',
                        'fao'
                    ),
                    'children' => array(
                        'address' => array(
                            'properties' => array(
                                'id',
                                'version',
                                'addressLine1',
                                'addressLine2',
                                'addressLine3',
                                'addressLine4',
                                'city',
                                'country',
                                'postcode'
                            )
                        )
                    )
                )
            )
        );

        if ($service == 'Workshop' && $method == 'GET' && $bundle == $actionDataBundle) {

            return array(
                'id' => 7,
                'version' => 1,
                'isExternal' => 1,
                'contactDetails' => array(
                    'id' => 6,
                    'version' => 2,
                    'fao' => 'Someone',
                    'address' => array(
                        'id' => 100,
                        'version' => 1,
                        'addressLine1' => 'Address Line 1',
                        'addressLine2' => '',
                        'addressLine3' => '',
                        'addressLine4' => '',
                        'city' => 'City',
                        'country' => 'GB',
                        'postcode' => 'AB1 1AB'
                    )
                )
            );
        }

        var_dump($service, $method, $bundle, $data);
        exit;
    }
}
