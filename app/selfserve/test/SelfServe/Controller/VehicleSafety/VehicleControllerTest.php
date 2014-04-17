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
