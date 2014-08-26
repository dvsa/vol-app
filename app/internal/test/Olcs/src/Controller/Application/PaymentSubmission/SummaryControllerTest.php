<?php

/**
 * Summary Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace OlcsTest\Controller\Application\PaymentSubmission;

use OlcsTest\Controller\Application\AbstractApplicationControllerTestCase;
use Common\Controller\Application\ApplicationController;

/**
 * Summary Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SummaryControllerTest extends AbstractApplicationControllerTestCase
{
    protected $controllerName =  '\Common\Controller\Application\PaymentSubmission\SummaryController';

    protected $defaultRestResponse = array();

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
     * Test indexAction with goToSummary
     */
    public function testIndexActionWithGoToSummary()
    {
        $this->setUpAction('index', null, array('form-actions' => array('goToSummary' => 'Go to summary')));

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexAction with journey finish
     */
    public function testIndexActionWithJourneyFinish()
    {
        $this->setUpAction('index', null, array('foo' => 'bar'));

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
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
        if ($service == 'Application' && $method == 'GET' && $bundle == ApplicationController::$licenceDataBundle) {

            return $this->getLicenceData('goods');
        }

        if ($service == 'ApplicationCompletion' && $method == 'GET') {

            return array(
                'id' => '1',
                'version' => 1,
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
            );
        }
    }
}
