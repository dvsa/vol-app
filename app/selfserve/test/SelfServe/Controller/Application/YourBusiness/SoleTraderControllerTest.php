<?php

/**
 * Sole Trader Controller Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace SelfServe\Test\Controller\Application\YourBusiness;

use SelfServe\Test\Controller\Application\AbstractApplicationControllerTestCase;
use SelfServe\Controller\Application\ApplicationController;

/**
 * Sole Trader Controller Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class SoleTraderControllerTest extends AbstractApplicationControllerTestCase
{

    protected $controllerName = '\SelfServe\Controller\Application\YourBusiness\SoleTraderController';
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
     * Test indexAction with submit
     */
    public function testIndexActionWithSubmit()
    {
        $this->setUpAction(
            'index', null, array(
            'form-actions' => array('submit' => ''),
            'data' => array(
                'dateOfBirth' => array(
                    'month' => '02',
                    'day'   => '23',
                    'year' => '2014'
                ),
                'id' => 79,
                'version' => 4,
                'title' => 'Mrs',
                'firstName' => 'A',
                'surname' => 'P12',
                'otherNames' => 'other12'
            )
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexAction with back button
     */
    public function testIndexActionWithBack()
    {
        $this->setUpAction(
            'index', null, array(
            'form-actions' => array('back' => 'Back')
        )
        );

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
            $licenceBundle = array(
                'children' => array(
                    'licence' => array(
                        'properties' => array(
                            'id',
                            'version',
                            'goodsOrPsv',
                            'niFlag',
                            'licenceType'
                        ),
                        'children' => array(
                            'organisation' => array(
                                'properties' => array(
                                    'type'
                                )
                            )
                        )
                    )
                )
            );
            $orgTypeBundle = array(
                'children' => array(
                    'licence' => array(
                        'children' => array(
                            'organisation' => array(
                                'properties' => array(
                                    'id',
                                    'version',
                                    'type',
                                )
                            )
                        )
                    )
                )
            );
            if ($bundle == $orgTypeBundle) {
                return array(
                    'licence' => array(
                        'organisation' => array(
                            'type' => $this->organisation,
                        )
                    )
                );
            } elseif ($bundle == $licenceBundle) {
                return array(
                    'licence' => array(
                        'id' => 10,
                        'version' => 1,
                        'goodsOrPsv' => 'goods',
                        'niFlag' => 0,
                        'licenceType' => 'standard-national',
                        'organisation' => array(
                            'type' => 'org_type.st'
                        )
                    )
                );
            } else {
                return array(
                    'id' => 1,
                    'version' => 1,
                );
            }
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

        $personDataBundle = array(
            'properties' => array(
                'id',
                'version',
                'title',
                'firstName',
                'surname',
                'dateOfBirth',
                'otherNames'
            ),
        );
        if ($service == 'Person' && $method = 'GET' && $bundle == $personDataBundle) {
            return array(
                'Count'  => 1,
                'Results' => array(
                    array(
                        'id' => 1,
                        'version' => 1,
                        'title' => 'Mr',
                        'firstName' => 'A',
                        'surname' => 'P',
                        'dateOfBirth' => '2014-01-01',
                        'otherNames' => 'other names'
                    )
                )
            );
        }

    }
}
