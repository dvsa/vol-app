<?php

/**
 * Search controller form post tests
 *
 * @author adminmwc
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class CaseConvictionControllerTest  extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../' . 'config/application.config.php'
        );
        $this->controller = $this->getMock(
            '\Olcs\Controller\CaseConvictionController',
            array(
                'getServiceLocator',
                'setBreadcrumb',
                'generateFormWithData',
                'generateForm',
                'redirect',
                'makeRestCall',
                'processEdit',
                'processAdd',
                'params',
                'fromRoute'
            )
        );
        parent::setUp();
        $_POST = array();
    }
    
    public function testIndexAction()
    {
        $this->controller->expects($this->at(0))
            ->method('fromRoute')
            ->with('licence')
            ->will($this->returnValue(7));
        
        $this->controller->expects($this->at(1))
            ->method('fromRoute')
            ->with('case')
            ->will($this->returnValue(54));
        
        $this->controller->expects($this->once())
            ->method('setBreadcrumb')
            ->with(array('licence_case_list/pagination' => array('licence' => 7)));
        
        $params = $this->getMock('\stdClass', array('fromPost'));
        
         $params->expects($this->once())
            ->method('fromPost')
            ->with('action')
            ->will($this->returnValue(false));
        
        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($params));
        
        /*$this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with('Conviction', 'GET', array('id' => 8))
            ->will($this->returnValue(array('id' => 8, 'version' => 1, 'dealtWith' => 'N')));
        
        $this->controller->expects($this->once())
            ->method('processEdit')
            ->with(array('id' => 8, 'version' => 1, 'dealtWith' => 'Y'), 'Conviction')
            ->will($this->returnValue(array('id' => 33)));
        
        $toRoute = $this->getMock('\stdClass', array('toRoute'));

        $toRoute->expects($this->once())
            ->method('toRoute')
            ->with('case_convictions', array(
                'case' =>  54, 'licence' => 7));

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($toRoute));*/
        
        $this->controller->indexAction();
        
    }
}
