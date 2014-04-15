<?php
namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class CaseStayControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../config/application.config.php'
        );
        
        $this->controller = $this->getMock(
            '\Olcs\Controller\CaseStayController',
            [
                'addAction',
                'processAddAction',
                'fromRoute',
                'generateFormWithData',
                'getCase',
                'getView'
            ]
        );
        
        parent::setUp();
    }

    public function testAddAction(){
        $caseId = '12345';
        $case = ['key' => 'case'];
        
        $this->controller->expects($this->at(1))
            ->method('fromRoute')
            ->with($this->equalTo('case'))
            ->will($this->returnValue($caseId));
        
        $this->controller->expects($this->once())
            ->method('getCase')
            ->with($this->equalTo($caseId))
            ->will($this->returnValue($case));
    }
    
    public function testEditAction(){
        $caseId = '12345';
        $stayId = '12345';
        
        $this->controller->expects($this->at(1))
            ->method('fromRoute')
            ->with($this->equalTo('case'))
            ->will($this->returnValue($caseId));
        
        $this->controller->expects($this->at(2))
            ->method('fromRoute')
            ->with($this->equalTo('stay'))
            ->will($this->returnValue($stayId));
    }
}