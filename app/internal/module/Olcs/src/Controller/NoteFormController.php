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
    public function indexAction()
    {
        $postParams = $this->params()->fromPost();
        $routeParams = $this->params()->fromRoute();
        
        $this->setBreadcrumb(array('licence_case_list/pagination' => array('licence' => $routeParams['licence'])));
        
        if (isset($postParams['action'])) {
            return $this->redirect()->toRoute($postParams['table'], array('licence' => $routeParams['licence'],
                        'case' => $routeParams['case'],
                        'id' => isset($postParams['id']) ? $postParams['id'] : '',
                        'action' => strtolower($postParams['action'])));
        }

        $form = $this->generateNoteForm(array());

        $view = $this->getViewModel(
            array(
            'form' => $form,
            'params' => array(
                'pageTitle' => "add-{$routeParams['type']}-note",
                'pageSubTitle' => array("add-{$routeParams['type']}-note-text", "case-summary-info")
            )
            )
        );

        $view->setTemplate('form');
        return $view;
        print 'note controller';
        return false;
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
        $data = array_intersect_key($data, array_flip(['id', 'convictionData', 'version']));
        $this->processEdit($data, 'VosaCase');

        return $this->redirect()->toRoute('case_convictions', array('case' => $data['id']));
    }
}
