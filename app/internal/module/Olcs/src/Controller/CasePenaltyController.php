<?php

/**
 * Case Penalty Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Olcs\Controller;

/**
 * Case Penalty Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class CasePenaltyController extends CaseController
{
    public function indexAction()
    {
        $caseId = $this->fromRoute('case');
        $licence = $this->fromRoute('licence');
        
        $penalties = array();
        $results = $this->makeRestCall('Penalty', 'GET', array('vosaCase' => $caseId));

        if ($results['Count']) {
            $penalties = $results['Results'][0];
        }

        $form = $this->generatePenaltyForm($penalties);

        $this->setBreadcrumb(array('licence_case_list/pagination' => array('licence' => $licence)));

        $view = $this->getView();
        $tabs = $this->getTabInformationArray();

        $case = $this->getCase($caseId);
        $summary = $this->getCaseSummaryArray($case);
        $details = $this->getCaseDetailsArray($case);

        $view->setVariables([
            'case' => $case,
            'tabs' => $tabs,
            'tab' => 'penalties',
            'summary' => $summary,
            'details' => $details,
            'commentForm' => $form,
        ]);

        $view->setTemplate('case/manage');
        return $view;
    }

    /**
     * Creates and returns the penalty form.
     *
     * @param array $case
     * @return \Zend\Form
     */
    public function generatePenaltyForm($penalty)
    {
        $form = $this->generateForm(
            'penalty-comment',
            'savePenaltyForm'
        );
        $form->setData($penalty);

        return $form;
    }

    /**
     * Saves the penalty form.
     *
     * @param array $data
     */
    public function savePenaltyForm($data)
    {
        $data['case'] = 24;
        $data['fields'] = $data;

        if (!empty($data['fields']['id'])) {
            $this->processEdit($data, 'Penalty');
        } else {
            $this->processAdd($data, 'Penalty');
        }

        return $this->redirect()->toRoute('case_penalty', array('licence' => 7, 'case' => 24));
    }
}
