<?php

/**
 * Search controller form post tests
 *
 * @author adminmwc
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class SubmissionControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../'  . 'config/application.config.php'
        );
        $this->controller = $this->getMock(
            '\Olcs\Controller\SubmissionController',
            array(
                'getServiceLocator',
                'setBreadcrumb',
                'generateFormWithData',
                'generateForm',
                'redirect',
                'params',
                'getParams',
                'makeRestCall',
                'setData',
                'processEdit',
                'processAdd',
                'getViewModel',
                'createSubmission',
                'getSubmissionView',
                'getRequest'
            )
        );
        $this->controller->routeParams = array();
        $this->licenceData = array(
            'id' => 7,
            'licenceType' => 'Standard National',
            'goodsOrPsv' => 'Psv'
        );
        
        parent::setUp();
    }
    
    public function testAddPostAction()
    {
        $this->controller->routeParams = array('case' => 54, 'licence' => 7, 'action' => 'add');
        
        $this->controller->expects($this->once())
            ->method('setBreadcrumb')
            ->with();
        
        $this->controller->expects($this->once())
            ->method('createSubmission')
            ->with($this->controller->routeParams)
            ->will($this->returnValue('{"submission":{}}'));
        
        $getRequest = $this->getMock('\stdClass', array('isPost'));
        
        $getRequest->expects($this->once())
            ->method('isPost')
            ->will($this->returnValue(true));
        
        $this->controller->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($getRequest));
        
        $data = array(
            'createdBy' => 1,
            'text' => '{"submission":{}}',
            'vosaCase' => 54);
        
        $this->controller->expects($this->once())
            ->method('processAdd')
            ->with($data, 'Submission')
            ->will($this->returnValue(8));
        
        $redirect = $this->getMock('\stdClass', array('toRoute'));
        
        $redirect->expects($this->once())
            ->method('toRoute')
            ->with('submission', array('licence' => 7, 'case' => 54, 'id' => null, 'action' => 'add'));
        
        $this->controller->expects($this->once())
             ->method('redirect')
             ->will($this->returnValue($redirect));
        
        $this->controller->addAction();
    }
    
    public function testAddGetAction()
    {
        $this->controller->routeParams = array('case' => 54, 'licence' => 7, 'action' => 'add');
        
        $this->controller->expects($this->once())
            ->method('setBreadcrumb')
            ->with();
        
        $this->controller->expects($this->once())
            ->method('createSubmission')
            ->with($this->controller->routeParams)
            ->will($this->returnValue('{"submission":{}}'));
        
        $getRequest = $this->getMock('\stdClass', array('isPost'));
        
        $getRequest->expects($this->once())
            ->method('isPost')
            ->will($this->returnValue(false));
        
        $this->controller->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($getRequest));
        
        $this->controller->expects($this->once())
            ->method('getSubmissionView')
            ->with(array('data' => array('submission' => array())))
            ->will($this->returnValue('view}'));
        
        $this->controller->addAction();
    }
    
    public function testEditAction()
    {
        $this->controller = $this->getMock(
            '\Olcs\Controller\SubmissionController',
            array(
                'getEditSubmissionData',
                'getSubmissionView',
                'setBreadcrumb',
                'getRequest'
            )
        );
        $this->controller->routeParams = array('case' => 54, 'licence' => 7, 'action' => 'add');
        
        $this->controller->expects($this->once())
            ->method('setBreadcrumb')
            ->with();
        
        $getRequest = $this->getMock('\stdClass', array('isPost'));
        
        $getRequest->expects($this->once())
            ->method('isPost')
            ->will($this->returnValue(false));
        
        $this->controller->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($getRequest));
        
        $this->controller->expects($this->once())
            ->method('getEditSubmissionData')
            ->will($this->returnValue('{"submission":{}}'));
        
        $this->controller->expects($this->once())
            ->method('getSubmissionView')
            ->with('{"submission":{}}');
        
        $this->controller->editAction();
    }
    
    public function testEditRedirectAction()
    {
        $this->controller = $this->getMock(
            '\Olcs\Controller\SubmissionController',
            array(
                'getEditSubmissionData',
                'getSubmissionView',
                'setBreadcrumb',
                'getRequest',
                'redirect',
                'params'
            )
        );
        $this->controller->routeParams = array('case' => 54, 'licence' => 7, 'action' => 'add');
        
        $this->controller->expects($this->once())
            ->method('setBreadcrumb')
            ->with();
        
        $getRequest = $this->getMock('\stdClass', array('isPost'));
        
        $getRequest->expects($this->once())
            ->method('isPost')
            ->will($this->returnValue(true));
        
        $this->controller->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($getRequest));
        
        $redirect = $this->getMock('\stdClass', array('toRoute'));
        
        $redirect->expects($this->once())
            ->method('toRoute')
            ->with('submission', array('licence' => 7, 'case' => 54, 'id' => null, 'action' => 'edit'));
        
        $this->controller->expects($this->once())
             ->method('redirect')
             ->will($this->returnValue($redirect));
        
         $params = $this->getMock('\stdClass', array('fromPost'));
         
         $params->expects($this->once())
            ->method('fromPost')
            ->with('id')
            ->will($this->returnValue(null));
         
         $this->controller->expects($this->once())
             ->method('params')
             ->will($this->returnValue($params));
        
        $this->controller->editAction();
    }
    
    /*public function testCreateSubmission()
    {
        $this->controller = $this->getMock(
            '\Olcs\Controller\SubmissionController',
            array(
                'makeRestCall',
            )
        );
        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with('Licence', 'GET', array('id' => 7))
            ->will($this->returnValue($this->licenceData));
        
        $this->controller->createSubmission(array('licence' => 7, 'case' => 54));
    }*/
}
