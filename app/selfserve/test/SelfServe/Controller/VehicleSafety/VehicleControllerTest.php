<?php

namespace SelfServe\test\VehicleSafety;

use PHPUnit_Framework_TestCase;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use SelfServe\Controller\VehiclesSafety\VehicleController;

class VehicleControllerTest extends AbstractHttpControllerTestCase
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
    public function testAddActionAccess()
    { 
        $this->dispatch('/selfserve/1/vehicle/add');
        
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('selfserve');
        $this->assertControllerName('selfserve\vehiclessafety\vehicle');
        $this->assertControllerClass('vehiclecontroller');
        $this->assertMatchedRouteName('selfserve/vehicle-action/vehicle-add');   
    }
    

    

}