<?php

/**
 * Test case for operating centre pages
 * 
 * @author Jakub.Igla
 * @todo implement DBUNIT
 */

namespace SelfServe\test\Finance;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use PHPUnit_Framework_TestCase;
use SelfServe\test\Bootstrap;

class OperatingCentreTest extends AbstractHttpControllerTestCase
{
    
    const APPLICATION_ID = 1;
    const OP_CENTRE_ID  = 1;
    
    protected $controller;
    protected $request;
    protected $response;
    protected $routeMatch;
    protected $event;

    protected function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../config/application.config.php'
        );
        
        $this->controller = $this->getMock('\SelfServe\Controller\Finance\OperatingCentreController', array(
        	'makeRestCall'
        ));
        
        $this->request    = new Request();
        $this->response   = new Response();
        $this->routeMatch = new RouteMatch(array('controller' => 'SelfServe\Finance\OperatingCentreController'));
        $this->event      = new MvcEvent();
        $this->event->setRouteMatch($this->routeMatch);
        $this->controller->setEvent($this->event);
        
        $this->controller->setServiceLocator(Bootstrap::getServiceManager());
    }
    
    public function testProcessAdd()
    {
        $validData = $this->getValidData();
        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue(array('id' => 1)))
        ;
        
        $router = $this->getMock('\Zend\Mvc\Router\SimpleRouteStack', ['assemble']);
        $router->expects($this->once())
            ->method('assemble')
            ->will($this->returnValue('/selfserve/1/finance/index'))
        ;
        $this->controller->getEvent()->setRouter($router);
        $this->controller->getEvent()->setResponse(new \Zend\Http\PhpEnvironment\Response());
        
        
        $this->assertEquals($this->controller->processAddForm($validData), null);
    }
    
    public function testProccessEdit()
    {
        $validData = array_merge($this->getValidData(), array('version' => 1));
        
        $router = $this->getMock('\Zend\Mvc\Router\SimpleRouteStack', ['assemble']);
        $router->expects($this->once())
            ->method('assemble')
            ->will($this->returnValue('/selfserve/1/finance/index'))
        ;
        $this->controller->getEvent()->setRouter($router);
        $this->controller->getEvent()->setResponse(new \Zend\Http\PhpEnvironment\Response());
        
        $proccessFormReturnValue = $this->controller->processEditForm($validData);
        
        $this->assertInstanceOf('Zend\Http\PhpEnvironment\Response', $proccessFormReturnValue);
        $this->assertEquals(302, $proccessFormReturnValue->getStatusCode());
    }
    
    public function testMapData()
    {
        $class = new \ReflectionClass('\SelfServe\Controller\Finance\OperatingCentreController');
        $method = $class->getMethod('mapData');
        $method->setAccessible(true);
        $result = $method->invokeArgs($this->controller, array($this->getValidData()));
        
        $this->assertArrayHasKey('numberOfVehicles', $result);
        $this->assertArrayHasKey('numberOfTrailers', $result);
        $this->assertArrayHasKey('sufficientParking', $result);
        $this->assertArrayHasKey('permission', $result);
        $this->assertArrayHasKey('application', $result);
    }
    
    private function getValidData()
    {
        return array(
            'authorised-vehicles' => array(
                'no-of-vehicles' => 1,
                'no-of-trailers' => 1,
                'parking-spaces-confirmation' => 1,
                'permission-confirmation' => 1,
                'ad-placed' => 1,
            ),
            'address' => array(
                'addressLine1' => '1 Some Street',
                'addressLine2' => '',
                'addressLine3' => '',
                'city' => 'Leeds',
                'postcode' => 'LS96NF',
                'country' => 'country.GB',
            ),

         );
    }
}
