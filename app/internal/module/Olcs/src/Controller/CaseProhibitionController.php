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
    public function indexAction()
    {
        $caseId = $this->fromRoute('case');
        $licence = $this->fromRoute('licence');

        $prohibition = array();

        $bundle = array(
            'children' => array(
                'case' => array(
                    'properties' => array(
                        'id'
                    )
                )
            )
        );

        $result = $this->makeRestCall('Prohibition', 'GET', array('case' => $caseId, 'bundle' => json_encode($bundle)));

        if ($result['Count']) {
            $prohibition = $result['Results'][0];
            $prohibition['case'] = $prohibition['case']['id'];
        } else {
            $prohibition['case'] = $caseId;
        }

        $form = $this->generateProhibitionForm($prohibition);

        $this->setBreadcrumb(array('licence_case_list/pagination' => array('licence' => $licence)));

        $view = $this->getView();
        $tabs = $this->getTabInformationArray();

        $case = $this->getCase($caseId);
        $summary = $this->getCaseSummaryArray($case);
        $details = $this->getCaseDetailsArray($case);

        $view->setVariables(
            [
                'case' => $case,
                'tabs' => $tabs,
                'tab' => 'prohibitions',
                'summary' => $summary,
                'details' => $details,
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
    private function generateProhibitionForm($prohibition)
    {
        $form = $this->generateForm(
            'prohibition-comment',
            'saveProhibitionForm'
        );
        $form->setData($prohibition);

        return $form;
    }

    /**
     * Saves the penalty form.
     *
     * @param array $data
     */
    public function saveProhibitionForm($data)
    {
        unset($data['cancel']);

        if ($data['submit'] === '') {
            if (!empty($data['id'])) {
                $this->processEdit($data, 'Prohibition');
            } else {
                $this->processAdd($data, 'Prohibition');
            }
        }

        return $this->redirect()->toRoute('case_prohibition', array(), array(), true);
    }
}
