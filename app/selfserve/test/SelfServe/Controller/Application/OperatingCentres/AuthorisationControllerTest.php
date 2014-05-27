<?php

/**
 * Authorisation Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace SelfServe\Test\Controller\Application\OperatingCentres;

use SelfServe\Test\Controller\Application\AbstractApplicationControllerTestCase;
use SelfServe\Controller\Application\ApplicationController;

/**
 * Authorisation Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AuthorisationControllerTest extends AbstractApplicationControllerTestCase
{

    protected $controllerName = '\SelfServe\Controller\Application\OperatingCentres\AuthorisationController';
    protected $defaultRestResponse = array(
        'OperatingCentre' => array(
            'POST' => array(
                'id' => 1
            )
        ),
        'ApplicationOperatingCentre' => array(
            'POST' => array(
                'id' => 2
            )
        )
    );
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

        // We should have 2 children (Navigation and Main)
        $children = $response->getChildren();
        $this->assertEquals(2, count($children));

        $main = null;
        $navigation = null;

        foreach ($children as $child) {
            if ($child->captureTo() == 'navigation') {
                $navigation = $child;
                continue;
            }

            if ($child->captureTo() == 'main') {
                $main = $child;
            }
        }

        // Assert that we have Main and Navigation views
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $main);
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $navigation);

        // We are not psv, so should have trailer related content
        $tableHtml = $main->getVariable('table');
        $form = $main->getVariable('form');

        $this->assertEquals($hasTrailers, (boolean) strstr($tableHtml, 'trailer'));
        //$this->assertEquals($hasTrailers, $form->get('data')->has('totAuthTrailers'));
        $this->assertEquals($hasTrailers, $form->get('data')->has('minTrailerAuth'));
        $this->assertEquals($hasTrailers, $form->get('data')->has('maxTrailerAuth'));
    }

    /**
     * Test indexAction standard national
     */
    public function testIndexActionStandardNational()
    {
        $this->setUpAction('index');

        $this->goodsOrPsv = 'goods';

        $this->setRestResponse(
            'Application',
            'GET',
            array(
                'licence' => array(
                    'id' => 10,
                    'version' => 1,
                    'goodsOrPsv' => $this->goodsOrPsv,
                    'niFlag' => 0,
                    'licenceType' => 'standard-national'
                )
            )
        );

        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);

        // We should have 2 children (Navigation and Main)
        $children = $response->getChildren();
        $this->assertEquals(2, count($children));

        $main = null;
        $navigation = null;

        foreach ($children as $child) {
            if ($child->captureTo() == 'navigation') {
                $navigation = $child;
                continue;
            }

            if ($child->captureTo() == 'main') {
                $main = $child;
            }
        }

        // Assert that we have Main and Navigation views
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $main);
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $navigation);

        // We are not psv, so should have trailer related content
        $tableHtml = $main->getVariable('table');
        $form = $main->getVariable('form');

        $this->assertEquals(false, $form->get('data')->has('totCommunityLicences'));
        $this->assertEquals(true, $form->get('data')->has('totAuthLargeVehicles'));

        $this->assertEquals(true, (boolean) strstr($tableHtml, 'trailer'));
        $this->assertEquals(true, $form->get('data')->has('minTrailerAuth'));
        $this->assertEquals(true, $form->get('data')->has('maxTrailerAuth'));
    }

    /**
     * Test indexAction standard internation
     */
    public function testIndexActionStandardInternational()
    {
        $this->setUpAction('index');

        $this->goodsOrPsv = 'goods';

        $this->setRestResponse(
            'Application',
            'GET',
            array(
                'licence' => array(
                    'id' => 10,
                    'version' => 1,
                    'goodsOrPsv' => $this->goodsOrPsv,
                    'niFlag' => 0,
                    'licenceType' => 'standard-international'
                )
            )
        );

        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);

        // We should have 2 children (Navigation and Main)
        $children = $response->getChildren();
        $this->assertEquals(2, count($children));

        $main = null;
        $navigation = null;

        foreach ($children as $child) {
            if ($child->captureTo() == 'navigation') {
                $navigation = $child;
                continue;
            }

            if ($child->captureTo() == 'main') {
                $main = $child;
            }
        }

        // Assert that we have Main and Navigation views
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $main);
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $navigation);

        // We are not psv, so should have trailer related content
        $tableHtml = $main->getVariable('table');
        $form = $main->getVariable('form');

        $this->assertEquals(true, $form->get('data')->has('totCommunityLicences'));
        $this->assertEquals(true, $form->get('data')->has('totAuthLargeVehicles'));

        $this->assertEquals(true, (boolean) strstr($tableHtml, 'trailer'));
        $this->assertEquals(true, $form->get('data')->has('minTrailerAuth'));
        $this->assertEquals(true, $form->get('data')->has('maxTrailerAuth'));
    }

    /**
     * Test indexAction restricted
     */
    public function testIndexActionRestricted()
    {
        $this->setUpAction('index');

        $this->goodsOrPsv = 'goods';

        $this->setRestResponse(
            'Application',
            'GET',
            array(
                'licence' => array(
                    'id' => 10,
                    'version' => 1,
                    'goodsOrPsv' => $this->goodsOrPsv,
                    'niFlag' => 0,
                    'licenceType' => 'restricted'
                )
            )
        );

        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);

        // We should have 2 children (Navigation and Main)
        $children = $response->getChildren();
        $this->assertEquals(2, count($children));

        $main = null;
        $navigation = null;

        foreach ($children as $child) {
            if ($child->captureTo() == 'navigation') {
                $navigation = $child;
                continue;
            }

            if ($child->captureTo() == 'main') {
                $main = $child;
            }
        }

        // Assert that we have Main and Navigation views
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $main);
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $navigation);

        // We are not psv, so should have trailer related content
        $tableHtml = $main->getVariable('table');
        $form = $main->getVariable('form');

        $this->assertEquals(true, $form->get('data')->has('totCommunityLicences'));
        $this->assertEquals(false, $form->get('data')->has('totAuthLargeVehicles'));

        $this->assertEquals(true, (boolean) strstr($tableHtml, 'trailer'));
        $this->assertEquals(true, $form->get('data')->has('minTrailerAuth'));
        $this->assertEquals(true, $form->get('data')->has('maxTrailerAuth'));
    }

    /**
     * Test indexAction with crud action
     */
    public function testIndexActionWithCrudAction()
    {
        $this->setUpAction('index', null, array('action' => 'Add'));

        $this->goodsOrPsv = 'goods';

        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexActionSubmit
     *
     * @dataProvider psvProvider
     */
    public function testIndexActionSubmit($goodsOrPsv, $hasTrailers)
    {
        $this->setUpAction(
            'index', null, array(
            'data' => array(
                'id' => 1,
                'version' => 6,
                'totAuthVehicles' => 10,
                'noOfOperatingCentres' => 1,
                'minVehicleAuth' => 10,
                'maxVehicleAuth' => 10,
                'minTrailerAuth' => 10,
                'maxTrailerAuth' => 10
            )
            )
        );

        $this->goodsOrPsv = $goodsOrPsv;

        $this->controller->setEnabledCsrf(false);

        $response = $this->controller->indexAction();

        $this->assertInstanceOf('\Zend\Http\Response', $response);
    }

    /**
     * Test addAction
     *
     * @dataProvider psvProvider
     */
    public function testAddAction($goodsOrPsv, $hasTrailers)
    {
        $this->setUpAction('add');

        $this->goodsOrPsv = $goodsOrPsv;

        $response = $this->controller->addAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);

        // We should have 2 children (Navigation and Main)
        $children = $response->getChildren();
        $this->assertEquals(2, count($children));

        $main = null;
        $navigation = null;

        foreach ($children as $child) {
            if ($child->captureTo() == 'navigation') {
                $navigation = $child;
                continue;
            }

            if ($child->captureTo() == 'main') {
                $main = $child;
            }
        }

        // Assert that we have Main and Navigation views
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $main);
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $navigation);

        // We are not psv, so should have trailer related content
        $form = $main->getVariable('form');
        $this->assertEquals($hasTrailers, $form->get('data')->has('numberOfTrailers'));
    }

    /**
     * Test addAction with submit
     *
     * @dataProvider psvProvider
     */
    public function testAddActionWithSubmit($goodsOrPsv, $hasTrailers)
    {
        $post = array(
            'address' => array(
                'id' => '',
                'version' => '',
                'addressLine1' => 'Some street',
                'city' => 'City',
                'postcode' => 'AN1 1ND',
                'country' => 'country.GB'
            ),
            'data' => array(
                'numberOfVehicles' => 10,
                'numberOfTrailers' => 10,
                'sufficientParking' => '1',
                'permission' => '1'
            )
        );

        $this->setUpAction('add', null, $post);

        $this->goodsOrPsv = $goodsOrPsv;

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->addAction();

        $this->assertInstanceOf('\Zend\Http\Response', $response);
    }

    /**
     * Test addAction with cancel
     */
    public function testAddActionWithCancel()
    {
        $post = array(
            'form-actions' => array(
                'cancel' => 'Cancel'
            )
        );

        $this->setUpAction('add', null, $post);

        $this->goodsOrPsv = 'goods';

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->addAction();

        $this->assertInstanceOf('\Zend\Http\Response', $response);
    }

    /**
     * Test editAction with cancel
     */
    public function testEditActionWithCancel()
    {
        $post = array(
            'form-actions' => array(
                'cancel' => 'Cancel'
            )
        );

        $this->setUpAction('edit', 1, $post);

        $this->goodsOrPsv = 'goods';

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->editAction();

        $this->assertInstanceOf('\Zend\Http\Response', $response);
    }

    /**
     * Test addAction with submit with add another
     *
     * @dataProvider psvProvider
     */
    public function testAddActionWithSubmitWithAddAnother($goodsOrPsv, $hasTrailers)
    {
        $post = array(
            'address' => array(
                'id' => '',
                'version' => '',
                'addressLine1' => 'Some street',
                'city' => 'City',
                'postcode' => 'AN1 1ND',
                'country' => 'country.GB'
            ),
            'data' => array(
                'numberOfVehicles' => 10,
                'numberOfTrailers' => 10,
                'sufficientParking' => '1',
                'permission' => '1'
            ),
            'form-actions' => array(
                'addAnother' => 'Add another'
            )
        );

        $this->setUpAction('add', null, $post);

        $this->goodsOrPsv = $goodsOrPsv;

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->addAction();

        $this->assertInstanceOf('\Zend\Http\Response', $response);
    }

    /**
     * Test addAction with submit with failure
     *
     * @expectedException \Exception
     */
    public function testAddActionWithSubmitWithFailure()
    {
        $post = array(
            'address' => array(
                'id' => '',
                'version' => '',
                'addressLine1' => 'Some street',
                'city' => 'City',
                'postcode' => 'AN1 1ND',
                'country' => 'country.GB'
            ),
            'data' => array(
                'numberOfVehicles' => 10,
                'numberOfTrailers' => 10,
                'sufficientParking' => '1',
                'permission' => '1'
            )
        );

        $this->setUpAction('add', null, $post);

        $this->goodsOrPsv = 'goods';

        $this->setRestResponse(
            'OperatingCentre', 'POST', ''
        );

        $this->controller->setEnabledCsrf(false);
        $this->controller->addAction();
    }

    /**
     * Test addAction with submit with failure
     *
     * @expectedException \Exception
     */
    public function testAddActionWithSubmitWithFailure2()
    {
        $post = array(
            'address' => array(
                'id' => '',
                'version' => '',
                'addressLine1' => 'Some street',
                'city' => 'City',
                'postcode' => 'AN1 1ND',
                'country' => 'country.GB'
            ),
            'data' => array(
                'numberOfVehicles' => 10,
                'numberOfTrailers' => 10,
                'sufficientParking' => '1',
                'permission' => '1'
            )
        );

        $this->setUpAction('add', null, $post);

        $this->goodsOrPsv = 'goods';

        $this->setRestResponse(
            'ApplicationOperatingCentre', 'POST', ''
        );

        $this->controller->setEnabledCsrf(false);
        $this->controller->addAction();
    }

    /**
     * Test editAction
     *
     * @dataProvider psvProvider
     */
    public function testEditAction($goodsOrPsv, $hasTrailers)
    {
        $this->setUpAction('edit', 3);

        $this->goodsOrPsv = $goodsOrPsv;

        $response = $this->controller->editAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);

        // We should have 2 children (Navigation and Main)
        $children = $response->getChildren();
        $this->assertEquals(2, count($children));

        $main = null;
        $navigation = null;

        foreach ($children as $child) {
            if ($child->captureTo() == 'navigation') {
                $navigation = $child;
                continue;
            }

            if ($child->captureTo() == 'main') {
                $main = $child;
            }
        }

        // Assert that we have Main and Navigation views
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $main);
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $navigation);

        // We are not psv, so should have trailer related content
        $form = $main->getVariable('form');
        $this->assertEquals($hasTrailers, $form->get('data')->has('numberOfTrailers'));
    }

    /**
     * Test deleteAction
     */
    public function testDeleteAction()
    {
        $this->setUpAction('delete', 3);

        $response = $this->controller->deleteAction();

        // Assert that the response redirects to Authorisation
        $this->assertInstanceOf('\Zend\Http\Response', $response);
    }

    /**
     * Provider for indexAction
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

        $actionDataBundle = array(
            'properties' => array(
                'id',
                'version',
                'numberOfTrailers',
                'numberOfVehicles',
                'sufficientParking',
                'permission',
                'adPlaced'
            ),
            'children' => array(
                'operatingCentre' => array(
                    'properties' => array(
                        'id',
                        'version'
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
                                'postcode',
                                'county',
                                'city',
                                'country'
                            )
                        )
                    )
                )
            )
        );

        if ($service == 'ApplicationOperatingCentre'
            && $method == 'GET'
            && $bundle === $actionDataBundle && isset($data['id'])) {

            return array(
                'id' => 1,
                'version' => 2,
                'numberOfTrailers' => 10,
                'numberOfVehicles' => 10,
                'sufficientParking' => 1,
                'permission' => 1,
                'adPlaced' => 1,
                'operatingCentre' => array(
                    'id' => 3,
                    'version' => 1,
                    'address' => array(
                        'id' => 1,
                        'version' => 1,
                        'addressLine1' => 'Some street 1',
                        'addressLine2' => 'Some street 2',
                        'addressLine3' => 'Some street 3',
                        'addressLine4' => 'Some street 4',
                        'postcode' => 'AB1 1AB',
                        'city' => 'City',
                        'country' => 'GB'
                    )
                )
            );
        }

        if ($service == 'ApplicationOperatingCentre' && $method == 'GET') {
            return array(
                'Count' => 1,
                'Results' => array(
                    array(
                        'id' => 1,
                        'adPlaced' => 1,
                        'permission' => 1,
                        'numberOfVehicles' => 10,
                        'numberOfTrailers' => 10,
                        'operatingCentre' => array(
                            'address' => array(
                                'id' => 1,
                                'addressLine1' => '123 Street',
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

        $controllerDataBundle = array(
            'properties' => array(
                'id',
                'version',
                'totAuthSmallVehicles',
                'totAuthMediumVehicles',
                'totAuthLargeVehicles',
                'totCommunityLicences',
                'totAuthVehicles',
                'totAuthTrailers'
            )
        );

        if ($service == 'Application' && $method == 'GET' && $bundle == $controllerDataBundle) {
            return array(
                'id' => 1,
                'version' => 1,
                'totAuthVehicles' => 10,
                'totAuthTrailers' => 10
            );
        }
    }
}
