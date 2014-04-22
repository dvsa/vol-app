<?php

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class IndexControllerTest extends AbstractHttpControllerTestCase
{

    protected function setUpMockController($methods)
    {
        $this->controller = $this->getMock(
            '\SelfServe\Controller\VehiclesSafety\IndexController',
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

    private function getMockLicenceArray()
    {
        return [
            'id' => 7,
            'makeRestCall',
            'goodsOrPsv' => 'goods'
        ];
    }
    
    private function generateVehicleTable()
    {
        return 'table';
    }
    
    
    public function testIndexActionAddVehicle()
    {
        $applicationId = 1;
        $vehicleId = 1;
        $mockLicenceArray = $this->getMockLicenceArray();
        
        $mockTable = 'table';
        $action = 'Add';
        
        $this->setUpMockController( [
                'getView',
                'makeRestCall',
                'redirectToVehicleAction',
                '_getLicenceEntity',
                'getPluginManager',
                'generateVehicleTable',
                'getRequest',
                'params',
                'redirect'
            ]);
        

        $mockRequest = $this->getMock('\StdClass', ['getPost']);
        $mockUrl = $this->getMock('\StdClass');
        
        $mockRequest->expects($this->at(0))
                ->method('getPost')
                ->with($this->equalTo('action'))
                ->willReturn($action);

        $mockRequest->expects($this->at(1))
                ->method('getPost')
                ->with($this->equalTo('id'))
                ->willReturn($vehicleId);
                
        $this->controller->expects($this->once())
            ->method('_getLicenceEntity')
            ->will($this->returnValue($mockLicenceArray));
             
        $this->controller->expects($this->once())
            ->method('generateVehicleTable')
            ->with($this->equalTo($mockLicenceArray))
            ->will($this->returnValue($mockTable));
                
        $this->controller->expects($this->at(2))
            ->method('getRequest')
            ->will($this->returnValue($mockRequest)); 
       
        $mockParams = $this->getMock('\stdClass', array('fromRoute'));

        $applicationId = 1;
        $mockParams->expects($this->once())
            ->method('fromRoute')
            ->with($this->equalTo('applicationId'))
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->once())
                ->method('params')
                ->will($this->returnValue($mockParams));
        
        $this->controller->expects($this->at(4))
            ->method('getRequest')
            ->will($this->returnValue($mockRequest)); 
                
        $mockRedirect = $this->getMock('\stdClass', array('params', 'toRoute'));

        $mockRedirect->expects($this->once())
            ->method('toRoute')
            ->with($this->stringContains('selfserve/vehicle-safety/vehicle-action/vehicle-add'), 
                    $this->equalTo(['action' => $action, 
                    'vehicleId' => $vehicleId, 
                    'applicationId' => $applicationId
                 ]))            
            ->will($this->returnValue('redirect'));
        
        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($mockRedirect));
        
        $this->controller->indexAction();
    }
    
    public function testIndexActionEditVehicle()
    {
        $applicationId = 1;
        $vehicleId = 1;
        $mockLicenceArray = $this->getMockLicenceArray();
        
        $mockTable = 'table';
        $action = 'Edit';
        
        $this->setUpMockController( [
                'getView',
                'makeRestCall',
                'redirectToVehicleAction',
                '_getLicenceEntity',
                'getPluginManager',
                'generateVehicleTable',
                'getRequest',
                'params',
                'redirect'
            ]);
        

        $mockRequest = $this->getMock('\StdClass', ['getPost']);
        $mockUrl = $this->getMock('\StdClass');
        
        $mockRequest->expects($this->at(0))
                ->method('getPost')
                ->with($this->equalTo('action'))
                ->willReturn($action);

        $mockRequest->expects($this->at(1))
                ->method('getPost')
                ->with($this->equalTo('id'))
                ->willReturn($vehicleId);
                
        $this->controller->expects($this->once())
            ->method('_getLicenceEntity')
            ->will($this->returnValue($mockLicenceArray));
             
        $this->controller->expects($this->once())
            ->method('generateVehicleTable')
            ->with($this->equalTo($mockLicenceArray))
            ->will($this->returnValue($mockTable));
                
        $this->controller->expects($this->at(2))
            ->method('getRequest')
            ->will($this->returnValue($mockRequest)); 
       
        $mockParams = $this->getMock('\stdClass', array('fromRoute'));

        $applicationId = 1;
        $mockParams->expects($this->once())
            ->method('fromRoute')
            ->with($this->equalTo('applicationId'))
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->once())
                ->method('params')
                ->will($this->returnValue($mockParams));
        
        $this->controller->expects($this->at(4))
            ->method('getRequest')
            ->will($this->returnValue($mockRequest)); 
                
        $mockRedirect = $this->getMock('\stdClass', array('params', 'toRoute'));

        $mockRedirect->expects($this->once())
            ->method('toRoute')
            ->with($this->stringContains('selfserve/vehicle-safety/vehicle-action/vehicle-edit'), 
                    $this->equalTo(['action' => $action, 
                    'vehicleId' => $vehicleId, 
                    'applicationId' => $applicationId
                 ]))            
            ->will($this->returnValue('redirect'));
        
        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($mockRedirect));
        
        $this->controller->indexAction();
    }
    
    public function testIndexActionDeleteVehicle()
    {
        $applicationId = 1;
        $vehicleId = 1;
        $mockLicenceArray = $this->getMockLicenceArray();
        
        $mockTable = 'table';
        $action = 'Delete';
        
        $this->setUpMockController( [
                'getView',
                'makeRestCall',
                'redirectToVehicleAction',
                '_getLicenceEntity',
                'getPluginManager',
                'generateVehicleTable',
                'getRequest',
                'params',
                'redirect'
            ]);
        

        $mockRequest = $this->getMock('\StdClass', ['getPost']);
        $mockUrl = $this->getMock('\StdClass');
        
        $mockRequest->expects($this->at(0))
                ->method('getPost')
                ->with($this->equalTo('action'))
                ->willReturn($action);

        $mockRequest->expects($this->at(1))
                ->method('getPost')
                ->with($this->equalTo('id'))
                ->willReturn($vehicleId);
                
        $this->controller->expects($this->once())
            ->method('_getLicenceEntity')
            ->will($this->returnValue($mockLicenceArray));
             

        $this->controller->expects($this->once())
            ->method('generateVehicleTable')
            ->with($this->equalTo($mockLicenceArray))
            ->will($this->returnValue($mockTable));
                
        $this->controller->expects($this->at(2))
            ->method('getRequest')
            ->will($this->returnValue($mockRequest)); 
       
        $mockParams = $this->getMock('\stdClass', array('fromRoute'));

        $applicationId = 1;
        $mockParams->expects($this->once())
            ->method('fromRoute')
            ->with($this->equalTo('applicationId'))
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->once())
                ->method('params')
                ->will($this->returnValue($mockParams));
        
        $this->controller->expects($this->at(4))
            ->method('getRequest')
            ->will($this->returnValue($mockRequest)); 
                
        $mockRedirect = $this->getMock('\stdClass', array('params', 'toRoute'));

        $mockRedirect->expects($this->once())
            ->method('toRoute')
            ->with($this->stringContains('selfserve/vehicle-safety/vehicle-action/vehicle-delete'), 
                    $this->equalTo(['action' => $action, 
                    'vehicleId' => $vehicleId, 
                    'applicationId' => $applicationId
                 ]))
            ->will($this->returnValue('redirect'));
        
        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($mockRedirect));
        
        $this->controller->indexAction();
    }
    
}
