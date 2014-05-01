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
                'getRequest',
                'url'
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
    
    /**
     * 
     */
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
    
    /**
     * 
     */
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
    
    /**
     * 
     */
    public function testEditPostAction()
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
    
    /**
     * 
     */
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
    
    /**
     * Test getEditSubmissionData
     */
    public function testgetEditSubmissionData()
    {
        $this->controller = $this->getMock(
            '\Olcs\Controller\SubmissionController',
            array(
                'makeRestCall',
                'getServiceLocator',
            )
        );
        $this->controller->routeParams = array('case' => 54, 'licence' => 7, 'action' => 'add', 'id' => 8);
        $bundle = array(
            'children' => array(
                'submissionActions' => array(
                    'properties' => 'ALL',
                    'children' => array(
                        'userSender' => array(
                            'properties' => 'ALL'
                        ),
                        'userRecipient' => array(
                            'properties' => 'ALL'
                        ),
                    )
                )
            )
        );
        
        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with('Submission', 'GET', array('id' => $this->controller->routeParams['id']), $bundle)
            ->will(
                $this->returnValue(array(
                'text' => '{"submission":{}}',
                'submissionActions' => array(
                    array(
                        'submissionActionType' => 'decision',
                        'submissionActionStatus' => 'submission_decision.disagree'
                    )
                )))
            );
        
        $serviceLocator = $this->getMock('\stdClass', array('get'));
        
        $serviceLocator->expects($this->once())
            ->method('get')
            ->with('config')
            ->will(
                $this->returnValue(array(
                'static-list-data' => array('submission_decision' => 
                    array(
                        'submission_decision.disagree' => 'Disagree'
                    ))
                ))
            );
        
        $this->controller->expects($this->once())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceLocator));
        
        $this->controller->getEditSubmissionData();
    }
    
    public function testgetSubmissionView()
    {
        $this->controller = $this->getMock(
            '\Olcs\Controller\SubmissionController',
            array(
                'getViewModel',
                'url',
            )
        );
        
        $this->controller->routeParams = array('case' => 54, 'licence' => 7, 'action' => 'post', 'id' => 8);
        
        $url = $this->getMock('\stdClass', array('fromRoute'));
        
        $url->expects($this->once())
            ->method('fromRoute')
            ->with('submission', $this->controller->routeParams)
            ->will($this->returnValue('/licence/7/case/28/submission/edit/166'));
        
        $this->controller->expects($this->once())
            ->method('url')
            ->will($this->returnValue($url));
        
        $viewModel = $this->getMock('\stdClass', array('setTemplate'));
        
       $viewModel->expects($this->once())
            ->method('setTemplate')
            ->with('submission/page');
        
        $this->controller->expects($this->once())
            ->method('getViewModel')
            ->with(
                array(
                    'params' => array(
                        'formAction' => '/licence/7/case/28/submission/edit/166',
                        'pageTitle' => 'case-submission',
                        'pageSubTitle' => 'case-submission-text',
                        'submission' => array()
                    )
                )
            )
            ->will($this->returnValue($viewModel));
         
        $this->controller->getSubmissionView(array());
    }
    
    public function testPostDecisionAction()
    {
        $this->controller = $this->getMock(
            '\Olcs\Controller\SubmissionController',
            array(
                'getViewModel',
                'params',
                'redirect',
                'backToCaseButton'
            )
        );
        $this->controller->routeParams = array('case' => 54, 'licence' => 7, 'action' => 'decision', 'id' => 8);
        
        $params = $this->getMock('\stdClass', array('fromPost'));
         
         $params->expects($this->at(0))
            ->method('fromPost')
            ->with('decision')
            ->will($this->returnValue(true));
         
         $this->controller->expects($this->atLeastOnce())
             ->method('params')
             ->will($this->returnValue($params));
         
         $redirect = $this->getMock('\stdClass', array('toRoute'));
        
        $redirect->expects($this->once())
            ->method('toRoute')
            ->with('submission', $this->controller->routeParams);
        
        $this->controller->expects($this->once())
             ->method('redirect')
             ->will($this->returnValue($redirect));
         
         $this->controller->postAction();
    }
    
    public function testPostRecommendAction()
    {
        $this->controller->routeParams = array('case' => 54, 'licence' => 7, 'action' => 'recommendation', 'id' => 8);
        $params = $this->getMock('\stdClass', array('fromPost'));
         
         $params->expects($this->at(0))
            ->method('fromPost')
            ->with('decision')
            ->will($this->returnValue(false));
         
         $params->expects($this->at(1))
            ->method('fromPost')
            ->with('recommend')
            ->will($this->returnValue(true));
         
         $this->controller->expects($this->atLeastOnce())
             ->method('params')
             ->will($this->returnValue($params));
         
         $redirect = $this->getMock('\stdClass', array('toRoute'));
        
        $redirect->expects($this->once())
            ->method('toRoute')
            ->with('submission', $this->controller->routeParams);
        
        $this->controller->expects($this->once())
             ->method('redirect')
             ->will($this->returnValue($redirect));
         
         $this->controller->postAction();
    }
    
    public function testRecommendationAction()
    {
        $this->controller = $this->getMock(
            '\Olcs\Controller\SubmissionController',
            array(
                'backToCaseButton',
                'formView',
                'setBreadcrumb',
                
            )
        );
        $this->controller->routeParams = array('case' => 54, 'licence' => 7, 'action' => 'recommendation', 'id' => 8);
        /*$this->controller->expects($this->once())
             ->method('backToCaseButton');*/
        
        $this->controller->expects($this->once())
            ->method('setBreadcrumb')
            ->with();
        
        $this->controller->expects($this->once())
            ->method('formView')
            ->with('recommend');
        
        $this->controller->recommendationAction();
    }
    
    public function testDecisionAction()
    {
        $this->controller = $this->getMock(
            '\Olcs\Controller\SubmissionController',
            array(
                'backToCaseButton',
                'formView',
                'setBreadcrumb',
                
            )
        );
        $this->controller->routeParams = array('case' => 54, 'licence' => 7, 'action' => 'recommendation', 'id' => 8);
        /*$this->controller->expects($this->once())
             ->method('backToCaseButton');*/
        
        $this->controller->expects($this->once())
            ->method('setBreadcrumb')
            ->with();
        
        $this->controller->expects($this->once())
            ->method('formView')
            ->with('decision');
        
        $this->controller->decisionAction();
    }
    
    public function testCreateSubmission()
    {
        
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
