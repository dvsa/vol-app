<?php

/**
 * Test case for operating centre pages
 * 
 * @author Jakub.Igla
 * @todo implement DBUNIT
 */

namespace SelfServe\test\LicenceType;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use PHPUnit_Framework_TestCase;
use SelfServe\test\Bootstrap;

class OperatingCentreControllerTest extends AbstractHttpControllerTestCase
{
    
    const APPLICATION_ID = 1;
    const OP_CENTRE_ID  = 1;
    
    protected $controller;
    protected $request;
    protected $response;
    protected $routeMatch;
    protected $event;

    protected function setUpMockController($methods)
    {
        $this->controller = $this->getMock(
            '\SelfServe\Controller\Finance\OperatingCentreController',
            $methods
        );

        $router = $this->getMock('\Zend\Mvc\Router\SimpleRouteStack', ['assemble']);
        $router->expects($this->any())
            ->method('assemble')
            ->will($this->returnValue('/selfserve/1/finance/index'))
        ;
        $this->controller->getEvent()->setRouter($router);
        $this->controller->getEvent()->setResponse(new \Zend\Http\PhpEnvironment\Response());
    }

    protected function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../config/application.config.php'
        );

        parent::setUp();
    }

    public function testAddAction()
    {
        $this->setUpMockController( [
            'params',
            'generateForm',
            'getFormConfigName',
        ]);
        $mockParams = $this->getMock('\stdClass', array('fromRoute'));
        $applicationId = 1;

        $mockParams->expects($this->once())
            ->method('fromRoute')
            ->with($this->equalTo('applicationId'))
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->once())
            ->method('getFormConfigName')
            ->with($this->equalTo($applicationId))
            ->will($this->returnValue('operating-centre'));

        $mockForm = new \Zend\Form\Form();
        $this->controller->expects($this->once())
            ->method('generateForm')
            ->will($this->returnValue($mockForm));

        $this->controller->addAction();
    }

    public function testEditActionWithEntityFound()
    {
        $this->setUpMockController( [
            'params',
            'makeRestCall',
            'getFormConfigName',
            'generateFormWithData',

        ]);
        $mockParams = $this->getMock('\stdClass', array('fromRoute'));

        $applicationId = 1;
        $operatingCentreId = 1;

        $mockParams->expects($this->at(0))
            ->method('fromRoute')
            ->with($this->equalTo('operatingCentreId'))
            ->will($this->returnValue($operatingCentreId));

        $mockParams->expects($this->at(1))
            ->method('fromRoute')
            ->with($this->equalTo('applicationId'))
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->at(0))
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->at(1))
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with($this->equalTo('ApplicationOperatingCentre'), $this->equalTo('GET'), $this->equalTo(['id' => $operatingCentreId, 'application' => $applicationId]))
            ->will($this->returnValue($this->mockOperatingCentre()));

        $mockForm = new \Zend\Form\Form();
        $this->controller->expects($this->once())
            ->method('generateFormWithData')
            ->will($this->returnValue($mockForm));

        $this->controller->editAction();
    }

    public function testEditActionWithNoEntityFound()
    {
        $this->setUpMockController( [
            'params',
            'makeRestCall',
            'getFormConfigName',
            'generateFormWithData',
            'notFoundAction',

        ]);
        $mockParams = $this->getMock('\stdClass', array('fromRoute'));

        $applicationId = 1;
        $operatingCentreId = 1;

        $mockParams->expects($this->at(0))
            ->method('fromRoute')
            ->with($this->equalTo('operatingCentreId'))
            ->will($this->returnValue($operatingCentreId));

        $mockParams->expects($this->at(1))
            ->method('fromRoute')
            ->with($this->equalTo('applicationId'))
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->at(0))
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->at(1))
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with($this->equalTo('ApplicationOperatingCentre'), $this->equalTo('GET'), $this->equalTo(['id' => $operatingCentreId, 'application' => $applicationId]))
            ->will($this->returnValue(null));

        $this->controller->editAction();
    }

    public function testGetFormConfigName()
    {
        $this->setUpMockController( [
            'makeRestCall',
        ]);

        $applicationId = 1;

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with($this->equalTo('Application'),
                   $this->equalTo('GET'),
                   $this->equalTo(['id' => $applicationId]),
                   $this->equalTo(['properties' => [], 'children' => ['licence']]))
            ->will($this->returnValue(['licence' => ['goodsOrPsv' => 'goods']]));

        $this->controller->getFormConfigName($applicationId);
    }

    public function testProcessAddForm()
    {
        $this->setUpMockController( [
            'makeRestCall',
            'params',
        ]);

        $mockParams = $this->getMock('\stdClass', array('fromRoute'));
        $applicationId = 1;

        $mockParams->expects($this->once())
            ->method('fromRoute')
            ->with($this->equalTo('applicationId'))
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue(['id' => 1]));

        $this->controller->processAddForm(['authorised-vehicles' => [
            'no-of-vehicles' => 1,
            'parking-spaces-confirmation' => 1,
            'permission-confirmation' => 1,
        ]]);
    }

    public function testProcessAddFormForPSV()
    {
        $this->setUpMockController( [
            'makeRestCall',
            'params',
        ]);

        $mockParams = $this->getMock('\stdClass', array('fromRoute'));
        $applicationId = 1;

        $mockParams->expects($this->once())
            ->method('fromRoute')
            ->with($this->equalTo('applicationId'))
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue(['id' => 1]));

        $this->controller->processAddForm(['authorised-vehicles' => [
            'no-of-vehicles' => 1,
            'parking-spaces-confirmation' => 1,
            'permission-confirmation' => 1,
            'no-of-trailers' => 1,
        ]]);
    }

    public function testProcessEditForm()
    {
        $this->setUpMockController( [
            'makeRestCall',
            'params',
        ]);

        $mockParams = $this->getMock('\stdClass', array('fromRoute'));
        $applicationId = 1;
        $operatingCentreId = 1;

        $mockParams->expects($this->at(0))
            ->method('fromRoute')
            ->with($this->equalTo('operatingCentreId'))
            ->will($this->returnValue($operatingCentreId));

        $mockParams->expects($this->at(1))
            ->method('fromRoute')
            ->with($this->equalTo('applicationId'))
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->at(0))
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->at(1))
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->processEditForm(['version' => 1, 'authorised-vehicles' => [
            'no-of-vehicles' => 1,
            'parking-spaces-confirmation' => 1,
            'permission-confirmation' => 1,
        ]]);
    }


    private function mockOperatingCentre()
    {
        return array(
            'version' => 1,
            'numberOfVehicles' => 23,
            'numberOfTrailers' => 12,
            'sufficientParking' => 1,
            'permission' => 1,
        );
    }

    
}