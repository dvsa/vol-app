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
    
    public $routeParams = array();
    
    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $this->routeParams = $this->getParams(array('case', 'licence', 'id', 'action'));
        parent::onDispatch($e);
    }

    /**
     * Add submission action
     * @return ViewModel
     */
    public function addAction()
    {
        $this->setBreadcrumb();
        
        $submission = $this->createSubmission($this->routeParams);
        $data = array(
            'createdBy' => 1,
            'text' => $submission,
            'vosaCase' => $this->routeParams['case'],
        );
        
        if ($this->getRequest()->isPost()) {
            $result = $this->processAdd($data, 'Submission');
            //$result = array('id' => 999);
            return $this->redirect()->toRoute('submission', array('licence' => $this->routeParams['licence'],
                        'case' => $this->routeParams['case'],
                        'id' => $result['id'],
                        'action' => strtolower($this->routeParams['action'])));
        }
        
        $submission = json_decode($submission, true);
        $submissionView = array();
        $submissionView['data'] = $submission;
        return $this->getSubmissionView($submissionView);
    }
    
    /**
     * Edit a conviction
     * @return type
     */
    public function editAction()
    {
        $this->setBreadcrumb();
        if ($this->getRequest()->isPost()) {
            return $this->redirect()->toRoute('submission', array('licence' => $this->routeParams['licence'],
                        'case' => $this->routeParams['case'],
                        'id' => $this->params()->fromPost('id'),
                        'action' => 'edit'));
        }
        
        $submission = $this->getEditSubmissionData();
        return $this->getSubmissionView($submission);
    }
    
    public function getEditSubmissionData()
    {
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
        $submissionData = $this->makeRestCall('Submission', 'GET', array('id' => $this->routeParams['id']), $bundle);
        $submissionActions = $this->getServiceLocator()->get('config');
        $submissionActions = $submissionActions['static-list-data'];
        
        $submission['data'] = json_decode($submissionData['text']);
        foreach ($submissionData['submissionActions'] as &$action) {
            $actions = isset($submissionActions['submission_'.$action['submissionActionType']])
                    ? $submissionActions['submission_'.$action['submissionActionType']] : '';
            $action['submissionActionStatus'] = $actions[$action['submissionActionStatus']];
        }
        $submission['submissionActions'] = $submissionData['submissionActions'];
        return $submission;
    }
    
    /**
     * Returns a submission view for add and edit
     * @param type $submission
     * @return type
     */
    public function getSubmissionView($submission)
    {
        $this->routeParams['action'] = 'post';
        $formAction = $this->url()->fromRoute('submission', $this->routeParams);
        $view = $this->getViewModel(
            array(
                'params' => array(
                    'formAction' => $formAction,
                    'pageTitle' => 'case-submission',
                    'pageSubTitle' => 'case-submission-text',
                    'submission' => $submission
                )
            )
        );
        
        $view->setTemplate('submission/page');
        return $view;
    }
    
    /**
     * Redirects to either recommendation or decision from submission
     * @return type
     */
    public function postAction()
    {
        $params = array(
            'case' => $this->routeParams['case'],
            'licence' => $this->routeParams['licence'],
            'id' => $this->routeParams['id']);
        if ($this->params()->fromPost('decision')) {
            $params['action'] = 'decision';
        } elseif ($this->params()->fromPost('recommend')) {
            $params['action'] = 'recommendation';
        }
        return $this->redirect()->toRoute(
            'submission',
            $params
        );
    }
    
    /**
     * returns recommendation form
     * @return type
     */
    public function recommendationAction()
    {
        if (isset($_POST['cancel-submission'])) {
            return $this->backToCaseButton();
        }
        $this->setBreadcrumb($this->getRecDecBreadcrumb());
        return $this->formView('recommend');
    }
    
    /**
     * returns decision form
     * @return type
     */
    public function decisionAction()
    {
        if (isset($_POST['cancel-submission'])) {
            return $this->backToCaseButton();
        }
        $this->setBreadcrumb($this->getRecDecBreadcrumb());
        return $this->formView('decision');
    }
    
    private function getRecDecBreadcrumb()
    {
        return array(
                'submission' => array(
                    'case' => $this->routeParams['case'],
                    'licence' => $this->routeParams['licence'],
                    'action' => $this->routeParams['action'],
                    'id' => $this->routeParams['id']
                ),
            );
    }
    
    /**
     * Return json encoded submission based on submission_config
     * @param type $routeParams
     * @return type
     */
    public function createSubmission($routeParams)
    {
        $licenceData = $this->makeRestCall('Licence', 'GET', array('id' => $routeParams['licence']));
        $submissionConfig = $this->getServiceLocator()->get('config')['submission_config'];
        $submission = array();
        foreach ($submissionConfig['sections'] as $section => $config) {
            if ($this->submissionExclude($section, $config, $licenceData)) {
                $submission[$section]['data'] = array();
                $submission[$section]['notes'] = null;
            }
        }
        $jsonSubmission = json_encode($submission);
        return $jsonSubmission;
    }
    
    /**
     * builds a submission and excludes sections based on rules in
     * the submission config
     * @param type $section
     * @param type $config
     * @param type $licenceData
     * @return boolean
     */
    public function submissionExclude($section, $config, $licenceData)
    {
        if (!isset($config['exclude'])) {
            return true;
        }
        if (in_array(strtolower($licenceData[$config['exclude']['column']]), $config['exclude']['values'])) {
            return true;
        }
        return false;
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
                'userSender' => 1)
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
        return $this->redirect()->toRoute(
            'case_manage',
            array(
                'case' => $this->routeParams['case'],
                'licence' => $this->routeParams['licence'],
                'tab' => 'overview')
        );
    }
    
    public function backToCaseButton()
    {
        return $this->redirect()->toRoute(
            'submission',
            array(
                'case' => $this->routeParams['case'],
                'licence' => $this->routeParams['licence'],
                'id' => $this->routeParams['id'],
                'action' => 'edit')
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
    
    /**
     * Overrides abstract class to set breadcrumb for all submission routes
     * @param type $navRoutes
     */
    public function setBreadcrumb($navRoutes = array())
    {
        $thisNavRoutes = array(
                'licence_case_list/pagination' => array('licence' => $this->routeParams['licence']),
                'case_manage' => array(
                    'case' => $this->routeParams['case'],
                    'licence' => $this->routeParams['licence'],
                    'tab' => 'overview'
                ));
        $allNavRoutes = array_merge($thisNavRoutes, $navRoutes);
        parent::setBreadcrumb($allNavRoutes);
    }
}
