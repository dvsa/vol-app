<?php

/**
 * Summary Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace SelfServe\Test\Controller\Application\ReviewDeclarations;

use SelfServe\Test\Controller\Application\AbstractApplicationControllerTestCase;
use SelfServe\Controller\Application\ApplicationController;

/**
 * Summary Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SummaryControllerTest extends AbstractApplicationControllerTestCase
{
    protected $controllerName =  '\SelfServe\Controller\Application\ReviewDeclarations\SummaryController';

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
     * Test indexAction with form alterations
     */
    public function testIndexActionWithFormAlterations()
    {
        $this->setUpAction('index');

        $this->setRestResponse(
            'Application',
            'GET',
            array(
                'prevConviction' => true,
                'licence' => array(
                    'id' => 10,
                    'version' => 1,
                    'goodsOrPsv' => 'goods',
                    'niFlag' => 1,
                    'licenceType' => 'standard-national',
                    'organisation' => array(
                        'organisationType' => 'org_type.lc'
                    )
                ),
                'documents' => array()
            )
        );

        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test simpleAction
     */
    public function testSimpleAction()
    {
        $this->setUpAction('simple');

        $response = $this->controller->simpleAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test indexAction with submit
     */
    public function testIndexActionWithSubmit()
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
        if ($service == 'Application' && $method == 'GET') {
            return array(
                'prevConviction' => true,
                'licence' => array(
                    'id' => 10,
                    'version' => 1,
                    'goodsOrPsv' => 'goods',
                    'niFlag' => 0,
                    'licenceType' => 'standard-national',
                    'organisation' => array(
                        'organisationType' => 'org_type.lc'
                    ),
                ),
                'documents' => array()
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

        $convictionDataBundle = array(
            'properties' => array(
                'id',
                'personTitle',
                'personFirstname',
                'personLastname',
                'dateOfConviction',
                'convictionNotes',
                'courtFpm',
                'penalty'
            ),
        );
        if ($service == 'Conviction' && $method === 'GET' && $bundle == $convictionDataBundle) {
            return array(
                'Count'  => 1,
                'Results' => array(
                    array(
                        'id' => 1,
                        'personTitle' => 'Mr',
                        'personFirstname' => 'Alex',
                        'personLastname' => 'P',
                        'dateOfConviction' => '01/01/2014',
                        'convictionNotes' => 'No MOT',
                        'courtFpm' => 'Leeds court',
                        'penalty' => '100£'
                    )
                )
            );
        }

    }
}
