<?php

/**
 * Application Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace SelfServe\Test\Controller\Application;

use SelfServe\Test\Controller\Application\AbstractApplicationControllerTestCase;
use SelfServe\Controller\Application\ApplicationController;

/**
 * Application Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationControllerTest extends AbstractApplicationControllerTestCase
{
    protected $controllerName =  '\SelfServe\Controller\Application\ApplicationController';

    protected $defaultRestResponse = array();

    private $lastSection = null;

    /**
     * Test that getNamespaceParts does what is expected
     */
    public function testGetNamespaceParts()
    {
        $controller = new \SelfServe\Controller\Application\ApplicationController();
        $parts = $controller->getNamespaceParts();

        $expected = array(
            'SelfServe',
            'Controller',
            'Application',
            'ApplicationController'
        );

        $this->assertEquals($expected, $parts);
    }

    /**
     * Test processDataMap without map
     */
    public function testProcessDataMapForSaveWithoutMap()
    {
        $input = array(
            'foo' => 'bar'
        );

        $controller = new \SelfServe\Controller\Application\ApplicationController();
        $output = $controller->processDataMapForSave($input);

        $this->assertEquals($input, $output);
    }

    /**
     * Test processDataMap
     */
    public function testProcessDataMapForSave()
    {
        $input = array(
            'foo' => array(
                'a' => 'A',
                'b' => 'B'
            ),
            'bar' => array(
                'c' => 'C',
                'd' => 'D'
            ),
            'bob' => array(
                'e' => 'E',
                'f' => 'F'
            )
        );

        $map = array(
            'main' => array(
                'mapFrom' => array('foo', 'bar'),
                'values' => array('cake' => 'cats'),
                'children' => array(
                    'bobs' => array(
                        'mapFrom' => array('bob')
                    )
                )
            )
        );

        $expected = array(
            'a' => 'A',
            'b' => 'B',
            'c' => 'C',
            'd' => 'D',
            'cake' => 'cats',
            'bobs' => array(
                'e' => 'E',
                'f' => 'F'
            )
        );

        $controller = new \SelfServe\Controller\Application\ApplicationController();
        $output = $controller->processDataMapForSave($input, $map);

        $this->assertEquals($expected, $output);
    }

    /**
     * Test indexAction
     */
    public function testIndexAction()
    {
        $this->setUpAction('index');

        $this->lastSection = 'Application/YourBusiness/BusinessDetails';

        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexAction without last section
     */
    public function testIndexActionWithoutLastSection()
    {
        $this->setUpAction('index');

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

            return array(
                'licence' => array(
                    'id' => 10,
                    'version' => 1,
                    'goodsOrPsv' => 'goods',
                    'niFlag' => 0,
                    'licenceType' => 'standard-national',
                    'organisation' => array(
                        'type' => 'org_type.lc'
                    )
                )
            );
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
                        'lastSection' => $this->lastSection
                    )
                )
            );
        }
    }
}
