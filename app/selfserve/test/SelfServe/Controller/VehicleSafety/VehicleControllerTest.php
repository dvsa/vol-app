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

        parent::setUp();

    }
    
    /*public function testAddAction()
    {
        $this->setUpMockController( [
                'getView',
                'setCurrentStep',
                'generateSectionForm',
                'formPost',
                'getStepProcessMethod',
                'getPersistedFormData',
                'params',
                '_getLicenceEntity'
            ]);
        $applicationId = '1';

        $mockLicenceArray = [
            'goodsOrPsv' => 'goods'
        ];

        $formData = [];
        
        $mockParams = $this->getMock('\stdClass', ['fromRoute']);
        $mockParams->expects($this->once())
                ->method('fromRoute')
                ->with($this->equalTo('applicationId'))
                ->will($this->returnValue($applicationId));
        
        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($mockParams));
                
        $this->controller->expects($this->once())
            ->method('_getLicenceEntity')
            ->will($this->returnValue($mockLicenceArray));

        $this->controller->expects($this->once())
            ->method('setCurrentStep')
            ->with($this->equalTo('add-goods-vehicle'));
        
        $mockForm = $this->getMock('\Zend\Form', ['setData']);

        $mockForm->expects($this->once())
            ->method('setData')
            ->with($this->equalTo($formData))
            ->will($this->returnValue($mockForm));
                
        $this->controller->expects($this->once())
            ->method('generateSectionForm')
            ->will($this->returnValue($mockForm));
    

        $this->controller->expects($this->once())
            ->method('formPost')
            ->will($this->returnValue($mockForm));
          
        $this->controller->expects($this->once())
            ->method('getPersistedFormData')
            ->will($this->returnValue($formData));
           
        $mockView = $this->getMock(
            'Zend\View\Model\ViewModel',
            ['setTemplate', 'setVariables']
        );
                
        $viewVariables = ['form' => $mockForm, 'goodsOrPsv' => 'goods'];

        $mockView->expects($this->once())
            ->method('setVariables')
            ->with($this->equalTo($viewVariables));
        
        $mockView->expects($this->once())
            ->method('setTemplate')
            ->with('self-serve/vehicle-safety/add-vehicle');
        
        $this->controller->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($mockView));

        $this->assertSame($mockView, $this->controller->addAction());
    }

    public function testGetView()
    {
        $this->setUpMockController( [
            'setCurrentStep',
            'generateSectionForm',
            'formPost',
            'getStepProcessMethod',
            'getPersistedFormData',
            'params',
            '_getLicenceEntity'
        ]);
        $view = $this->controller->getView();
        $this->assertTrue($view instanceof \Zend\View\Model\ViewModel);
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
                ->with('selfserve/vehicles-safety', array('applicationId' => $applicationId));
        
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
        
        $this->controller->processAddGoodsVehicle($valid_data, $form, $params);

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
                ->with('selfserve/vehicle-action/vehicle-add', array('action' => 'add', 'applicationId' => $applicationId));
        
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
        
        $this->controller->processAddGoodsVehicle($valid_data, $form, $params);

    }*/
    
}
