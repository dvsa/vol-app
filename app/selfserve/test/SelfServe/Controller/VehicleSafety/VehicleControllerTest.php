<?php

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class VehicleControllerTest extends AbstractHttpControllerTestCase
{

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
                '_getLicenceEntity'
            ]
        );
        parent::setUp();

    }
    
    /**
     * Test access to main index action
     *
    public function testAddActionAccess()
    { 
        $this->dispatch('/selfserve/1/vehicle/add');
        
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('selfserve');
        $this->assertControllerName('selfserve\vehiclessafety\vehicle');
        $this->assertControllerClass('vehiclecontroller');
        $this->assertMatchedRouteName('selfserve/vehicle-action/vehicle-add');   
    }*/
    
    public function testAddAction()
    {
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


    

}