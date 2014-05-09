<?php

/**
 * Case Conviction Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace Olcs\Controller;

use Common\Controller\FormActionController;

/**
 * Case Conviction Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class NoteFormController extends FormActionController
{
    public function addAction()
    {
        $postParams = $this->params()->fromPost();
        $routeParams = $this->params()->fromRoute();
        
        $this->setBreadcrumb(
            array(
                'licence_case_list/pagination' => array('licence' => $routeParams['licence']),
                'case_manage' => array(
                        'case' => $routeParams['case'],
                        'licence' => $routeParams['licence'],
                        'tab' => 'overview'
                ),
                'submission' => array(
                        'case' => $routeParams['case'],
                        'licence' => $routeParams['licence'],
                        'id' => $routeParams['typeId'],
                        'action' => 'edit'
                )
            )
        );
        
        $form = $this->generateNoteForm(array());

        $view = $this->getViewModel(
            array(
            'form' => $form,
            'params' => array(
                'pageTitle' => "add-{$routeParams['type']}-note",
                'pageSubTitle' => array("add-{$routeParams['type']}-note-text", $routeParams['section'])
                )
            )
        );

        $view->setTemplate('form');
        return $view;
    }

    /**
     * Creates and returns the comment form.
     *
     * @param array $case
     * @return \Zend\Form
     */
    public function generateNoteForm($case)
    {
        $form = $this->generateForm(
            'note',
            'saveNoteForm'
        );
        $form->setData($case);

        return $form;
    }

    /**
     * Saves the comment form.
     *
     * @param array $data
     */
    public function saveNoteForm($data)
    {
        $routeParams = $this->params()->fromRoute();
        $postParams = $this->params()->fromPost();
//print_r($postParams);
        $submission = $this->makeRestCall($routeParams['type'], 'GET', array('id' => $routeParams['typeId']));
        $submissionData = json_decode($submission['text'], true);
        $newNote = array();
        $newNote['note'] = $postParams['main']['note'];
        $newNote['userId'] = $this->getLoggedInUser();
        $newNote['date'] = date("c");
        $submissionData[$routeParams['section']]['notes'][] = $newNote;
        $data = array();
        $data['id'] = $submission['id'];
        $data['version'] = $submission['version'];
        $data['text'] = json_encode($submissionData);
        
//print_r($data);
        $this->processEdit($data, $routeParams['type']);

        $routeParams = $this->params()->fromRoute();
        $routeParams['action'] = 'edit';
        $routeParams['id'] = $routeParams['typeId'];
        return $this->redirect()->toRoute('submission', $routeParams);
    }
}
