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
    /* *
     * Index action loads form data
     *
     * @return \Zend\Form
     */
    public function indexAction()
    {
        $caseId = $this->fromRoute('case');
        $licence = $this->fromRoute('licence');

        $penalties = array();

        $bundle = array(
            'children' => array(
                'case' => array(
                    'properties' => array(
                        'id'
                    )
                )
            )
        );

        $result = $this->makeRestCall('Penalty', 'GET', array('case' => $caseId, 'bundle' => json_encode($bundle)));

        if ($result['Count']) {
            $penalties = $result['Results'][0];
            $penalties['case'] = $penalties['case']['id'];
        } else {
            $penalties['case'] = $caseId;
        }

        $form = $this->generatePenaltyForm($penalties);

        $this->setBreadcrumb(array('licence_case_list/pagination' => array('licence' => $licence)));

        $view = $this->getView();
        $tabs = $this->getTabInformationArray();

        $case = $this->getCase($caseId);
        $summary = $this->getCaseSummaryArray($case);

        $view->setVariables(
            array(
                'case' => $case,
                'tabs' => $tabs,
                'tab' => 'penalties',
                'summary' => $summary,
                'commentForm' => $form,
            )
        );

        $view->setTemplate('case/manage');
        return $view;
    }

    /**
     * Creates and returns the penalty form.
     *
     * @param array $case
     * @return \Zend\Form
     */
    private function generatePenaltyForm($penalty)
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
        unset($data['cancel']);

        if ($data['submit'] === '') {
            if (!empty($data['id'])) {
                $this->processEdit($data, 'Penalty');
            } else {
                $this->processAdd($data, 'Penalty');
            }
        }

        return $this->redirect()->toRoute('case_penalty', array(), array(), true);
    }
}
