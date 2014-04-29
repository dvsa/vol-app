<?php
/**
 * Submission controller
 * Create, view and modify submissions
 * @author Mike Cooper <michael.cooper@valtech.co.uk>
 */

namespace Olcs\Controller;

use Common\Controller\FormActionController;
use Zend\View\Model\ViewModel;

class SubmissionController extends FormActionController
{

    /**
     * Add submission action
     * @return ViewModel
     */
    public function addAction()
    {
        $routeParams = $this->getParams(array('case', 'licence', 'id', 'action'));
        $this->setBreadcrumb(
            array(
                'licence_case_list/pagination' => array('licence' => $routeParams['licence']),
                'case_manage' => array(
                    'case' => $routeParams['case'],
                    'licence' => $routeParams['licence'],
                    'tab' => 'overview'
                )
            )
        );
        
        $submission = $this->createSubmission($routeParams);
        $data = array(
            'createdBy' => 1,
            'text' => $submission,
            'vosaCase' => $routeParams['case'],
        );
        
        if ($this->getRequest()->isPost()) {
            $result = $this->processAdd($data, 'Submission');
        }
        
        $submission = json_decode($submission, true);
        $routeParams['id'] = $result['id'];
        $routeParams['action'] = 'post';
        $formAction = $this->url()->fromRoute('submission', $routeParams);
        $view = $this->getViewModel(
            array(
                'params' => array(
                    'formAction' => $formAction,
                    'pageTitle' => 'case-submission',
                    'pageSubTitle' => 'case-submission-text',
                    'data' => $submission
                )
            )
        );
        
        $view->setTemplate('submission/page');
        return $view;
    }
    
    /**
     * 
     * @return type
     */
    public function postAction()
    {
        $routeParams = $this->getParams(array('case', 'licence', 'id'));
        $params = array(
            'case' => $routeParams['case'],
            'licence' => $routeParams['case'],
            'id' => $routeParams['id']);
        if ($this->params()->fromPost('decision')) {
            $params['action'] = 'decision';
        } elseif ($this->params()->fromPost('recommend')) {
            $params['action'] = 'recomendation';
        }
        return $this->redirect()->toRoute(
            'submission',
            $params
        );
    }
    
    /**
     * Recommendation form
     * @return type
     */
    public function recomendationAction()
    {
        return $this->formView('recommend');
    }
    
    /**
     * Decision form
     * @return type
     */
    public function decisionAction()
    {
        return $this->formView('decision');
    }
    
    /**
     * Return json encoded submission
     * @param type $routeParams
     * @return type
     */
    public function createSubmission($routeParams)
    {
        $licenceData = $this->makeRestCall('Licence', 'GET', array('id' => $routeParams['licence']));
        $caseData = array();
        //$caseData = $this->makeRestCall('VosaCase', 'GET', array('id' => $routeParams['case']));
        $submission = array();
        $submission['case-summary-info'] = $caseData;
        $submission['persons'] = array();
        if (in_array(strtolower($licenceData['licenceType']), array('standard national', 'standard international'))) {
            $submission['transport-managers'] = array();
        }
        $submission['outstanding-applications']['data'] = array();
        $submission['outstanding-applications']['notes'] = array();
        $submission['environmental'] = array();
        $submission['objections'] = array();
        $submission['representations'] = array();
        $submission['previous-history'] = array();
        $submission['operating-centre'] = array();
        $submission['conditions'] = array();
        $submission['undertakings'] = array();
        $submission['annual-test-history'] = array();
        $submission['prohibition-history'] = array();
        $submission['conviction-history'] = array();
        if (strtolower($licenceData['goodsOrPsv']) == 'psv') {
            $submission['bus-services-registered'] = array();
            $submission['bus-compliance-issues'] = array();
        }
        $submission['current-submission'] = array();
        //$submission['recommendation-decision'] = array();
        $jsonSubmission = json_encode($submission);
        return $jsonSubmission;
    }
    
    /**
     * Gets the view for the form based on type
     * @param type $type
     * @return type
     */
    public function formView($type)
    {
        $form = $this->getFormWithUsers(
            $type,
            array(
                'submission' => $this->params()->fromRoute('id'),
                'userSender' => 2)
        );
        $form = $this->formPost($form, 'processRecDecForm');
        $view = $this->getViewModel(
            array(
                'form' => $form,
                'params' => array(
                    'pageTitle' => "submission-$type",
                    'pageSubTitle' => "submission-$type-text",
                )
            )
        );
        $view->setTemplate('form');
        return $view;
    }
    
    /**
     * Adds a SubmissionAction entry
     * @param type $data
     * @return type
     */
    public function processRecDecForm($data)
    {
        $data = array_merge($data, $data['main']);
        $result = $this->processAdd($data, 'SubmissionAction');
        $routeParams = $this->getParams(array('case', 'licence', 'id'));
        return $this->redirect()->toRoute(
            'case_manage',
            array(
                'case' => $routeParams['case'],
                'licence' => $routeParams['licence'],
                'tab' => 'overview')
        );
    }
    
    /**
     * Gets user list for recipients
     * @return type
     */
    private function getUserList()
    {
        $users = $this->makeRestCall('User', 'GET', array());
        $userList = [];
        foreach ($users['Results'] as $user) {
            $userList[$user['id']] = $user['displayName'];
        }
        return $userList;
    }
    
    /**
     * Gets a form for the form type and populates the Send to list with users
     * @param type $userList
     * @param type $formType
     * @return type
     */
    private function getFormWithUsers($formType, $data = array())
    {
        $userList = $this->getUserList();
        $generator = $this->getFormGenerator();
        $formConfig = $generator->getFormConfig($formType);
        $formConfig[$formType]['fieldsets']['main']['elements']['userRecipient']['value_options'] = $userList;
        $form = $generator->setFormConfig($formConfig)->createForm($formType);
        $form->setData($data);
        return $form;
    }
}
