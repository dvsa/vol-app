<?php

/**
 * Test indexController
 */

namespace OlcsTest\Controller\VehicleSafety;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Test indexController
 */
class IndexControllerTest extends AbstractHttpControllerTestCase
{

    protected function setUpMockController($methods)
    {
        $this->controller = $this->getMock(
            '\SelfServe\Controller\VehiclesSafety\IndexController', $methods
        );
    }

    protected function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../../../config/application.config.php'
        );

        $this->serviceLocator = $this->getMock('\stdClass', array('get'));

        parent::setUp();
    }

    private function getMockLicenceArray()
    {
        return [
            'id' => 7,
            'makeRestCall',
            'goodsOrPsv' => 'goods'
        ];
    }

    private function generateVehicleTable()
    {
        return 'table';
    }

    public function testIndexActionAddVehicle()
    {
        $applicationId = 1;
        $vehicleId = 1;
        $mockLicenceArray = $this->getMockLicenceArray();

        $mockTable = 'table';
        $action = 'Add';

        $this->setUpMockController(
            [
                'getView',
                'makeRestCall',
                'redirectToVehicleAction',
                'getLicenceEntity',
                'getPluginManager',
                'generateVehicleTable',
                'getRequest',
                'params',
                'redirect'
            ]
        );

        $mockRequest = $this->getMock('\StdClass', ['getPost']);

        $mockRequest->expects($this->at(0))
            ->method('getPost')
            ->with($this->equalTo('action'))
            ->willReturn($action);

        $mockRequest->expects($this->at(1))
            ->method('getPost')
            ->with($this->equalTo('id'))
            ->willReturn($vehicleId);

        $this->controller->expects($this->once())
            ->method('getLicenceEntity')
            ->will($this->returnValue($mockLicenceArray));

        $this->controller->expects($this->once())
            ->method('generateVehicleTable')
            ->with($this->equalTo($mockLicenceArray))
            ->will($this->returnValue($mockTable));

        $this->controller->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($mockRequest));

        $mockParams = $this->getMock('\stdClass', array('fromRoute'));

        $applicationId = 1;
        $mockParams->expects($this->exactly(2))
            ->method('fromRoute')
            ->with($this->equalTo('applicationId'))
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->exactly(2))
            ->method('params')
            ->will($this->returnValue($mockParams));

        $mockRedirect = $this->getMock('\stdClass', array('params', 'toRoute'));

        $mockRedirect->expects($this->once())
            ->method('toRoute')
            ->with(
                $this->stringContains('selfserve/vehicle-safety/vehicle-action/vehicle-add'),
                $this->equalTo(
                    [
                        'action' => $action,
                        'vehicleId' => $vehicleId,
                        'applicationId' => $applicationId
                    ]
                )
            )
            ->will($this->returnValue('redirect'));

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($mockRedirect));

        $mockJourney=Array('Count'=>0,'Results'=>[]);
        $this->controller->expects($this->any())
            ->method('makeRestCall')
            ->will($this->returnValue($mockJourney));

        $this->controller->indexAction();
    }

    public function testIndexActionEditVehicle()
    {
        $applicationId = 1;
        $vehicleId = 1;
        $mockLicenceArray = $this->getMockLicenceArray();

        $mockTable = 'table';
        $action = 'Edit';

        $this->setUpMockController(
            [
                'getView',
                'makeRestCall',
                'redirectToVehicleAction',
                'getLicenceEntity',
                'getPluginManager',
                'generateVehicleTable',
                'getRequest',
                'params',
                'redirect',
                'getPost'
            ]
        );

        $mockRequest = $this->getMock('\stdClass', ['getPost']);

        $mockRequest->expects($this->at(0))
            ->method('getPost')
            ->with($this->equalTo('action'))
            ->willReturn($action);

        $mockRequest->expects($this->at(1))
            ->method('getPost')
            ->with($this->equalTo('id'))
            ->willReturn($vehicleId);

        $this->controller->expects($this->once())
            ->method('getLicenceEntity')
            ->will($this->returnValue($mockLicenceArray));

        $this->controller->expects($this->once())
            ->method('generateVehicleTable')
            ->with($this->equalTo($mockLicenceArray))
            ->will($this->returnValue($mockTable));

        $this->controller->expects($this->at(3))
            ->method('getRequest')
            ->will($this->returnValue($mockRequest));

        $mockParams = $this->getMock('\stdClass', array('fromRoute'));

        $applicationId = 1;
        $mockParams->expects($this->exactly(2))
            ->method('fromRoute')
            ->with($this->equalTo('applicationId'))
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->exactly(2))
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($mockRequest));

        $mockRedirect = $this->getMock('\stdClass', array('params', 'toRoute'));

        $mockRedirect->expects($this->once())
            ->method('toRoute')
            ->with(
                $this->stringContains('selfserve/vehicle-safety/vehicle-action/vehicle-edit'),
                $this->equalTo(
                    [
                        'action' => $action,
                        'vehicleId' => $vehicleId,
                        'applicationId' => $applicationId
                    ]
                )
            )
            ->will($this->returnValue('redirect'));

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($mockRedirect));

        $this->controller->indexAction();
    }

    public function testIndexActionDeleteVehicle()
    {
        $applicationId = 1;
        $vehicleId = 1;
        $mockLicenceArray = $this->getMockLicenceArray();

        $mockTable = 'table';
        $action = 'Delete';

        $this->setUpMockController(
            [
                'getView',
                'makeRestCall',
                'redirectToVehicleAction',
                'getLicenceEntity',
                'getPluginManager',
                'generateVehicleTable',
                'getRequest',
                'params',
                'redirect'
            ]
        );

        $mockRequest = $this->getMock('\StdClass', ['getPost']);

        $mockRequest->expects($this->at(0))
            ->method('getPost')
            ->with($this->equalTo('action'))
            ->willReturn($action);

        $mockRequest->expects($this->at(1))
            ->method('getPost')
            ->with($this->equalTo('id'))
            ->willReturn($vehicleId);

        $this->controller->expects($this->once())
            ->method('getLicenceEntity')
            ->will($this->returnValue($mockLicenceArray));

        $this->controller->expects($this->once())
            ->method('generateVehicleTable')
            ->with($this->equalTo($mockLicenceArray))
            ->will($this->returnValue($mockTable));

        $this->controller->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($mockRequest));

        $mockParams = $this->getMock('\stdClass', array('fromRoute'));

        $applicationId = 1;
        $mockParams->expects($this->exactly(2))
            ->method('fromRoute')
            ->with($this->equalTo('applicationId'))
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->exactly(2))
            ->method('params')
            ->will($this->returnValue($mockParams));

        $mockRedirect = $this->getMock('\stdClass', array('params', 'toRoute'));

        $mockRedirect->expects($this->once())
            ->method('toRoute')
            ->with(
                $this->stringContains('selfserve/vehicle-safety/vehicle-action/vehicle-delete'),
                $this->equalTo(
                    [
                        'action' => $action,
                        'vehicleId' => $vehicleId,
                        'applicationId' => $applicationId
                    ]
                )
            )
            ->will($this->returnValue('redirect'));

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($mockRedirect));

        $this->controller->indexAction();
    }

    public function testGenerateVehicleTable()
    {
        $this->setUpMockController(
            [
                'makeRestCall',
                'getPluginManager',
                'getServiceLocator',
            ]
        );
        $licenceId = 7;
        $mockUrl = $this->getMock('\StdClass');
        $mockLicence = ['id' => $licenceId];

        $settings = array(
            'sort' => 'field',
            'order' => 'ASC',
            'limit' => 10,
            'page' => 1,
            'url' => $mockUrl
        );
        $mockResults = [0 => ['vrm' => 'VRM1']];

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with(
                $this->equalTo('LicenceVehicle'),
                $this->equalTo('GET'),
                $this->equalTo(['licence' => $mockLicence['id']])
            )
            ->will($this->returnValue($mockResults));

        $mockPluginManager = $this->getMock('\stdClass', array('get'));

        $mockPluginManager->expects($this->once())
            ->method('get')
            //->with($this->equalTo('url'))
            ->will($this->returnValue($mockUrl));

        $this->controller->expects($this->once())
            ->method('getPluginManager')
            ->will($this->returnValue($mockPluginManager));

        $mockTableService = $this->getMock('\stdClass', array('buildTable'));
        $mockTable = '<html>table</html>';

        $this->setServiceLocator('Table', $mockTableService);

        $mockTableService->expects($this->once())
            ->method('buildTable')
            ->with($this->equalTo('vehicle'), $this->equalTo($mockResults), $this->equalTo($settings))
            ->will($this->returnValue($mockTable));

        $this->setServiceLocator('Table', $mockTableService);

        $this->controller->generateVehicleTable($mockLicence);
    }

    public function testCompleteAction()
    {
        $this->setUpMockController(
            [
                'params',
                'redirect'
            ]
        );
        $mockRedirect = $this->getMock('\stdClass', array('toRoute'));

        $mockParams = $this->getMock('\stdClass', array('fromRoute'));
        $applicationId = 7;
        $next_step = 'index';
        $mockParams->expects($this->once())
            ->method('fromRoute')
            ->with($this->equalTo('applicationId'))
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $mockRedirect->expects($this->once())
            ->method('toRoute')
            ->with(
                $this->equalTo('selfserve/transport'),
                $this->equalTo(['applicationId' => $applicationId, 'step' => $next_step])
            );

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($mockRedirect));

        $this->controller->completeAction();
    }

    private function setServiceLocator($params, $returnVal)
    {
        $this->controller->expects($this->once())
            ->method('getServiceLocator')
            ->will($this->returnValue($this->serviceLocator));

        $this->serviceLocator->expects($this->once())
            ->method('get')
            ->with($params)
            ->will($this->returnValue($returnVal));
    }
}
