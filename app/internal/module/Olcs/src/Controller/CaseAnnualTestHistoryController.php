<?php

/**
 * Case Prohibition Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Olcs\Controller;

/**
 * Case Prohibition Controller
 */
class CaseAnnualTestHistoryController extends CaseController
{

    /**
     * Index action loads the form data
     *
     * @return \Zend\View
     */
    public function indexAction()
    {
        $caseId = $this->fromRoute('case');
        $licence = $this->fromRoute('licence');

        $annualTestHistory = array();

        $bundle = array(
            'children' => array(
                'case' => array(
                    'properties' => array(
                        'id'
                    )
                )
            )
        );

        $result = $this->makeRestCall('AnnualTestHistory', 'GET', array('case' => $caseId, 'bundle' => json_encode($bundle)));

        if ($result['Count']) {
            $annualTestHistory = $result['Results'][0];
            $annualTestHistory['case'] = $annualTestHistory['case']['id'];
        } else {
            $annualTestHistory['case'] = $caseId;
        }

        $form = $this->generateAnnualTestHistoryForm($annualTestHistory);

        $this->setBreadcrumb(array('licence_case_list/pagination' => array('licence' => $licence)));

        $view = $this->getView();
        $tabs = $this->getTabInformationArray();

        $case = $this->getCase($caseId);
        $summary = $this->getCaseSummaryArray($case);

        $view->setVariables(
            [
                'case' => $case,
                'tabs' => $tabs,
                'tab' => 'annual_test_history',
                'summary' => $summary,
                'commentForm' => $form,
            ]
        );

        $view->setTemplate('case/manage');
        return $view;
    }

    /**
     * Creates and returns the prohibition form.
     *
     * @param array $prohibition
     * @return \Zend\Form
     */
    private function generateAnnualTestHistoryForm($annualTestHistory)
    {
        $form = $this->generateForm(
            'annual-test-history-comment',
            'saveAnnualTestHistoryForm'
        );
        $form->setData($annualTestHistory);

        return $form;
    }

    /**
     * Saves the penalty form.
     *
     * @param array $data
     */
    public function saveAnnualTestHistoryForm($data)
    {
        unset($data['cancel']);

        if ($data['submit'] === '') {
            if (!empty($data['id'])) {
                $this->processEdit($data, 'AnnualTestHistory');
            } else {
                $this->processAdd($data, 'AnnualTestHistory');
            }
        }

        return $this->redirect()->toRoute('case_annual_test_history', array(), array(), true);
    }
}
