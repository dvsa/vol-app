<?php

/**
 * LicenceHistoryControllerTest
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace SelfServe\Test\Controller\Application\PreviousHistory;

use SelfServe\Test\Controller\Application\AbstractApplicationControllerTestCase;
use SelfServe\Controller\Application\ApplicationController;

/**
 * LicenceHistoryControllerTest
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceHistoryControllerTest extends AbstractApplicationControllerTestCase
{
    protected $controllerName =  '\SelfServe\Controller\Application\PreviousHistory\LicenceHistoryController';

    protected $defaultRestResponse = array();
    protected $call=1;
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
    }

    /**
     * Test indexAction with submit
     * @group acurrent
     */
    public function testIndexActionWithSubmit()
    {
        $this->setUpAction(
            'index',
            null,
            array(
                'dataLicencesCurrent' => array(
                    'id' => '',
                    'version' => '1',
                    'currentLicence' => 'Y'
                ),
                'table-licences-current' => array(
                    'rows' => 1
                ),
                'dataLicencesApplied' => array(
                    'appliedForLicence' => 'Y'
                ),
                'table-licences-applied' => array(
                    'rows' => 1
                ),
                'dataLicencesRevoked' => array(
                    'refusedLicence' => 'Y'
                ),
                'table-licences-revoked' => array(
                    'rows' => 1
                ),
                'dataLicencesRefused' => array(
                    'revokedLicence' => 'Y',
                ),
                'table-licences-refused' => array(
                    'rows' => 1
                ),
                'dataLicencesDisqualified' => array(
                    'disqualifiedLicence' => 'N',
                ),
                'table-licences-disqualified' => array(
                    'rows' => 1
                ),
                'dataLicencesPublicInquiry' => array(
                    'publicInquiryLicence' => 'Y'
                ),
                'table-licences-public-inquiry' => array(
                    'rows' => 1
                ),
                'dataLicencesHeld' => array(
                    'heldLicence' => 'N'
                ),
                'table-licences-held' => array(
                    'rows' => 0
                ),
                'form-actions' => array(
                    'submit' => ''
                )
            )
        );
        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->indexAction();
        
        // Make sure we get a response not a view
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
        echo PHP_EOL . 'call number ' . $this->call++ . PHP_EOL;
        echo 'service ' . $service . PHP_EOL;
        echo 'method ' . $method . PHP_EOL;
        echo 'bundle:' . PHP_EOL;
        print_r($bundle);
        
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
        $previousLicenceBundle = array(
            'properties' => array(
                'id',
                'version',
                'licNo',
                'holderName',
                'willSurrender',
                'purchaseDate',
                'disqualificationDate',
                'disqualificationLength',
                'previousLicenceType'
             )   
        );
        if ($service == 'PreviousLicence' && $method == 'GET' && $bundle == $previousLicenceBundle) {
            return array(
                //'Count' => 1,
                //'Results' => array(
                //    array(
                        'id' => 1,
                        'version' => 1,
                        'licNo' => 'ln',
                        'holderName' => 'hn',
                        'willSurrender' => true,
                        'purchaseDate' => '',
                        'disqualificationDate' => '',
                        'disqualificationLength' => '1',
                        'previousLicenceType' => 'CURRENT'
                  //  )
                //)    
            );
        }
    }
}
