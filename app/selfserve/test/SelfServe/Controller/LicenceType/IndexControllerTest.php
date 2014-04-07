<?php

namespace SelfServe\test\LicenceType;

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
     * Test access to operator location step
     */
    public function testOperatorLocationStepAccess()
    { 
        $this->dispatch('/selfserve/licence-type/operator-location');
        
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('selfserve');
        $this->assertControllerName('selfserve\licencetype\index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('selfserve/licence-type');
        
    }
        
    /**
     * Test access to operator type step
     */
    public function testOperatorTypeStepAccess()
    { 
        $this->dispatch('/selfserve/licence-type/operator-type');
        
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('selfserve');
        $this->assertControllerName('selfserve\licencetype\index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('selfserve/licence-type');
    }   
        
    /**
     * Test access to licence type step
     */
    public function testLicenceTypeStepAccess()
    { 
        $this->dispatch('/selfserve/licence-type/licence-type');
        
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('selfserve');
        $this->assertControllerName('selfserve\licencetype\index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('selfserve/licence-type');
    }   
    
    
    /**
     * Test access to licence type PSV step
     */
    public function testLicenceTypePSVStepAccess()
    { 
        $this->dispatch('/selfserve/licence-type/licence-type-psv');
        
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('selfserve');
        $this->assertControllerName('selfserve\licencetype\index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('selfserve/licence-type');
    }   
    
        
    /**
     * Test access to complete step action
     */
    public function testCompleteActionAccess()
    { 
        $this->dispatch('/selfserve/licence-type/complete');
        
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('selfserve');
        $this->assertControllerName('selfserve\licencetype\index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('selfserve/licence-type-complete');
    }   
}