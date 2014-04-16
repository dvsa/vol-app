<?php

/**
 * Search controller form post tests
 *
 * @author adminmwc
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class OlcsConvictionControllerTest  extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../'
                . 'config/application.config.php'
        );
        $this->controller = $this->getMock(
            '\Olcs\Controller\ConvictionController',
            array(
                'getServiceLocator',
                'setBreadcrumb',
                'generateFormWithData',
                'generateForm',
                'getPluginManager',
                'redirect',
                'params',
                'getParams',
                'makeRestCall',
                'setData',
                'url',
                'processEdit',
                'processAdd'
            )
        );
        parent::setUp();
    }
    
    public function testAddAction() 
    {
        /*$this->controller->expects($this->once())
            ->method('setBreadcrumb')
            ->with(array('operators/operators-params' => array('operatorName' => 'a')));*/
        
        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('case', 'licence', 'id'))
            ->will($this->returnValue(Array ( 'licence' => 7, 'case' => 54 )));
        
        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with('VosaCase', 'GET', array('id' => 54))
            ->will($this->returnValue(array('id' => 54)));
        
        $form = $this->getMock('\stdClass', array('setData'));
        
        $this->controller->expects($this->once())
            ->method('generateForm')
            ->with('conviction', 'processConviction')
            ->will($this->returnValue($form));
        
        $form->expects($this->once())
            ->method('setData')
            ->with(array('vosaCase' => 54));
        
        $this->controller->addAction();
    }
    
    public function testAddFailAction() 
    {
        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('case', 'licence', 'id'))
            ->will($this->returnValue(Array ( 'licence' => 7, 'case' => 54 )));
        
        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with('VosaCase', 'GET', array('id' => 54))
            ->will($this->returnValue(''));
        
        $this->controller->addAction();
    }
    
    public function testEditAction() 
    {
        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('case', 'licence', 'id'))
            ->will($this->returnValue(Array ( 'licence' => 7, 'case' => 54, 'id' => 33 )));
        
        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with('Conviction', 'GET', array('id' => 33))
            ->will($this->returnValue(array('id' => 33)));
        
        $form = $this->getMock('\stdClass', array('setData'));
        
        $this->controller->expects($this->once())
            ->method('generateFormWithData')
            ->with('conviction', 'processConviction')
            ->will($this->returnValue($form));
        
        $this->controller->editAction();
    }
    
    public function testEditFailAction() 
    {
        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('case', 'licence', 'id'))
            ->will($this->returnValue(Array ( 'licence' => 7, 'case' => 54, 'id' => 33 )));
        
        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with('Conviction', 'GET', array('id' => 33))
            ->will($this->returnValue(''));
        
        $this->controller->editAction();
    }
    
    public function testProcessEditAction() 
    {
        $data = array(
            'id' => 33,
            'defendant-details' => array(),
            'offence' => array()
            );
        
        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('action', 'licence', 'case'))
            ->will($this->returnValue(Array ( 'licence' => 7, 'case' => 54, 'action' => 'edit' )));
        
        $this->controller->expects($this->once())
            ->method('processEdit')
            ->with(array('id' => 33), 'Conviction')
            ->will($this->returnValue(array('id' => 33)));
        
        $toRoute = $this->getMock('\stdClass', array('toRoute'));
        
        $toRoute->expects($this->once())
            ->method('toRoute')
            ->with('case_convictions', array(
                'case' =>  54)
            );
        
        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($toRoute));
        
        $this->controller->processConviction($data);
    }
    
    public function testProcessAddAction() 
    {
        $data = array(
            'id' => 33,
            'defendant-details' => array(),
            'offence' => array()
            );
        
        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('action', 'licence', 'case'))
            ->will($this->returnValue(Array ( 'licence' => 7, 'case' => 54, 'action' => 'add' )));
        
        $this->controller->expects($this->once())
            ->method('processAdd')
            ->with(array('id' => 33), 'Conviction')
            ->will($this->returnValue(array('id' => 33)));
        
        $toRoute = $this->getMock('\stdClass', array('toRoute'));
        
        $toRoute->expects($this->once())
            ->method('toRoute')
            ->with('case_convictions', array(
                'case' =>  54)
            );
        
        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($toRoute));
        
        $this->controller->processConviction($data);
    }
}
