<?php

/**
 * Case Prohibition Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Olcs\Controller;

/**
 * Case Prohibition Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class CaseProhibitionController extends CaseController
{

    /**
     * Index action loads the form data
     *
     * @return \Zend\Form\Form
     */
    public function indexAction()
    {
        $caseId = $this->fromRoute('case');
        $licence = $this->fromRoute('licence');
        $action = $this->fromPost('action');

        if ($action) {
            $action = strtolower($action);

            if ($action == 'add') {
                return $this->redirectToCrud($action, null);
            } elseif ($id) {
                return $this->redirectToCrud($action, $id);
            }
        }

        $prohibition = array();

        $bundle = array(
            'children' => array(
                'case' => array(
                    'properties' => array(
                        'id'
                    )
                ),
                'prohibitionType' => array(
                    'properties' => array(
                        'handle',
                        'comment'
                    )
                )
            )
        );

        $result = $this->makeRestCall('Prohibition', 'GET', array('case' => $caseId, 'bundle' => json_encode($bundle)));

        if ($result['Count']) {
            $prohibition = $result['Results'][0];
            $prohibition['case'] = $prohibition['case']['id'];
            $table = $this->buildTable('prohibition', [0 => $prohibition]);
        } else {
            $prohibition['case'] = $caseId;
            $table = $this->buildTable('prohibition', []);
        }

        $prohibitionNote = $this->makeRestCall('ProhibitionNote', 'GET', array('case' => $caseId));
        $prohibitionNote['case'] = $caseId;

        if ($prohibitionNote['Count']) {
            $prohibitionNote = $prohibitionNote['Results'][0];
        }

        $prohibitionNote['case'] = $caseId;

        $form = $this->generateProhibitionNoteForm($prohibitionNote);

        $this->setBreadcrumb(array('licence_case_list/pagination' => array('licence' => $licence)));

        $view = $this->getView();
        $tabs = $this->getTabInformationArray();

        $case = $this->getCase($caseId);
        $summary = $this->getCaseSummaryArray($case);

        $view->setVariables(
            [
                'case' => $case,
                'tabs' => $tabs,
                'tab' => 'prohibitions',
                'table' => $table,
                'summary' => $summary,
                'commentForm' => $form,
            ]
        );

        $view->setTemplate('case/manage');
        return $view;
    }

    /**
     * Add action
     *
     * @return void|\Zend\View\Model\ViewModel
     */
    public function addAction()
    {
        $licenceId = $this->fromRoute('licence');
        $caseId = $this->fromRoute('case');

        $this->setBreadcrumb(
            array(
                'licence_case_list/pagination' => array('licence' => $licenceId),
                'case_prohibition' => array('licence' => $licenceId, 'case' => $caseId)
            )
        );

        $form = $this->generateFormWithData(
            'prohibition',
            'processAddProhibition',
            array(
                'case' => $caseId
            )
        );

        $view = $this->getView(
            [
                'params' => [
                    'pageTitle' => 'Add prohibition',
                    'pageSubTitle' => ''
                ],
                'form' => $form,
            ]
        );

        $view->setTemplate('prohibition/form');

        return $view;
    }

    /**
     * Edit action
     *
     * @return void|\Zend\View\Model\ViewModel
     */
    public function editAction()
    {

    }

    /**
     * Creates and returns the prohibition form.
     *
     * @param array $prohibition
     * @return \Zend\Form\Form
     */
    private function generateProhibitionNoteForm($prohibition)
    {
        $form = $this->generateForm(
            'prohibition-comment',
            'saveProhibitionNoteForm'
        );
        $form->setData($prohibition);

        return $form;
    }

    /**
     * Saves the prohibition notes form.
     *
     * @param array $data
     * @return Redirect
     */
    public function saveProhibitionNoteForm($data)
    {
        unset($data['cancel']);

        if ($data['submit'] === '') {
            if (!empty($data['id'])) {
                $this->processEdit($data, 'ProhibitionNote');
            } else {
                $this->processAdd($data, 'ProhibitionNote');
            }
        }

        return $this->redirect()->toRoute('case_prohibition', array(), array(), true);
    }

    /**
     * Processes the add prohibition form
     *
     * @param array $data
     */
    public function processAddProhibition ($data)
    {
        $result = $this->processAdd($data, 'Prohibition');

        if (isset($result['id'])) {
            return $this->redirectToAction();
        }

        return $this->redirectToAction('add');
    }

    /**
     * Redirects to the add or edit action
     *
     * @param string $action
     * @param int $id
     * @return Redirect
     */
    private function redirectToCrud($action, $id = null)
    {
        return $this->redirect()->toRoute(
            'case_prohibition',
            array(
                'action' => $action,
                'id' => $id,
            ),
            array(),
            true
        );
    }
}
