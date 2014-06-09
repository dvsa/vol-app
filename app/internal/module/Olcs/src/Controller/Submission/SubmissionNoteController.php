<?php

/**
 * Submission Notes Controller
 *
 * @author Mike Cooper <michael.cooper@valtech.co.uk>
 */
namespace Olcs\Controller\Submission;

use Common\Controller\FormActionController;

/**
 * Submission Notes Controller
 *
 * @author Mike Cooper <michael.cooper@valtech.co.uk>
 */
class SubmissionNoteController extends FormActionController
{

    public $routeParams = array();

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $this->routeParams = $this->params()->fromRoute();
        parent::onDispatch($e);
    }

    public function addAction()
    {
        $this->setBreadcrumb(
            array(
                'licence_case_list/pagination' => array('licence' => $this->routeParams['licence']),
                'case_manage' => array(
                    'case' => $this->routeParams['case'],
                    'licence' => $this->routeParams['licence'],
                    'tab' => 'overview'
                ),
                'submission' => array(
                    'case' => $this->routeParams['case'],
                    'licence' => $this->routeParams['licence'],
                    'id' => $this->routeParams['typeId'],
                    'action' => 'edit'
                )
            )
        );

        if (isset($_POST['cancel-note'])) {
            return $this->backToSubmissionButton();
        }

        $callback = 'processNote';
        $submission = $this->makeRestCall('Submission', 'GET', array('id' => $this->routeParams['typeId']));
        $form = $this->generateNoteForm(
            array('version' => $submission['version']), $callback
        );

        $view = $this->getViewModel(
            array(
                'form' => $form,
                'params' => array(
                    'pageTitle' => "{$this->routeParams['action']}-{$this->routeParams['type']}-note",
                    'pageSubTitle' => array("{$this->routeParams['action']}-{$this->routeParams['type']}-note-text",
                        $this->routeParams['section']
                    )
                )
            )
        );

        $view->setTemplate('form');
        return $view;
    }

    public function backToSubmissionButton()
    {
        return $this->redirect()->toRoute(
            'submission',
            array(
                'case' => $this->routeParams['case'],
                'licence' => $this->routeParams['licence'],
                'id' => $this->routeParams['typeId'],
                'action' => 'edit'
            )
        );
    }

    /**
     * Creates and returns the comment form.
     *
     * @param array $case
     * @return \Zend\Form
     */
    public function generateNoteForm($data, $callback)
    {
        $form = $this->generateForm(
            'note', $callback
        );
        $form->setData($data);

        return $form;
    }

    /**
     * Saves the comment form.
     *
     * @param array $data
     */
    public function createNote($submissionData)
    {
        $postParams = $this->params()->fromPost();
        $newNote = array();
        $newNote['note'] = $postParams['main']['note'];
        $user = $this->makeRestCall('User', 'GET', array('id' => $this->getLoggedInUser()));
        $newNote['user'] = $user['name'];
        $newNote['date'] = date("c");
        $i = count($submissionData[$this->routeParams['section']]['notes']) + 1;
        $submissionData[$this->routeParams['section']]['notes'][$i] = $newNote;

        return $submissionData;
    }

    public function processNote($data)
    {
        $postParams = $this->params()->fromPost();

        $submission = $this->makeRestCall(
            $this->routeParams['type'], 'GET', array('id' => $this->routeParams['typeId'])
        );
        $submissionData = json_decode($submission['text'], true);

        $submissionData = $this->createNote($submissionData);

        $data = array();
        $data['id'] = $submission['id'];
        $data['version'] = $postParams['version'];
        $data['text'] = json_encode($submissionData);

        $this->processEdit($data, $this->routeParams['type']);

        $routeParams = $this->routeParams;
        $routeParams['action'] = 'edit';
        $routeParams['id'] = $routeParams['typeId'];

        return $this->redirect()->toRoute('submission', $routeParams);
    }
}
