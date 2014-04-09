<?php

namespace SelfServe\test\BusinessType;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use PHPUnit_Framework_TestCase;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;

class IndexControllerTest extends AbstractControllerTestCase
{
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

        parent::setUp();

    }
    
    /**
     * Test access to business type action
     */
    public function testBusinessTypeStepAccess()
    { 
        $this->dispatch('/selfserve/7/business-type/business-type');
        
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('selfserve');
        $this->assertControllerName('selfserve\BusinessType\index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('selfserve/business-type');   
    }
    
    /**
     * Test access to registered company action
     */
    public function testRegisteredCompanyStepAccess()
    { 
        $this->dispatch('/selfserve/7/business-type/registered-company');
        
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('selfserve');
        $this->assertControllerName('selfserve\BusinessType\index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('selfserve/business-type');
    }   
    
    /**
     * Test access to Sole Trader action
     */
    public function testSoleTraderStepAccess()
    { 
        $this->dispatch('/selfserve/7/business-type/sole-trader');
        
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('selfserve');
        $this->assertControllerName('selfserve\BusinessType\index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('selfserve/business-type');
    }   
    
    /**
     * Test access to Partnership action
     */
    public function testPartnershipStepAccess()
    { 
        $this->dispatch('/selfserve/7/business-type/partnership');
        
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('selfserve');
        $this->assertControllerName('selfserve\BusinessType\index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('selfserve/business-type');
    }   
      
    
    /**
     * Test access to Llp action
     */
    public function testLlpStepAccess()
    { 
        $this->dispatch('/selfserve/7/business-type/llp');
        
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('selfserve');
        $this->assertControllerName('selfserve\BusinessType\index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('selfserve/business-type');
    }   
      
    /**
     * Test access to public authority action
     */
    public function testPublicAuthorityStepAccess()
    { 
        $this->dispatch('/selfserve/7/business-type/public-authority');
        
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('selfserve');
        $this->assertControllerName('selfserve\BusinessType\index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('selfserve/business-type');
    }   
    
    /**
     * Test access to complete action
     */
    public function testCompleteActionAccess()
    { 
        $this->dispatch('/selfserve/7/business-type/complete');
        
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('selfserve');
        $this->assertControllerName('selfserve\BusinessType\index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('selfserve/business-complete');
    }   
}