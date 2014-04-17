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
    
    public function testProccessAdd()
    {
        $validData = array(
        	'authorised-vehicles' => array(
        	   'no-of-vehicles' => 1,
        	   'no-of-trailers' => 1,
        	   'parking-spaces-confirmation' => 1,
        	   'permission-confirmation' => 1,
            ),
        );
        $this->assertEquals($this->controller->processAddForm($validData), null);
    }
    
    public function testProccessEdit()
    {
        $validData = array(
                'authorised-vehicles' => array(
                        'no-of-vehicles' => 1,
                        'no-of-trailers' => 1,
                        'parking-spaces-confirmation' => 1,
                        'permission-confirmation' => 1,
                       
                ),
                'version' => 1,
        );
        $this->assertInstanceOf($this->controller->processEditForm($validData), '\Zend\Http\PhpEnvironment\Response');
    }
    
    
    
    
    
    
}