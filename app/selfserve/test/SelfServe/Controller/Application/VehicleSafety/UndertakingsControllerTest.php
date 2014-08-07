<?php

/**
 * Undertakings Controller Test
 *
 * @author Jessica Rowbottom <jess.rowbottom@valtech.co.uk>
 */

namespace SelfServe\Test\Controller\Application\VehicleSafety;

use SelfServe\Test\Controller\Application\AbstractApplicationControllerTestCase;
use SelfServe\Controller\Application\ApplicationController;

/**
 * Undertakings Controller Test
 *
 * @author Jessica Rowbottom <jess.rowbottom@valtech.co.uk>
 */
class UndertakingsControllerTest extends AbstractApplicationControllerTestCase
{

    protected $controllerName = '\SelfServe\Controller\Application\VehicleSafety\UndertakingsController';
    protected $defaultRestResponse = array();
    private $taDataResponse;

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
        $this->taDataResponse=array(
                'id' => 1,
                'version' => 1,
                'status' => 'test.status',
                'psvOperateSmallVehicles' => null,
                'psvSmallVehicleNotes' => "",
                'psvSmallVehicleConfirmation' => null,
                'psvNoSmallVehiclesConfirmation' => null,
                'psvLimousines' => null,
                'psvNoLimousineConfirmation' => null,
                'psvOnlyLimousinesConfirmation' => null,
                'licence' => array(
                    'id' => 10,
                    'version' => 1,
                    'goodsOrPsv' => 'psv',
                    'niFlag' => 0,
                    'licenceType' => 'standard-national',
                    'organisation' => array(
                        'organisationType' => 'org_type.lc'
                    )
                ),
                'trafficArea' => array(
                    'id' => 5,
                    'applyScottishRules' => false
                ),
                'totAuthSmallVehicles' => 1,
                'totAuthMediumVehicles' => 1,
                'totAuthLargeVehicles' => 0
            );

        $this->setUpAction('index');

        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);

        $form = $this->getFormFromResponse($response);
    }

    /**
     * Test indexAction
     */
    public function testIndexActionWithCase1ShowsFullForm()
    {
        $taDataResponse=array(
                'id' => 1,
                'version' => 1,
                'status' => 'test.status',
                'psvOperateSmallVehicles' => null,
                'psvSmallVehicleNotes' => "",
                'psvSmallVehicleConfirmation' => null,
                'psvNoSmallVehiclesConfirmation' => null,
                'psvLimousines' => null,
                'psvNoLimousineConfirmation' => null,
                'psvOnlyLimousinesConfirmation' => null,
                'licence' => array(
                    'id' => 10,
                    'version' => 1,
                    'goodsOrPsv' => 'psv',
                    'niFlag' => 0,
                    'licenceType' => 'standard-national',
                    'organisation' => array(
                        'organisationType' => 'org_type.lc'
                    )
                ),
                'trafficArea' => array(
                    'id' => 5,
                    'applyScottishRules' => false
                ),
                'totAuthSmallVehicles' => 1,
                'totAuthMediumVehicles' => 0,
                'totAuthLargeVehicles' => 0
            );

        $presentFields=array(
                'smallVehiclesIntention' => Array(
                    'psvOperateSmallVehicles',              // 15b[i]
                    'psvSmallVehicleNotes'                  // 15b[ii]
                ),
                'smallVehiclesUndertakings' => Array(       // 15c/d
                    'psvSmallVehicleConfirmation'
                ),
                'limousinesNoveltyVehicles' => Array(
                    'psvLimousines',                        // 15f[i]
                    'psvNoLimousineConfirmation'            // 15f[ii]
                )
        );

        $missingFields=array(
                'nineOrMore' => Array(                      // 15e
                    'psvNoSmallVehiclesConfirmation'
                ),
                'limousinesNoveltyVehicles' => Array(
                    'psvOnlyLimousinesConfirmation',        // 15g
                )
        );

        $this->assertFormElements($taDataResponse, $presentFields);
    }

    /**
     * Test indexAction
     */
    public function testIndexActionWithCase2ShowsFullForm()
    {
        $taDataResponse=array(
                'id' => 1,
                'version' => 1,
                'status' => 'test.status',
                'psvOperateSmallVehicles' => null,
                'psvSmallVehicleNotes' => "",
                'psvSmallVehicleConfirmation' => null,
                'psvNoSmallVehiclesConfirmation' => null,
                'psvLimousines' => null,
                'psvNoLimousineConfirmation' => null,
                'psvOnlyLimousinesConfirmation' => null,
                'licence' => array(
                    'id' => 10,
                    'version' => 1,
                    'goodsOrPsv' => 'psv',
                    'niFlag' => 0,
                    'licenceType' => 'standard-national',
                    'organisation' => array(
                        'organisationType' => 'org_type.lc'
                    )
                ),
                'trafficArea' => array(
                    'id' => 8,
                    'applyScottishRules' => true
                ),
                'totAuthSmallVehicles' => 1,
                'totAuthMediumVehicles' => 0,
                'totAuthLargeVehicles' => 0
            );

        $presentFields=array(
                'smallVehiclesUndertakings' => Array(       // 15c/d
                    'psvSmallVehicleConfirmation'
                ),
                'limousinesNoveltyVehicles' => Array(
                    'psvLimousines',                        // 15f[i]
                    'psvNoLimousineConfirmation'            // 15f[ii]
                )
        );

        $missingFields=array(
                'smallVehiclesIntention' => Array(
                    'psvOperateSmallVehicles',              // 15b[i]
                    'psvSmallVehicleNotes'                  // 15b[ii]
                ),
                'nineOrMore' => Array(                      // 15e
                    'psvNoSmallVehiclesConfirmation'
                ),
                'limousinesNoveltyVehicles' => Array(
                    'psvOnlyLimousinesConfirmation',        // 15g
                )
        );

        $this->assertFormElements($taDataResponse, $presentFields, $missingFields);
    }

    /**
     * Test indexAction
     */
    public function testIndexActionWithCase3ShowsFullForm()
    {
        $taDataResponse=array(
                'id' => 1,
                'version' => 1,
                'status' => 'test.status',
                'psvOperateSmallVehicles' => null,
                'psvSmallVehicleNotes' => "",
                'psvSmallVehicleConfirmation' => null,
                'psvNoSmallVehiclesConfirmation' => null,
                'psvLimousines' => null,
                'psvNoLimousineConfirmation' => null,
                'psvOnlyLimousinesConfirmation' => null,
                'licence' => array(
                    'id' => 10,
                    'version' => 1,
                    'goodsOrPsv' => 'psv',
                    'niFlag' => 0,
                    'licenceType' => 'standard-national',
                    'organisation' => array(
                        'organisationType' => 'org_type.lc'
                    )
                ),
                'trafficArea' => array(
                    'id' => 5,
                    'applyScottishRules' => false
                ),
                'totAuthSmallVehicles' => 0,
                'totAuthMediumVehicles' => 1,
                'totAuthLargeVehicles' => 0
            );

        $presentFields=array(
                'nineOrMore' => Array(                      // 15e
                    'psvNoSmallVehiclesConfirmation'
                ),
                'limousinesNoveltyVehicles' => Array(
                    'psvLimousines',                        // 15f[i]
                    'psvNoLimousineConfirmation'            // 15f[ii]
                ),
                'limousinesNoveltyVehicles' => Array(
                    'psvOnlyLimousinesConfirmation',        // 15g
                )
        );

        $missingFields=array(
                'smallVehiclesIntention' => Array(
                    'psvOperateSmallVehicles',              // 15b[i]
                    'psvSmallVehicleNotes'                  // 15b[ii]
                ),
                'smallVehiclesUndertakings' => Array(       // 15c/d
                    'psvSmallVehicleConfirmation'
                ),
        );

        $this->assertFormElements($taDataResponse, $presentFields, $missingFields);
    }


    /**
     * Test indexAction
     */
    public function testIndexActionWithCase3AndNullsShowsFullForm()
    {
        $taDataResponse=array(
                'id' => 1,
                'version' => 1,
                'status' => 'test.status',
                'psvOperateSmallVehicles' => null,
                'psvSmallVehicleNotes' => "",
                'psvSmallVehicleConfirmation' => null,
                'psvNoSmallVehiclesConfirmation' => null,
                'psvLimousines' => null,
                'psvNoLimousineConfirmation' => null,
                'psvOnlyLimousinesConfirmation' => null,
                'licence' => array(
                    'id' => 10,
                    'version' => 1,
                    'goodsOrPsv' => 'psv',
                    'niFlag' => 0,
                    'licenceType' => 'standard-national',
                    'organisation' => array(
                        'organisationType' => 'org_type.lc'
                    )
                ),
                'trafficArea' => array(
                    'id' => 5,
                    'applyScottishRules' => null
                ),
                'totAuthSmallVehicles' => null,
                'totAuthMediumVehicles' => 1,
                'totAuthLargeVehicles' => null
            );

        $presentFields=array(
                'nineOrMore' => Array(                      // 15e
                    'psvNoSmallVehiclesConfirmation'
                ),
                'limousinesNoveltyVehicles' => Array(
                    'psvLimousines',                        // 15f[i]
                    'psvNoLimousineConfirmation'            // 15f[ii]
                ),
                'limousinesNoveltyVehicles' => Array(
                    'psvOnlyLimousinesConfirmation',        // 15g
                )
        );

        $missingFields=array(
                'smallVehiclesIntention' => Array(
                    'psvOperateSmallVehicles',              // 15b[i]
                    'psvSmallVehicleNotes'                  // 15b[ii]
                ),
                'smallVehiclesUndertakings' => Array(       // 15c/d
                    'psvSmallVehicleConfirmation'
                ),
        );

        $this->assertFormElements($taDataResponse, $presentFields, $missingFields);
    }

    /**
     * Test indexAction
     */
    public function testIndexActionWithCase4ShowsFullForm()
    {
        $taDataResponse=array(
                'id' => 1,
                'version' => 1,
                'status' => 'test.status',
                'psvOperateSmallVehicles' => null,
                'psvSmallVehicleNotes' => "",
                'psvSmallVehicleConfirmation' => null,
                'psvNoSmallVehiclesConfirmation' => null,
                'psvLimousines' => null,
                'psvNoLimousineConfirmation' => null,
                'psvOnlyLimousinesConfirmation' => null,
                'licence' => array(
                    'id' => 10,
                    'version' => 1,
                    'goodsOrPsv' => 'psv',
                    'niFlag' => 0,
                    'licenceType' => 'standard-national',
                    'organisation' => array(
                        'organisationType' => 'org_type.lc'
                    )
                ),
                'trafficArea' => array(
                    'id' => 5,
                    'applyScottishRules' => false
                ),
                'totAuthSmallVehicles' => 1,
                'totAuthMediumVehicles' => 1,
                'totAuthLargeVehicles' => 1
            );

        $presentFields=array(
                'smallVehiclesIntention' => Array(
                    'psvOperateSmallVehicles',              // 15b[i]
                    'psvSmallVehicleNotes'                  // 15b[ii]
                ),
                'smallVehiclesUndertakings' => Array(       // 15c/d
                    'psvSmallVehicleConfirmation'
                ),
                'limousinesNoveltyVehicles' => Array(
                    'psvLimousines',                        // 15f[i]
                    'psvNoLimousineConfirmation'           // 15f[ii]
                ),
                'limousinesNoveltyVehicles' => Array(
                    'psvOnlyLimousinesConfirmation',        // 15g
                )
        );

        $missingFields=array(
                'nineOrMore' => Array(                      // 15e
                    'psvNoSmallVehiclesConfirmation'
                ),
        );

        $this->assertFormElements($taDataResponse, $presentFields, $missingFields);
    }

    /**
     * Test indexAction
     */
    public function testIndexActionWithCase5ShowsFullForm()
    {
        $taDataResponse=array(
                'id' => 1,
                'version' => 1,
                'status' => 'test.status',
                'psvOperateSmallVehicles' => null,
                'psvSmallVehicleNotes' => "",
                'psvSmallVehicleConfirmation' => null,
                'psvNoSmallVehiclesConfirmation' => null,
                'psvLimousines' => null,
                'psvNoLimousineConfirmation' => null,
                'psvOnlyLimousinesConfirmation' => null,
                'licence' => array(
                    'id' => 10,
                    'version' => 1,
                    'goodsOrPsv' => 'psv',
                    'niFlag' => 0,
                    'licenceType' => 'standard-national',
                    'organisation' => array(
                        'organisationType' => 'org_type.lc'
                    )
                ),
                'trafficArea' => array(
                    'id' => 5,
                    'applyScottishRules' => true
                ),
                'totAuthSmallVehicles' => 1,
                'totAuthMediumVehicles' => 1,
                'totAuthLargeVehicles' => 1
            );

        $presentFields=array(
                'smallVehiclesUndertakings' => Array(       // 15c/d
                    'psvSmallVehicleConfirmation'
                ),
                'limousinesNoveltyVehicles' => Array(
                    'psvLimousines',                        // 15f[i]
                    'psvNoLimousineConfirmation'           // 15f[ii]
                ),
                'limousinesNoveltyVehicles' => Array(
                   'psvOnlyLimousinesConfirmation',         // 15g
                )
        );

        $missingFields=array(
                'smallVehiclesIntention' => Array(
                    'psvOperateSmallVehicles',              // 15b[i]
                    'psvSmallVehicleNotes'                  // 15b[ii]
                ),
                'nineOrMore' => Array(                      // 15e
                    'psvNoSmallVehiclesConfirmation'
                ),
        );

        $this->assertFormElements($taDataResponse, $presentFields, $missingFields);
    }

    protected function assertFormElements($responseArray, $present = array(), $missing = array())
    {
        $this->taDataResponse=$responseArray;
        $this->setUpAction('index');

        foreach ($present as $fieldsetName => $fieldsetElements) {
            $fieldset = $this->getFormFromResponse(
                $this->controller->indexAction()
            )->get($fieldsetName);
            foreach ($fieldsetElements as $element) {
                $this->assertTrue($fieldset->has($element));
            }
        }

        foreach ($missing as $fieldsetName => $fieldsetElements) {
            if ( $this->getFormFromResponse(
                $this->controller->indexAction()
            )->has($fieldsetName) ) {
                $fieldset = $this->getFormFromResponse(
                    $this->controller->indexAction()
                )->get($fieldsetName);
                foreach ($fieldsetElements as $element) {
                    $this->assertFalse($fieldset->has($element));
                }
            } else {
                $this->assertFalse($this->getFormFromResponse($this->controller->indexAction())->has($fieldsetName));
            }
        }
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
                    'goodsOrPsv' => 'psv',
                    'niFlag' => 0,
                    'licenceType' => 'restricted',
                    'organisation' => array(
                        'organisationType' => 'org_type.lc'
                    ),
                )
            );
        }

        $taDataBundle = array(
            'properties' => array(
                'id',
                'version',
                'status',
                'totAuthSmallVehicles',
                'totAuthMediumVehicles',
                'totAuthLargeVehicles',
                'psvOperateSmallVehicles',
                'psvSmallVehicleNotes',
                'psvSmallVehicleConfirmation',
                'psvNoSmallVehicleConfirmation',
                'psvLimousines',
                'psvNoLimousineConfirmation',
                'psvOnlyLimousineConfirmation',
            ),
            'children' => array(
                'trafficArea' => array(
                    'properties' => array(
                        'id',
                        'applyScottishRules',
                    ),
                )
            )
        );

        if ($service == 'Application' && $method == 'GET' && $bundle == $taDataBundle) {
            return $this->taDataResponse;
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
}
