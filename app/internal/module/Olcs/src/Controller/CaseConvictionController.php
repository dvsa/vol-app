<?php

/**
 * Case Conviction Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace Olcs\Controller;

/**
 * Case Conviction Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class CaseConvictionController extends CaseController
{
    public function indexAction()
    {
        $licence = $this->fromRoute('licence');
        $caseId = $this->fromRoute('case');
        
        $this->setBreadcrumb(array('licence_case_list/pagination' => array('licence' => $licence)));
        
        if ($this->params()->fromPost('action')) {
            return $this->redirect()->toRoute($this->params()->fromPost('table'), array('licence' => $licence,
                        'case' => $caseId,
                        'id' => $this->params()->fromPost('id') ? $this->params()->fromPost('id') : '',
                        'action' => strtolower($this->params()->fromPost('action'))));
        }

        $view = $this->getView();
        $tabs = $this->getTabInformationArray();
        $action = 'convictions';
        $caseId = $this->fromRoute('case');

        $case = $this->getCase($caseId);

        $form = $this->generateCommentForm($case);

        $summary = $this->getCaseSummaryArray($case);
        $details = $this->getCaseDetailsArray($case);

        $results = $this->makeRestCall('Conviction', 'GET', array('vosaCase' => $caseId));

        $data = [];
        $data['url'] = $this->getPluginManager()->get('url');

        $table = $this->getServiceLocator()->get('Table')->buildTable('convictions', $results, $data);

        $view->setVariables([
            'case' => $case,
            'tabs' => $tabs,
            'tab' => $action,
            'summary' => $summary,
            'details' => $details,
            'table' => $table,
            'commentForm' => $form,
        ]);

        $view->setTemplate('case/manage');
        return $view;
    }

    /**
     * Creates and returns the comment form.
     *
     * @param array $case
     * @return \Zend\Form
     */
    public function generateCommentForm($case)
    {
        $form = $this->generateForm(
            'conviction-comment',
            'saveCommentForm'
        );
        $form->setData($case);

        return $form;
    }

    /**
     * Saves the comment form.
     *
     * @param array $data
     */
    public function saveCommentForm($data)
    {
        /* print_r($data); */

        $data = array_intersect_key($data, array_flip(['id', 'convictionData', 'version']));

        /* print_r($data);
        die(); */

        $this->processEdit($data, 'VosaCase');

        return $this->redirect()->toRoute('case_convictions', array('case' => $data['id']));
    }
}
