<?php

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class VehicleControllerTest extends AbstractHttpControllerTestCase
{

    protected function setUpMockController($methods)
    {
        $this->controller = $this->getMock(
            '\SelfServe\Controller\VehiclesSafety\VehicleController',
            $methods
        );    
    }
    
    protected function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../config/application.config.php'
        );
        $this->controller = $this->getMock(
            '\SelfServe\Controller\VehiclesSafety\VehicleController',
            [
                'getView',
                'setCurrentStep',
                'generateSectionForm',
                'formPost',
                'getStepProcessMethod',
                'getPersistedFormData',
                'params',
                '_getLicenceEntity',
                'makeRestCall',
            ]
        );
        parent::setUp();

    }
    
    public function testDeleteAction()
    {
        $applicationId = '1';
        $vehicleId = '1';
    
        $mockParams[0] = $this->getMock('\stdClass', ['fromRoute']);
        $mockParams[0]->expects($this->once())
            ->method('fromRoute')
            ->with($this->equalTo('applicationId'))
            ->will($this->returnValue($applicationId));
        
        $mockParams[1] = $this->getMock('\stdClass', ['fromRoute']);
        $mockParams[1]->expects($this->once())
            ->method('fromRoute')
            ->with($this->equalTo('vehicleId'))
            ->will($this->returnValue($vehicleId));
        
        $this->controller->expects($this->exactly(2))
            ->method('params')
            ->will($this->onConsecutiveCalls($mockParams[0], $mockParams[1]));
        
        $restCallResults = array(
        	array('Count' => 1, 'Results' => array(array('id' => 1))),
            array(),
        );
        
        $this->controller->expects($this->exactly(2))
            ->method('makeRestCall')
            ->will($this->onConsecutiveCalls($restCallResults[0], $restCallResults[1]));
        
        $router = $this->getMock('\Zend\Mvc\Router\SimpleRouteStack', ['assemble']);
        $router->expects($this->once())
                ->method('assemble')
                ->will($this->returnValue('/selfserve/1/vehicle-safety/safety-inspections'))
        ;
        $this->controller->getEvent()->setRouter($router);
        $this->controller->getEvent()->setResponse(new \Zend\Http\PhpEnvironment\Response());
        
        $deleteAction = $this->controller->deleteAction();
        $this->assertInstanceOf('Zend\Http\PhpEnvironment\Response', $deleteAction);
        $this->assertEquals(302, $deleteAction->getStatusCode());
    }
    
    public function testAddAction()
    {
        $this->setUpMockController( [
                'generateForm',
                '_getLicenceEntity'
            ]);
        
        $mockLicenceArray = [
            'id' => 7,
            'makeRestCall',
            'goodsOrPsv' => 'goods'
        ];

        $this->controller->expects($this->once())
                ->method('_getLicenceEntity')
                ->will($this->returnValue($mockLicenceArray)); 
        
        $this->controller->expects($this->once())
                ->method('generateForm')
                ->with($this->equalTo('update-vehicle'), 
                       $this->equalTo('processAddGoodsVehicleForm'));       

        $this->controller->addAction();
    }
    
    public function testEditActionWithValidVehicle()
    {
        $this->setUpMockController( [
                'generateForm',
                '_getLicenceEntity',
                'params',
                'makeRestCall',
                'notFoundAction',
                'generateFormWithData',
                'getViewModel'
            ]);
        
        $mockLicenceArray = [
            'id' => 7,
            'goodsOrPsv' => 'goods'
        ];
        $vehicleId = 1;
        $restData = array(
        	'id' => $vehicleId,
        );
        $vehicleResult = array(
            'id' => 1,
            'version' => 1,
            'vrm' => 'VRM1',
            'platedWeight' => 1000,
            'bodyType' => 'vehicleBodyType'
        );
        
        $vehicleForm = new \Zend\Form\Form();
        
        $vehicleData = array(
            'id' => $vehicleResult['id'],
            'version' => $vehicleResult['version'],
            'vrm' => $vehicleResult['vrm'],
            'plated_weight' => $vehicleResult['platedWeight'],
            'body_type' => $vehicleResult['bodyType']
        );
        $mockParams = $this->getMock('\StdClass', ['fromRoute']);
        $mockParams->expects($this->once())
                ->method('fromRoute')
                ->with($this->equalTo('vehicleId'))
                ->will($this->returnValue($vehicleId)); 

        $this->controller->expects($this->once())
                ->method('_getLicenceEntity')
                ->will($this->returnValue($mockLicenceArray)); 
        
        $this->controller->expects($this->once())
                ->method('params')
                ->will($this->returnValue($mockParams)); 
                
        $this->controller->expects($this->once())
                ->method('makeRestCall')
                ->with($this->equalTo('Vehicle'), 
                       $this->equalTo('GET'), 
                       $this->equalTo($restData))
                ->will($this->returnValue($vehicleResult));
                
        $this->controller->expects($this->once())
                ->method('generateFormWithData')
                ->with($this->equalTo('update-vehicle'), 
                       $this->equalTo('processEditGoodsVehicleForm'), 
                       $this->equalTo($vehicleData))
                ->will($this->returnValue($vehicleForm));

        $this->controller->editAction();
    }
 
    public function testEditActionWithInvalidVehicle()
    {
        $this->setUpMockController( [
                'generateForm',
                '_getLicenceEntity',
                'params',
                'makeRestCall',
                'notFoundAction',
                'generateFormWithData',
                'getViewModel'
            ]);
        
        $mockLicenceArray = [
            'id' => 7,
            'goodsOrPsv' => 'goods'
        ];
        $vehicleId = 1;
        $restData = array(
        	'id' => $vehicleId,
        );
        $vehicleResult = null;
        
        $vehicleForm = new \Zend\Form\Form();
        
        $vehicleData = array(
            'id' => $vehicleResult['id'],
            'version' => $vehicleResult['version'],
            'vrm' => $vehicleResult['vrm'],
            'plated_weight' => $vehicleResult['platedWeight'],
            'body_type' => $vehicleResult['bodyType']
        );
        $mockParams = $this->getMock('\StdClass', ['fromRoute']);
        $mockParams->expects($this->once())
                ->method('fromRoute')
                ->with($this->equalTo('vehicleId'))
                ->will($this->returnValue($vehicleId)); 

        $this->controller->expects($this->once())
                ->method('_getLicenceEntity')
                ->will($this->returnValue($mockLicenceArray)); 
        
        $this->controller->expects($this->once())
                ->method('params')
                ->will($this->returnValue($mockParams)); 
                
        $this->controller->expects($this->once())
                ->method('makeRestCall')
                ->with($this->equalTo('Vehicle'), 
                       $this->equalTo('GET'), 
                       $this->equalTo($restData))
                ->will($this->returnValue($vehicleResult));
        
        $this->controller->expects($this->once())
                ->method('notFoundAction');
                
      
        $this->controller->editAction();
    }
   
    public function testProcessAddGoodsVehicle()
    {
         $this->setUpMockController( [
            'persistVehicle',
            'makeRestCall',
            'getRequest',
            'redirect',
            'params'
        ]);
                
        $applicationId = 1;
        
        $mockLicenceArray = [
            'goodsOrPsv' => 'goods'
        ];

        $valid_data = ['vrm' => 'test', 
                       'plated_weight' => 1000,
                       'body_type' => 'asdf']; 
        $form = new \Zend\Form\Form();
        $params = array();
        $posted_data = ['submit_save' => ''];

        $mockParams = $this->getMock('\stdClass', ['fromRoute']);
        $mockParams->expects($this->any())
                ->method('fromRoute')
                ->with($this->equalTo('applicationId'))
                ->will($this->returnValue($applicationId));
        
        $mockPost = $this->getMock('\stdClass', ['toArray']);
        $mockPost->expects($this->once())
                ->method('toArray')
                ->will($this->returnValue($posted_data));
        
        $mockRequest = $this->getMock('\stdClass', ['getPost']);
        $mockRequest->expects($this->once())
                ->method('getPost')
                ->will($this->returnValue($mockPost));
        
        $mockRedirect = $this->getMock('\stdClass', ['toRoute']);
        $mockRedirect->expects($this->once())
                ->method('toRoute')
                ->with('selfserve/vehicle-safety', array('applicationId' => $applicationId));
        
        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($mockParams));
        
        $this->controller->expects($this->any())
            ->method('_getLicenceEntity')
            ->will($this->returnValue($mockLicenceArray));

        $this->controller->expects($this->any())
            ->method('persistVehicle')
            ->with($this->equalTo($valid_data))
            ->will($this->returnValue(true));
    
        $this->controller->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($mockRequest));
    
        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($mockRedirect));
        
        $this->controller->processAddGoodsVehicleForm($valid_data, $form, $params);

    }
    
    public function testProcessAddGoodsVehicleAndAddanother()
    {
         $this->setUpMockController( [
            'persistVehicle',
            'makeRestCall',
            'getRequest',
            'redirect',
            'params'
        ]);
                
        $applicationId = 1;
        
        $mockLicenceArray = [
            'goodsOrPsv' => 'goods'
        ];

        $valid_data = ['vrm' => 'test', 
                       'plated_weight' => 1000,
                       'body_type' => 'asdf']; 
        $form = new \Zend\Form\Form();
        $params = array();
        $posted_data = ['submit_add_another' => ''];

        $mockParams = $this->getMock('\stdClass', ['fromRoute']);
        $mockParams->expects($this->any())
                ->method('fromRoute')
                ->with($this->equalTo('applicationId'))
                ->will($this->returnValue($applicationId));
        
        $mockPost = $this->getMock('\stdClass', ['toArray']);
        $mockPost->expects($this->once())
                ->method('toArray')
                ->will($this->returnValue($posted_data));
        
        $mockRequest = $this->getMock('\stdClass', ['getPost']);
        $mockRequest->expects($this->once())
                ->method('getPost')
                ->will($this->returnValue($mockPost));
        
        $mockRedirect = $this->getMock('\stdClass', ['toRoute']);
        $mockRedirect->expects($this->once())
                ->method('toRoute')
                ->with('selfserve/vehicle-safety/vehicle-action/vehicle-add', array('action' => 'add', 'applicationId' => $applicationId));
        
        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($mockParams));
        
        $this->controller->expects($this->any())
            ->method('_getLicenceEntity')
            ->will($this->returnValue($mockLicenceArray));

        $this->controller->expects($this->any())
            ->method('persistVehicle')
            ->with($this->equalTo($valid_data))
            ->will($this->returnValue(true));
    
        $this->controller->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($mockRequest));
    
        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($mockRedirect));
        
        $this->controller->processAddGoodsVehicleForm($valid_data, $form, $params);

    }
    
       
    public function testProcessEditGoodsVehicle()
    {
         $this->setUpMockController( [
            'persistVehicle',
            'makeRestCall',
            'getRequest',
            'redirect',
            'params'
        ]);
                
        $applicationId = 1;
        
        $mockLicenceArray = [
            'goodsOrPsv' => 'goods'
        ];

        $valid_data = ['id' => 1,
                       'vrm' => 'test', 
                       'plated_weight' => 1000,
                       'body_type' => 'asdf']; 
        $form = new \Zend\Form\Form();
        $params = array();
        $posted_data = ['submit_save' => ''];

        $mockParams = $this->getMock('\stdClass', ['fromRoute']);
        $mockParams->expects($this->any())
                ->method('fromRoute')
                ->with($this->equalTo('applicationId'))
                ->will($this->returnValue($applicationId));
        
        $mockPost = $this->getMock('\stdClass', ['toArray']);
        $mockPost->expects($this->once())
                ->method('toArray')
                ->will($this->returnValue($posted_data));
        
        $mockRequest = $this->getMock('\stdClass', ['getPost']);
        $mockRequest->expects($this->once())
                ->method('getPost')
                ->will($this->returnValue($mockPost));
        
        $mockRedirect = $this->getMock('\stdClass', ['toRoute']);
        $mockRedirect->expects($this->once())
                ->method('toRoute')
                ->with('selfserve/vehicle-safety', array('applicationId' => $applicationId));
        
        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($mockParams));
        
        $this->controller->expects($this->any())
            ->method('_getLicenceEntity')
            ->will($this->returnValue($mockLicenceArray));

        $this->controller->expects($this->any())
            ->method('persistVehicle')
            ->with($this->equalTo($valid_data))
            ->will($this->returnValue(true));
    
        $this->controller->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($mockRequest));
    
        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($mockRedirect));
        
        $this->controller->processEditGoodsVehicleForm($valid_data, $form, $params);

    }
}
