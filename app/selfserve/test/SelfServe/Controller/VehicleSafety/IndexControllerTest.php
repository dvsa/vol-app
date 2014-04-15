<?php

namespace SelfServe\test\VehicleSafety;


use PHPUnit_Framework_TestCase;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;


class IndexControllerTest extends AbstractHttpControllerTestCase
{

    protected function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../config/application.config.php'
        );

        parent::setUp();

    }
    
    /**
     * Test access to main index action
     */
    public function testIndexActionAccess()
    { 
        $this->dispatch('/selfserve/1/vehicle-safety/index');
        
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('selfserve');
        $this->assertControllerName('selfserve\vehiclessafety\index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('selfserve/vehicles-safety');   
    }
    
    /**
     * Test press Add vehicle button
     */
    public function testPostAddVehicleAccess()
    { 
        $postData = array(
            'action'  => 'add',
        );
        $this->dispatch('/selfserve/1/vehicle-safety/index', 'POST', $postData);
        $this->assertResponseStatusCode(302);
        $this->assertRedirectTo('/selfserve/1/vehicle/add');
        
    }
    

}