<?php

/**
 * Safety Controller Test
 *
 * @author Jessica Rowbottom <jess.rowbottom@valtech.co.uk>
 */

namespace SelfServe\Test\Controller\Application\VehicleSafety;

use SelfServe\Test\Controller\Application\AbstractApplicationControllerTestCase;
use SelfServe\Controller\Application\ApplicationController;

/**
 * Safety Controller Test
 *
 * @author Jessica Rowbottom <jess.rowbottom@valtech.co.uk>
 */
class UndertakingsControllerTest extends AbstractApplicationControllerTestCase
{

    protected $controllerName = '\SelfServe\Controller\Application\VehicleSafety\UndertakingsController';
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
    public function testIndexAction($goodsOrPsv)
    {
        $this->setUpAction('index');

        $this->goodsOrPsv = $goodsOrPsv;

        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);

        $form = $this->getFormFromResponse($response);
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
                        'organisationType' => 'org_type.lc'
                    )
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

        if ($service == 'Vehicle' && $method == 'POST') {
            return array('id' => 1);
        }

        if ($service == 'LicenceVehicle' && $method == 'POST') {
            return array('id' => 1);
        }

        $tableDataBundle = array(
            'properties' => null,
            'children' => array(
                'licenceVehicles' => array(
                    'properties' => null,
                    'children' => array(
                        'vehicle' => array(
                            'properties' => array(
                                'id',
                                'vrm',
                                'platedWeight'
                            )
                        )
                    )
                )
            )
        );

        if ($service == 'Licence' && $method == 'GET' && $bundle == $tableDataBundle) {
            return array(
                'licenceVehicles' => array(
                    array(
                        'vehicle' => array(
                            'id' => 1,
                            'vrm' => 'AB12 ABG',
                            'platedWeight' => 100
                        )
                    ),
                    array(
                        'vehicle' => array(
                            'id' => 2,
                            'vrm' => 'DB12 ABG',
                            'platedWeight' => 150
                        )
                    )
                )
            );
        }

        if ($service == 'Vehicle' && $method == 'GET') {
            return array(
                'id' => 1,
                'version' => 1,
                'vrm' => 'AB12 ABC',
                'platedWeight' => 100
            );
        }

        if ($service == 'LicenceVehicle' && $method == 'GET') {
            return array(
                'Count' => 1,
                'Results' => array(
                    array('id' => 1)
                )
            );
        }
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
}