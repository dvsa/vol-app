<?php

/**
 * Test OperatingCentreController
 */

namespace SelfServe\test\Controller\Finance;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use SelfServe\Controller\Finance\OperatingCentreController;

/**
 * Test OperatingCentreController
 */
class OperatingCentreControllerTest extends AbstractHttpControllerTestCase
{

    /**
     * Build a mock controller
     *
     * @param array $methods
     */
    protected function getMockController($methods = array())
    {
        $this->controller = $this->getMock(
            'SelfServe\Controller\Finance\OperatingCentreController', $methods
        );
    }

    /**
     * Set up the unit tests
     */
    protected function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../../../config/application.config.php'
        );

        parent::setUp();
    }

    /**
     * Test indexAction With Crud Action
     */
    public function testIndexActionWithCrudAction()
    {
        $this->getMockController(array('checkForCrudAction'));

        $this->controller->expects($this->once())
            ->method('checkForCrudAction')
            ->will($this->returnValue('add'));

        $this->assertEquals('add', $this->controller->indexAction());
    }

    /**
     * Test indexAction With Missing Application
     */
    public function testIndexActionWithMissingApplication()
    {
        $applicationId = 1;

        $this->getMockController(array('checkForCrudAction', 'params', 'makeRestCall', 'notFoundAction'));

        $mockParams = $this->getMock('\stdClass', array('fromRoute'));

        $mockParams->expects($this->once())
            ->method('fromRoute')
            ->with('applicationId')
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->once())
            ->method('checkForCrudAction')
            ->will($this->returnValue(false));

        $this->controller->expects($this->any())
            ->method('makeRestCall')
            ->will($this->returnCallback(array($this, 'mockRestCall')));

        $this->controller->expects($this->once())
            ->method('notFoundAction')
            ->will($this->returnValue(404));

        $this->assertEquals(404, $this->controller->indexAction());
    }

    /**
     * Test indexAction with 0 results
     */
    public function testIndexActionWithoutResults()
    {
        $applicationId = 2;

        $this->getMockController(
            array(
                'checkForCrudAction',
                'params',
                'makeRestCall',
                'getServiceLocator',
                'generateFormWithData',
                'getViewModel',
                'renderLayout',
                'notFoundAction',
            )
        );

        $mockParams = $this->getMock('\stdClass', array('fromRoute'));

        $mockParams->expects($this->any())
            ->method('fromRoute')
            ->with('applicationId')
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->once())
            ->method('checkForCrudAction')
            ->will($this->returnValue(false));

        $this->controller->expects($this->at(0))
            ->method('makeRestCall')
            ->will($this->returnValue(array('licence' => array())));

        $this->controller->expects($this->at(1))
            ->method('makeRestCall')
            ->will($this->returnCallback(array($this, 'mockRestCall')));


        $this->controller->indexAction();
    }

    /**
     * Test indexAction with 0 results
     *
     * @group current
     */
    public function testIndexAction()
    {
        $applicationId = 3;

        $this->getMockController(
            array(
                'checkForCrudAction',
                'params',
                'makeRestCall',
                'getServiceLocator',
                'generateFormWithData',
                'getViewModel',
                'renderLayout'
            )
        );

        $mockParams = $this->getMock('\stdClass', array('fromRoute'));

        $mockParams->expects($this->any())
            ->method('fromRoute')
            ->with('applicationId')
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->once())
            ->method('checkForCrudAction')
            ->will($this->returnValue(false));

        $this->controller->expects($this->any())
            ->method('makeRestCall')
            ->will($this->returnCallback(array($this, 'mockRestCall')));

        $mockServiceLocator = $this->getMock('\stdClass', array('get'));

        $mockTable = $this->getMock('\stdClass', array('buildTable'));

        $mockTable->expects($this->once())
            ->method('buildTable')
            ->with('operatingcentre')
            ->will($this->returnValue('<table></table>'));

        $mockServiceLocator->expects($this->once())
            ->method('get')
            ->with('Table')
            ->will($this->returnValue($mockTable));

        $this->controller->expects($this->once())
            ->method('getServiceLocator')
            ->will($this->returnValue($mockServiceLocator));

        $this->controller->expects($this->once())
            ->method('generateFormWithData')
            ->will($this->returnValue('<form></form>'));

        $mockViewModel = $this->getMock('\stdClass', array('setTemplate'));

        $this->controller->expects($this->once())
            ->method('getViewModel')
            ->with(array('operatingCentres' => '<table></table>', 'form' => '<form></form>', 'isPsv' => false))
            ->will($this->returnValue($mockViewModel));

        $this->controller->expects($this->once())
            ->method('renderLayout')
            ->with($mockViewModel)
            ->will($this->returnValue('LAYOUT'));

        $this->assertEquals('LAYOUT', $this->controller->indexAction());
    }

    /**
     * Test addAction
     */
    public function testAddAction()
    {
        $this->getMockController(array('generateForm', 'getViewModel', 'renderLayout', 'params', 'makeRestCall'));
        $applicationId = 3;
        $mockViewModel = $this->getMock('\stdClass', array('setTemplate'));

        $mockParams = $this->getMock('\stdClass', array('fromRoute'));
        $mockParams->expects($this->any())
            ->method('fromRoute')
            ->with('applicationId')
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->once())
            ->method('generateForm')
            ->will($this->returnValue('<form></form>'));

        $this->controller->expects($this->once())
            ->method('getViewModel')
            ->will($this->returnValue($mockViewModel));

        $this->controller->expects($this->once())
            ->method('renderLayout')
            ->with($mockViewModel)
            ->will($this->returnValue('LAYOUT'));

        $this->assertEquals('LAYOUT', $this->controller->addAction());
    }

    /**
     * Test editAction With Missing Id
     */
    public function testEditActionWithMissingId()
    {
        $ocId = 1;
        $applicationId = 3;

        $this->getMockController(array('makeRestCall', 'params', 'notFoundAction'));

        $mockParams = $this->getMock('\stdClass', array('fromRoute'));

        $mockParams->expects($this->at(0))
            ->method('fromRoute')
            ->with('id')
            ->will($this->returnValue($ocId));

        $mockParams->expects($this->at(1))
            ->method('fromRoute')
            ->with('applicationId')
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue(array()));

        $this->controller->expects($this->once())
            ->method('notFoundAction')
            ->will($this->returnValue(404));

        $this->assertEquals(404, $this->controller->editAction());
    }

    /**
     * Test editAction
     */
    public function testEditAction()
    {
        $ocId = 2;
        $applicationId = 3;

        $data = array(
            'version' => 1,
            'numberOfVehicles' => 10,
            'numberOfTrailers' => 10,
            'sufficientParking' => 1,
            'permission' => 1,
            'licence' => array('goodsOrPsv' => 'psv'),
        );

        $this->getMockController(array('makeRestCall', 'params', 'generateFormWithData', 'getViewModel', 'renderLayout'));

        $mockParams = $this->getMock('\stdClass', array('fromRoute'));

        $mockParams->expects($this->at(0))
            ->method('fromRoute')
            ->with('id')
            ->will($this->returnValue($ocId));

        $mockParams->expects($this->at(1))
            ->method('fromRoute')
            ->with('applicationId')
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->once())
            ->method('generateFormWithData')
            ->will($this->returnValue('<form></form>'));

        $this->controller->expects($this->any())
            ->method('makeRestCall')
            ->will($this->returnCallback(array($this, 'mockRestCall')));

        $mockViewModel = $this->getMock('\stdClass', array('setTemplate'));

        $this->controller->expects($this->once())
            ->method('getViewModel')
            ->will($this->returnValue($mockViewModel));

        $this->controller->expects($this->once())
            ->method('renderLayout')
            ->with($mockViewModel)
            ->will($this->returnValue('LAYOUT'));

        $this->assertEquals('LAYOUT', $this->controller->editAction());
    }

    /**
     * Test deleteAction Without Id
     */
    public function testDeleteActionWithoutId()
    {
        $ocId = 1;
        $appId = 2;
        $result = array(

        );

        $this->getMockController(array('params', 'makeRestCall', 'notFoundAction'));

        $mockParams = $this->getMock('\stdClass', array('fromRoute'));

        $mockParams->expects($this->at(0))
            ->method('fromRoute')
            ->with('id')
            ->will($this->returnValue($ocId));

        $mockParams->expects($this->at(1))
            ->method('fromRoute')
            ->with('applicationId')
            ->will($this->returnValue($appId));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->at(2))
            ->method('makeRestCall')
            ->will($this->returnValue($result));

        $this->controller->expects($this->once())
            ->method('notFoundAction')
            ->will($this->returnValue(404));

        $this->assertEquals(404, $this->controller->deleteAction());
    }

    /**
     * Test deleteAction
     */
    public function testDeleteAction()
    {
        $ocId = 1;
        $appId = 2;
        $result = array(
            'id' => 1
        );

        $this->getMockController(array('params', 'makeRestCall', 'redirect'));

        $mockParams = $this->getMock('\stdClass', array('fromRoute'));

        $mockParams->expects($this->at(0))
            ->method('fromRoute')
            ->with('id')
            ->will($this->returnValue($ocId));

        $mockParams->expects($this->at(1))
            ->method('fromRoute')
            ->with('applicationId')
            ->will($this->returnValue($appId));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->at(2))
            ->method('makeRestCall')
            ->will($this->returnValue($result));

        $this->controller->expects($this->at(3))
            ->method('makeRestCall')
            ->with('ApplicationOperatingCentre', 'DELETE', array('id' => 1));

        $mockRedirect = $this->getMock('\stdClass', array('toRoute'));

        $mockRedirect->expects($this->once())
            ->method('toRoute')
            ->will($this->returnValue('REDIRECT'));

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($mockRedirect));

        $this->assertEquals('REDIRECT', $this->controller->deleteAction());
    }

    /**
     * Test completeAction
     */
    public function testCompleteAction()
    {
        $controller = new OperatingCentreController();

        $response = $controller->completeAction();

        $this->assertEquals(null, $response);
    }

    /**
     * Test processAuthorisation
     */
    public function testProcessAuthorisation()
    {
        $data = array(
            'data' => array(
                'id' => '',
                'noOfOperatingCentres' => '',
                'minVehicleAuth' => '',
                'maxVehicleAuth' => '',
                'minTrailerAuth' => '',
                'maxTrailerAuth' => ''
            )
        );

        $this->getMockController(array('makeRestCall', 'redirect'));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with('Application', 'PUT');

        $mockRedirect = $this->getMock('\stdClass', array('toRoute'));

        $mockRedirect->expects($this->once())
            ->method('toRoute')
            ->will($this->returnValue('REDIRECT'));

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($mockRedirect));

        $this->assertEquals('REDIRECT', $this->controller->processAuthorisation($data));
    }

    /**
     * Test processAddForm With Failure
     */
    public function testProcessAddFormWithFailure()
    {
        $applicationId = 7;

        $data = array(
            'address' => array(
                'addressLine1' => '',
                'addressLine2' => '',
                'addressLine3' => '',
                'city' => '',
                'country' => '',
                'postcode' => ''
            ),
            'authorised-vehicles' => array(
                'no-of-vehicles' => 3,
                'no-of-trailers' => 4,
                'parking-spaces-confirmation' => 1,
                'permission-confirmation' => 1
            )
        );

        $this->getMockController(array('makeRestCall', 'params'));

        $mockParams = $this->getMock('\stdClass', array('fromRoute'));

        $mockParams->expects($this->once())
            ->method('fromRoute')
            ->with('applicationId')
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->at(1))
            ->method('makeRestCall')
            ->with('OperatingCentre', 'POST')
            ->will($this->returnValue(array('id' => 1)));

        $this->controller->expects($this->at(2))
            ->method('makeRestCall')
            ->with('ApplicationOperatingCentre', 'POST')
            ->will($this->returnValue(array()));

        $this->assertEquals(null, $this->controller->processAddForm($data));
    }

    /**
     * Test processAddForm
     */
    public function testProcessAddForm()
    {
        $applicationId = 7;

        $data = array(
            'address' => array(
                'addressLine1' => '',
                'addressLine2' => '',
                'addressLine3' => '',
                'city' => '',
                'country' => '',
                'postcode' => ''
            ),
            'authorised-vehicles' => array(
                'no-of-vehicles' => 3,
                'no-of-trailers' => 4,
                'parking-spaces-confirmation' => 1,
                'permission-confirmation' => 1
            )
        );

        $this->getMockController(array('makeRestCall', 'params', 'redirect'));

        $mockParams = $this->getMock('\stdClass', array('fromRoute'));

        $mockParams->expects($this->once())
            ->method('fromRoute')
            ->with('applicationId')
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->at(1))
            ->method('makeRestCall')
            ->with('OperatingCentre', 'POST')
            ->will($this->returnValue(array('id' => 1)));

        $this->controller->expects($this->at(2))
            ->method('makeRestCall')
            ->with('ApplicationOperatingCentre', 'POST')
            ->will($this->returnValue(array('id' => 1)));

        $mockRedirect = $this->getMock('\stdClass', array('toRoute'));

        $mockRedirect->expects($this->once())
            ->method('toRoute')
            ->will($this->returnValue('REDIRECT'));

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($mockRedirect));

        $this->assertEquals('REDIRECT', $this->controller->processAddForm($data));
    }

    /**
     * Test processEditForm
     */
    public function testProcessEditForm()
    {
        $ocId = 4;
        $applicationId = 7;

        $data = array(
            'version' => 1,
            'address' => array(
                'addressLine1' => '',
                'addressLine2' => '',
                'addressLine3' => '',
                'city' => '',
                'country' => '',
                'postcode' => ''
            ),
            'authorised-vehicles' => array(
                'no-of-vehicles' => 3,
                'no-of-trailers' => 4,
                'parking-spaces-confirmation' => 1,
                'permission-confirmation' => 1
            )
        );

        $this->getMockController(array('makeRestCall', 'params', 'redirect'));

        $mockParams = $this->getMock('\stdClass', array('fromRoute'));

        $mockParams->expects($this->at(0))
            ->method('fromRoute')
            ->with('id')
            ->will($this->returnValue($ocId));

        $mockParams->expects($this->at(1))
            ->method('fromRoute')
            ->with('applicationId')
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with('ApplicationOperatingCentre', 'PUT');

        $mockRedirect = $this->getMock('\stdClass', array('toRoute'));

        $mockRedirect->expects($this->once())
            ->method('toRoute')
            ->will($this->returnValue('REDIRECT'));

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($mockRedirect));

        $this->assertEquals('REDIRECT', $this->controller->processEditForm($data));
    }

    /**
     * Mock the rest call
     *
     * @param string $service
     * @param string $method
     * @param array $data
     */
    public function mockRestCall($service, $method, $data)
    {
        $response = array();

        $aocs = array(
            1 => array(),
            2 => array(
                'Count' => 0,
                'Results' => array()
            ),
            3 => array(
                'Count' => 1,
                'Results' => array(
                    array(
                        'id' => 3,
                        'numberOfTrailers' => 5,
                        'numberOfVehicles' => 5,
                        'permission' => 1,
                        'adPlaced' => 1,
                        'operatingCentre' => array(
                            'id' => 2,
                            'address' => array(
                                'addressLine1' => '',
                                'addressLine2' => '',
                                'addressLine3' => '',
                                'addressLine4' => '',
                                'postcode' => '',
                                'county' => '',
                                'city' => '',
                                'country' => ''
                            )
                        ),
                        'version' => 1
                    )
                )
            )
        );

        $aocsById = array(
            2=> array(
                        'id' => 2,
                        'numberOfTrailers' => 5,
                        'numberOfVehicles' => 5,
                        'permission' => 1,
                        'adPlaced' => 1,
                        'operatingCentre' => array(
                            'id' => 2,
                            'address' => array(
                                'addressLine1' => '',
                                'addressLine2' => '',
                                'addressLine3' => '',
                                'addressLine4' => '',
                                'postcode' => '',
                                'county' => '',
                                'city' => '',
                                'country' => ''
                            )
                        ),
                        'version' => 1,
                        'sufficientParking' => 1
                    )
        );

        $applications = array(
            1 => array(),
            2 => array(
                'version' => 1,
                'totAuthVehicles' => 5,
                'totAuthTrailers' => 10
            ),
            3 => array(
                'version' => 1,
                'totAuthVehicles' => 5,
                'totAuthTrailers' => 10,
                'licence' => array('goodsOrPsv' => 'goods'),
            )
        );

        switch ($service) {
            case 'Application':

                $this->assertTrue(array_key_exists('id', $data));
                $response = $applications[$data['id']];

                break;

            case 'ApplicationOperatingCentre':
                $this->assertTrue(array_key_exists('application', $data) or array_key_exists('id', $data));
                if ( array_key_exists('application', $data) ) {
                    $response = $aocs[$data['application']];
                } else {
                    $response = $aocsById[$data['id']];
                }
                break;

            case 'ApplicationCompletion':
                $response = Array('Count'=>0,'Results'=>[]);
                break;
        }

        return $response;
    }
}
