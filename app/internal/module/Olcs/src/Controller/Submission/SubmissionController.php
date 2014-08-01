<?php

/**
 * Submission controller
 * Create, view and modify submissions
 *
 * @author Mike Cooper <michael.cooper@valtech.co.uk>
 */

namespace Olcs\Controller\Submission;

use Common\Controller\CrudInterface;
use Common\Controller\FormActionController;
use Zend\View\Model\ViewModel;

/**
 * Submission controller
 * Create, view and modify submissions
 *
 * @author Mike Cooper <michael.cooper@valtech.co.uk>
 */
class SubmissionController extends FormActionController implements CrudInterface
{
    use SubmissionSectionTrait;

    public $routeParams = array();

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $this->routeParams = $this->getParams(array('case', 'licence', 'id', 'action'));
        $this->submissionConfig = $this->getServiceLocator()->get('config')['submission_config'];
        parent::onDispatch($e);
    }

    /**
     * Does what it says on the tin.
     *
     * @return mixed
     */
    public function redirectToIndex()
    {
        $licenceId = $this->fromRoute('licence');
        $caseId = $this->fromRoute('case');
        $id = $this->fromRoute('id');

        return $this->redirect()->toRoute(
            'submission',
            array(
                'action' => 'edit',
                'licence' => $licenceId,
                'case' => $caseId,
                'id' => $id,
            )
        );
    }

    /**
     * @return mixed
     */
    public function deleteAction()
    {
        $this->response->setStatusCode(501);

        return array(
            'content' => 'Delete Method Not Implemented in ' . __CLASS__
        );
    }

    /**
     * Gets a variable from the route
     *
     * @param string $param
     * @param mixed $default
     * @return type
     */
    public function fromRoute($param, $default = null)
    {
        return $this->params()->fromRoute($param, $default);
    }

    /**
     * Add submission action
     * @return ViewModel
     */
    public function addAction()
    {
        $this->setSubmissionBreadcrumb();

        $submission = $this->createSubmission($this->routeParams);
        $data = array(
            'createdBy' => $this->getLoggedInUser(),
            'text' => $submission,
            'vosaCase' => $this->routeParams['case'],
        );

        if ($this->getRequest()->isPost()) {
            $result = $this->processAdd($data, 'Submission');
            //$result = array('id' => 999);
            return $this->redirect()->toRoute(
                'submission',
                array(
                    'licence' => $this->routeParams['licence'],
                    'case' => $this->routeParams['case'],
                    'id' => $result['id'],
                    'action' => 'edit'
                )
            );
        }

        $submission = json_decode($submission, true);
        $submissionView = array();
        $submissionView['data'] = $submission;
        return $this->getSubmissionView($submissionView);
    }

    /**
     * Edit a submission
     * @return type
     */
    public function editAction()
    {
        $this->setSubmissionBreadcrumb();
        if ($this->getRequest()->isPost()) {
            return $this->redirect()->toRoute(
                'submission',
                array(
                    'licence' => $this->routeParams['licence'],
                    'case' => $this->routeParams['case'],
                    'id' => $this->params()->fromPost('id'),
                    'action' => 'edit'
                )
            );
        }

        $submission = $this->getEditSubmissionData();
        return $this->getSubmissionView($submission);
    }

    /**
     * Add a section note
     * @return type
     */
    public function addnoteAction()
    {
        $postParams = $this->params()->fromPost();
        if ($this->getRequest()->isPost()) {
            $this->routeParams['type'] = 'submission';
            $this->routeParams['typeId'] = $this->routeParams['id'];
            $this->routeParams['section'] = $postParams['section'];
            $this->routeParams['action'] = 'add';
            unset($this->routeParams['id']);
            return $this->redirect()->toRoute('note', $this->routeParams);
        }
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
                        'piReasons' => array(
                            'properties' => 'ALL'
                        ),
                    )
                )
            )
        );
        $submissionData = $this->makeRestCall('Submission', 'GET', array('id' => $this->routeParams['id']), $bundle);
        $submissionActions = $this->getServiceLocator()->get('config');
        $submissionActions = $submissionActions['static-list-data'];

        $submission['data'] = json_decode($submissionData['text'], true);
        foreach ($submissionData['submissionActions'] as &$action) {
            $actions = isset($submissionActions['submission_' . $action['submissionActionType']])
                ? $submissionActions['submission_' . $action['submissionActionType']]
                : '';
            $action['submissionActionStatus'] = $actions[$action['submissionActionStatus']];
        }
        $submission['submissionActions'] = $submissionData['submissionActions'];
        krsort($submission['submissionActions']);

        return $submission;
    }

    /**
     * Returns a submission view containing all sections for add and edit
     * @param type $submission
     * @return type
     */
    public function getSubmissionView($submission)
    {
        $submissionViews = $this->getSubmissionSectionViews($submission['data']);
        $submission['views'] = $submissionViews;
        $this->routeParams['action'] = 'post';
        $formAction = $this->url()->fromRoute('submission', $this->routeParams);
        $view = $this->getViewModel(
            array(
                'params' => array(
                    'formAction' => $formAction,
                    'routeParams' => $this->routeParams,
                    'pageTitle' => 'case-submission',
                    'pageSubTitle' => 'case-submission-text',
                    'submission' => $submission,
                    'submissionConfig' => $this->submissionConfig['sections']
                )
            )
        );

        $view->setTemplate('submission/page');
        return $view;
    }

    /**
     * Gets a rendered version of each section to pass to the main view
     * @param array $sections
     * @return type
     */
    public function getSubmissionSectionViews(array $sections)
    {
        $viewRender = $this->getServiceLocator()->get('ViewRenderer');
        $renderedViews = array();
        foreach ($sections as $sectionName => $section) {
            $view = $this->getViewModel(array('sectionData' => $section['data']));
            if (isset($this->submissionConfig['sections'][$sectionName]['view'])) {
                $view->setTemplate($this->submissionConfig['sections'][$sectionName]['view']);
            } else {
                $view->setTemplate('submission/partials/blank');
            }
            $renderedViews[$sectionName]['view'] = $viewRender->render($view);
            $renderedViews[$sectionName]['notes'] = $section['notes'];
        }
        return $renderedViews;
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
        $this->setSubmissionBreadcrumb($this->getRecDecBreadcrumb());
        return $this->formView('recommend');
    }

    /**
     * returns decision form
     * @return type
     */
    public function decisionAction()
    {
        $this->setSubmissionBreadcrumb($this->getRecDecBreadcrumb());
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
     * Gets the view for the form based on type
     * @param type $type
     * @return type
     */
    public function formView($type)
    {
        $form = $this->getFormWithListData(
            $type, array(
            'submission' => $this->routeParams['id'],
            'userSender' => $this->getLoggedInUser())
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
        $this->processAdd($data, 'SubmissionAction');
        return $this->redirect()->toRoute(
            'case_manage',
            array(
                'case' => $this->routeParams['case'],
                'licence' => $this->routeParams['licence'],
                'tab' => 'overview'
            )
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
                'action' => 'edit'
            )
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
            $userList[$user['id']] = $user['name'];
        }
        return $userList;
    }

    /**
     * Gets a form for the form type and populates the Send to list with users
     * 
     * @param string $formType
     * @param array $data
     * 
     * @return type
     */
    public function getFormWithListData($formType, $data = array())
    {
        $userList = $this->getUserList();
        $piReasons = $this->getPiReasons();

        $form = $this->getForm($formType);
        $form->get('main')->get('userRecipient')->setValueOptions($userList);
        $form->get('main')->get('piReasons')->setValueOptions($piReasons);

        $form->setData($data);
        return $form;
    }

    /**
     * 
     * Get Pi Reasons based on licence type and whether the licence is from 
     * Northern Ireland
     * 
     * @param string $licenceType
     * @param bool $isNi
     * @return type
     */
    private function getPiReasons()
    {
        $reasons = [];

        $licence = $this->makeRestCall(
            'Licence',
            'GET',
            [
                'id' => $this->routeParams['licence']
            ]
        );

        //licence type should really be a lookup table in
        //both Licence and PiReason entities!
        switch (strtolower($licence['goodsOrPsv'])) {
            case 'goods':
                $goodsOrPsv = 'GV';
                break;
            case 'psv':
                $goodsOrPsv = 'PSV';
                break;
            default:
                return $this->notFoundAction();
        }

        $piReasons = $this->makeRestCall(
            'PiReason',
            'GET',
            [
                'goodsOrPsv' => $goodsOrPsv,
                'isNi' => $licence['niFlag'],
                'limit' => 'all'
            ]
        );

        foreach ($piReasons['Results'] as $result) {
            $reasons[$result['id']] = mb_substr($result['sectionCode'] . ' - ' . $result['description'], 0, 150);
        }

        return $reasons;
    }

    /**
     * Overrides abstract class to set breadcrumb for all submission routes
     * @param type $navRoutes
     */
    public function setSubmissionBreadcrumb($navRoutes = array())
    {
        $thisNavRoutes = array(
            'licence_case_list/pagination' => array('licence' => $this->routeParams['licence']),
            'case_manage' => array(
                'case' => $this->routeParams['case'],
                'licence' => $this->routeParams['licence'],
                'tab' => 'overview'
            )
        );
        $allNavRoutes = array_merge($thisNavRoutes, $navRoutes);
        $this->setBreadcrumb($allNavRoutes);
    }
}
