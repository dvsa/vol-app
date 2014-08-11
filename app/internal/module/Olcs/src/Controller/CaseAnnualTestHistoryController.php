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
    protected $case;

    /**
     * Index action loads the form data
     *
     * @return \Zend\View
     */
    public function indexAction()
    {
        $caseId = $this->fromRoute('case');
        $licence = $this->fromRoute('licence');

        $case = $this->getCase($caseId);

        $form = $this->generateAnnualTestHistoryForm($case);

        $this->setBreadcrumb(array('licence_case_list/pagination' => array('licence' => $licence)));

        $view = $this->getView();
        $tabs = $this->getTabInformationArray();

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
    protected function generateAnnualTestHistoryForm($case)
    {
        $data = [];
        $data['main'] = $case;

        $form = $this->generateForm(
            'annual-test-history-comment',
            'saveAnnualTestHistoryForm'
        );
        $form->setData($data);

        return $form;
    }

    /**
     * Saves the penalty form.
     *
     * @param array $data
     */
    public function saveAnnualTestHistoryForm($data)
    {
        if (isset($data['main'])) {
            $data = $data + $data['main'];
            unset($data['main']);
        }

        if (!empty($data['id'])) {
            $this->processEdit($data, 'VosaCase');
        }

        return $this->redirect()->toRoute('case_annual_test_history', array(), array(), true);
    }
}
