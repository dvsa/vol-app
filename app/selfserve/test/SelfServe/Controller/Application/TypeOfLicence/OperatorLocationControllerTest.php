<?php

/**
 * OperatorLocation Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace SelfServe\Test\Controller\Application\TypeOfLicence;

use SelfServe\Test\Controller\Application\AbstractApplicationControllerTestCase;
use SelfServe\Controller\Application\ApplicationController;

/**
 * OperatorLocation Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OperatorLocationControllerTest extends AbstractApplicationControllerTestCase
{
    protected $controllerName =  '\SelfServe\Controller\Application\TypeOfLicence\OperatorLocationController';

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

        $variables = $response->getVariables();

        $this->assertTrue($variables['isCollapsible']);
    }


    /**
     * Test indexAction With Disabled Sections
     */
    public function testIndexActionWithDisabledSections()
    {
        $this->setUpAction('index');

        $completion = array(
            'Count' => 1,
            'Results' => array(
                array(
                    'id' => 1,
                    'version' => 1,
                    'application' => '1',
                    'sectionTypeOfLicenceStatus' => 1,
                    'sectionTypeOfLicenceOperatorLocationStatus' => 2,
                    'sectionTypeOfLicenceOperatorTypeStatus' => 2,
                    'sectionTypeOfLicenceOperatorLocationStatus' => 0,
                    'sectionYourBusinessStatus' => 0,
                    'sectionYourBusinessBusinessTypeStatus' => 0,
                    'sectionYourBusinessBusinessDetailsStatus' => 0,
                    'sectionYourBusinessAddressesStatus' => 0,
                    'sectionYourBusinessPeopleStatus' => 0,
                    'sectionTaxiPhvStatus' => 0,
                    'sectionOperatingCentresStatus' => 0,
                    'sectionOperatingCentresAuthorisationStatus' => 0,
                    'sectionOperatingCentresFinancialEvidenceStatus' => 0,
                    'sectionTransportManagersStatus' => 0,
                    'sectionVehicleSafetyStatus' => 0,
                    'sectionVehicleSafetyVehicleStatus' => 0,
                    'sectionVehicleSafetySafetyStatus' => 0,
                    'sectionPreviousHistoryStatus' => 0,
                    'sectionPreviousHistoryFinancialHistoryStatus' => 0,
                    'sectionPreviousHistoryLicenceHistoryStatus' => 0,
                    'sectionPreviousHistoryConvictionPenaltiesStatus' => 0,
                    'sectionReviewDeclarationsStatus' => 0,
                    'sectionPaymentSubmissionStatus' => 0,
                    'sectionPaymentSubmissionPaymentStatus' => 0,
                    'sectionPaymentSubmissionSummaryStatus' => 0,
                    'lastSection' => ''
                )
            )
        );

        $this->setRestResponse('ApplicationCompletion', 'GET', $completion);

        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test indexAction without licence data
     */
    public function testIndexActionWithoutLicenceData()
    {
        $this->setUpAction('index');

        $this->setRestResponse('Application', 'GET', array('licence' => null));

        $this->lastSection = 'Application/YourBusiness/BusinessDetails';

        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test indexAction with submit
     */
    public function testIndexActionWithPartialSubmitNoJsRedirectsWithinSection()
    {
        $this->setUpAction(
            'index',
            null,
            array(
                'operator-location' => array(
                    'niFlag' => 1
                )
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\Http\Response', $response);

        $location = $response->getHeaders()->get('Location')->getFieldValue();

        $this->assertContains('type-of-licence', $location);
    }

    public function testIndexActionWithPartialSubmitJsReturnsErrors()
    {
        $this->setUpAction(
            'index',
            null,
            array(
                'operator-location' => array(
                    'niFlag' => 1
                ),
                'js-submit' => true
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->indexAction();
        // Make sure we get a view with errors
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);

        $mainView = $response->getChildren()[1];

        $this->assertFalse($mainView->getVariable('form')->isValid());
    }

    public function testIndexActionWithFullSubmitJsRedirectsToNextSection()
    {
        $this->setUpAction(
            'index',
            null,
            array(
                'operator-location' => array(
                    'niFlag' => 1
                ),
                'operator-type' => array(
                    'goodsOrPsv' => 'goods'
                ),
                'licence-type' => array(
                    'licenceType' => 'standard-international'
                ),
                'js-submit' => true,
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\Http\Response', $response);

        $location = $response->getHeaders()->get('Location')->getFieldValue();

        $this->assertContains('your-business', $location);
    }

    /**
     * Test indexAction with invalid submit
     */
    public function testIndexActionWithPartialSubmitWithoutNiFlag()
    {
        $this->setUpAction(
            'index',
            null,
            array(
                'operator-location' => array(
                )
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->indexAction();

        // Make sure we get a view with errors
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);

        $mainView = $response->getChildren()[1];

        $this->assertFalse($mainView->getVariable('form')->isValid());
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
                        'sectionTypeOfLicenceOperatorLocationStatus' => 2,
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
