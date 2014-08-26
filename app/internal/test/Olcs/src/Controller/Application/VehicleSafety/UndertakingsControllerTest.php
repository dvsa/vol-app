<?php

/**
 * Undertakings Controller Test
 *
 * @author Jessica Rowbottom <jess.rowbottom@valtech.co.uk>
 */

namespace OlcsTest\Controller\Application\VehicleSafety;

use OlcsTest\Controller\Application\AbstractApplicationControllerTestCase;
use Common\Controller\Application\ApplicationController;

/**
 * Undertakings Controller Test
 *
 * @author Jessica Rowbottom <jess.rowbottom@valtech.co.uk>
 */
class UndertakingsControllerTest extends AbstractApplicationControllerTestCase
{

    protected $controllerName = '\Common\Controller\Application\VehicleSafety\UndertakingsController';
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
                'status' => array(
                    'id' => 'test.status'
                ),
                'psvOperateSmallVhl' => null,
                'psvSmallVhlNotes' => "",
                'psvSmallVhlConfirmation' => null,
                'psvNoSmallVhlConfirmation' => null,
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
                        'type' => array(
                            'id' => ApplicationController::ORG_TYPE_REGISTERED_COMPANY
                        )
                    )
                ),
                'trafficArea' => array(
                    'id' => 5,
                    'isScottishRules' => false
                ),
                'totAuthSmallVehicles' => 1,
                'totAuthMediumVehicles' => 1,
                'totAuthLargeVehicles' => 0
            );

        $this->setUpAction('index');

        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);

        $form = $this->getFormFromView($response);
    }

    /**
     * Test indexAction
     */
    public function testIndexActionWithCase1ShowsFullForm()
    {
        $taDataResponse=array(
                'id' => 1,
                'version' => 1,
                'status' => array(
                    'id' => 'test.status'
                ),
                'psvOperateSmallVhl' => null,
                'psvSmallVhlNotes' => "",
                'psvSmallVhlConfirmation' => null,
                'psvNoSmallVhlConfirmation' => null,
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
                        'type' => array(
                            'id' => ApplicationController::ORG_TYPE_REGISTERED_COMPANY
                        )
                    )
                ),
                'trafficArea' => array(
                    'id' => 5,
                    'isScottishRules' => false
                ),
                'totAuthSmallVehicles' => 1,
                'totAuthMediumVehicles' => 0,
                'totAuthLargeVehicles' => 0
            );

        $presentFields=array(
            'smallVehiclesIntention' => Array(
                'psvOperateSmallVhl',              // 15b[i]
                'psvSmallVhlNotes',                 // 15b[ii]
                'psvSmallVhlConfirmation'           // 15cd
            ),
            'limousinesNoveltyVehicles' => Array(
                'psvLimousines',                        // 15f[i]
                'psvNoLimousineConfirmation'            // 15f[ii]
            )
        );

        $missingFields=array(
            'nineOrMore' => Array(
                'psvNoSmallVhlConfirmation'        // 15e
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
    public function testIndexActionWithCase2ShowsFullForm()
    {
        $taDataResponse=array(
                'id' => 1,
                'version' => 1,
                'status' => array(
                    'id' => 'test.status'
                ),
                'psvOperateSmallVhl' => null,
                'psvSmallVhlNotes' => "",
                'psvSmallVhlConfirmation' => null,
                'psvNoSmallVhlConfirmation' => null,
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
                        'type' => array(
                            'id' => ApplicationController::ORG_TYPE_REGISTERED_COMPANY
                        )
                    )
                ),
                'trafficArea' => array(
                    'id' => 8,
                    'isScottishRules' => true
                ),
                'totAuthSmallVehicles' => 1,
                'totAuthMediumVehicles' => 0,
                'totAuthLargeVehicles' => 0
            );

        $presentFields=array(
            'smallVehiclesIntention' => Array(          // 15c/d
                'psvSmallVhlConfirmation'
            ),
            'limousinesNoveltyVehicles' => Array(
                'psvLimousines',                        // 15f[i]
                'psvNoLimousineConfirmation'            // 15f[ii]
            )
        );

        $missingFields=array(
            'smallVehiclesIntention' => Array(
                'psvOperateSmallVhl',              // 15b[i]
                'psvSmallVhlNotes'                  // 15b[ii]
            ),
            'nineOrMore' => Array(                      // 15e
                'psvNoSmallVhlConfirmation'
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
                'status' => array(
                    'id' => 'test.status'
                ),
                'psvOperateSmallVhl' => null,
                'psvSmallVhlNotes' => "",
                'psvSmallVhlConfirmation' => null,
                'psvNoSmallVhlConfirmation' => null,
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
                        'type' => array(
                            'id' => ApplicationController::ORG_TYPE_REGISTERED_COMPANY
                        )
                    )
                ),
                'trafficArea' => array(
                    'id' => 5,
                    'isScottishRules' => false
                ),
                'totAuthSmallVehicles' => 0,
                'totAuthMediumVehicles' => 1,
                'totAuthLargeVehicles' => 0
            );

        $presentFields=array(
            'nineOrMore' => Array(                      // 15e
                'psvNoSmallVhlConfirmation'
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
                'psvOperateSmallVhl',              // 15b[i]
                'psvSmallVhlNotes',                 // 15b[ii]
                'psvSmallVhlConfirmation'           // 15cd
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
                'status' => array(
                    'id' => 'test.status'
                ),
                'psvOperateSmallVhl' => null,
                'psvSmallVhlNotes' => "",
                'psvSmallVhlConfirmation' => null,
                'psvNoSmallVhlConfirmation' => null,
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
                        'type' => array(
                            'id' => ApplicationController::ORG_TYPE_REGISTERED_COMPANY
                        )
                    )
                ),
                'trafficArea' => array(
                    'id' => 5,
                    'isScottishRules' => null
                ),
                'totAuthSmallVehicles' => null,
                'totAuthMediumVehicles' => 1,
                'totAuthLargeVehicles' => null
            );

        $presentFields=array(
            'nineOrMore' => Array(                      // 15e
                'psvNoSmallVhlConfirmation'
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
                'psvOperateSmallVhl',              // 15b[i]
                'psvSmallVhlNotes',                 // 15b[ii]
                'psvSmallVhlConfirmation'           // 15cd
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
                'status' => array(
                    'id' => 'test.status'
                ),
                'psvOperateSmallVhl' => null,
                'psvSmallVhlNotes' => "",
                'psvSmallVhlConfirmation' => null,
                'psvNoSmallVhlConfirmation' => null,
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
                        'type' => array(
                            'id' => ApplicationController::ORG_TYPE_REGISTERED_COMPANY
                        )
                    )
                ),
                'trafficArea' => array(
                    'id' => 5,
                    'isScottishRules' => false
                ),
                'totAuthSmallVehicles' => 1,
                'totAuthMediumVehicles' => 1,
                'totAuthLargeVehicles' => 1
            );

        $presentFields=array(
            'smallVehiclesIntention' => Array(
                'psvOperateSmallVhl',              // 15b[i]
                'psvSmallVhlNotes',                 // 15b[ii]
                'psvSmallVhlConfirmation'
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
            'nineOrMore' => Array(                      // 15e
                'psvNoSmallVhlConfirmation'
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
                'status' => array(
                    'id' => 'test.status'
                ),
                'psvOperateSmallVhl' => null,
                'psvSmallVhlNotes' => "",
                'psvSmallVhlConfirmation' => null,
                'psvNoSmallVhlConfirmation' => null,
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
                        'type' => array(
                            'id' => ApplicationController::ORG_TYPE_REGISTERED_COMPANY
                        )
                    )
                ),
                'trafficArea' => array(
                    'id' => 5,
                    'isScottishRules' => true
                ),
                'totAuthSmallVehicles' => 1,
                'totAuthMediumVehicles' => 1,
                'totAuthLargeVehicles' => 1
            );

        $presentFields=array(
            'smallVehiclesIntention' => Array(          // 15c/d
                'psvSmallVhlConfirmation'
            ),
            'limousinesNoveltyVehicles' => Array(
                'psvLimousines',                        // 15f[i]
                'psvNoLimousineConfirmation'            // 15f[ii]
            ),
            'limousinesNoveltyVehicles' => Array(
               'psvOnlyLimousinesConfirmation',         // 15g
            )
        );

        $missingFields=array(
            'smallVehiclesIntention' => Array(
                'psvOperateSmallVhl',              // 15b[i]
                'psvSmallVhlNotes'                  // 15b[ii]
            ),
            'nineOrMore' => Array(                      // 15e
                'psvNoSmallVhlConfirmation'
            ),
        );

        $this->assertFormElements($taDataResponse, $presentFields, $missingFields);
    }

    protected function assertFormElements($responseArray, $present = array(), $missing = array())
    {
        $this->taDataResponse=$responseArray;
        $this->setUpAction('index');

        // Make sure we get a view not a response
        $response=$this->controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);

        foreach ($present as $fieldsetName => $fieldsetElements) {
            $fieldset = $this->getFormFromView($response)->get($fieldsetName);
            foreach ($fieldsetElements as $element) {
                $this->assertTrue($fieldset->has($element), $element . ' not present');
            }
        }

        foreach ($missing as $fieldsetName => $fieldsetElements) {
            if ( $this->getFormFromView($response)->has($fieldsetName) ) {
                $fieldset = $this->getFormFromView($response)->get($fieldsetName);
                foreach ($fieldsetElements as $element) {
                    $this->assertFalse($fieldset->has($element), $element . ' should not be present');
                }
            } else {
                $this->assertFalse($this->getFormFromView($response)->has($fieldsetName));
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
            return $this->getLicenceData('psv');
        }

        if ($service == 'ApplicationCompletion' && $method == 'GET') {
            return $this->getApplicationCompletionData();
        }

        $taDataBundle = array(
            'properties' => array(
                'id',
                'version',
                'totAuthSmallVehicles',
                'totAuthMediumVehicles',
                'totAuthLargeVehicles',
                'psvOperateSmallVhl',
                'psvSmallVhlNotes',
                'psvSmallVhlConfirmation',
                'psvNoSmallVhlConfirmation',
                'psvLimousines',
                'psvNoLimousineConfirmation',
                'psvOnlyLimousinesConfirmation',
            ),
            'children' => array(
                'trafficArea' => array(
                    'properties' => array(
                        'id',
                        'isScottishRules',
                    ),
                ),
                'status' => array(
                    'properties' => array(
                        'id'
                    )
                )
            )
        );

        if ($service == 'Application' && $method == 'GET' && $bundle == $taDataBundle) {
            return $this->taDataResponse;
        }
    }
}
