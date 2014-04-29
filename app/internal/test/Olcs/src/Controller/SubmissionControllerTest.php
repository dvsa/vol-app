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
                'getView',
                'createSubmission'
            )
        );
        
        $this->licenceData = array(
            'id' => 7,
            'licenceType' => 'Standard National',
            'goodsOrPsv' => 'Psv'
        );
        
        parent::setUp();
    }
    
    public function testAddAction()
    {
        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('case', 'licence', 'id'))
            ->will($this->returnValue(array ( 'licence' => 7, 'case' => 54)));
        
        $this->controller->expects($this->once())
            ->method('setBreadcrumb')
            ->with(array(
                'licence_case_list/pagination' => array('licence' => 7),
                'case_manage' => array('case' => 54, 'licence' => 7, 'tab' => 'overview')
            ));
        
        $this->controller->expects($this->once())
            ->method('createSubmission')
            ->with(array('case' => 54, 'licence' => 7))
            ->will($this->returnValue('{"submission":{}}'));
        
        $this->controller->expects($this->once())
            ->method('processAdd')
            ->with(array(
                'createdBy' => 1,
                'text' => '{"submission":{}}',
                'vosaCase' => 54))
            ->will($this->returnValue(8));
        
        $view = $this->getMock(
            'Zend\View\Model\ViewModel',
            ['setTemplate']
        );
        
        $this->controller->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($view));
        
        $view->expects($this->once())
            ->method('setTemplate')
            ->with($this->equalTo('submission/page'));
        
        $this->controller->addAction();
    }
    
    public function testCreateSubmission()
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
    }
}
