<?php

/**
 * Case Complaint Controller
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace Olcs\Controller;

/**
 * Case Complaint Controller
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 */
class CaseComplaintController extends CaseController
{

    /**
     * Main index action responsible for generating the main landing page for
     * complaints.
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $caseId = $this->fromRoute('case');
        $licenceId = $this->fromRoute('licence');
        $complaintId = $this->fromRoute('complaint');

        $this->setBreadcrumb(array('licence_case_list/pagination' => array('licence' => $licenceId)));

        // checks for CRUD and redirects as required
        $this->checkForCrudAction('complaint', array('case' => $caseId, 'licence' => $licenceId), 'id');

        // no crud, generate the main complaints table
        $view = $this->getView();
        $tabs = $this->getTabInformationArray();
        $action = 'complaints';

        $case = $this->getCase($caseId);

        $summary = $this->getCaseSummaryArray($case);
        $details = $this->getCaseDetailsArray($case);

        $bundle = $this->getComplaintBundle();

        $results = $this->makeRestCall(
            'VosaCase', 'GET', array(
            'id' => $caseId, 'bundle' => json_encode($bundle))
        );

        $data = [];
        $data['url'] = $this->getPluginManager()->get('url');

        $table = $this->buildTable('complaints', $results['complaints'], $data);

        $view->setVariables(
            [
            'case' => $case,
            'tabs' => $tabs,
            'tab' => $action,
            'summary' => $summary,
            'details' => $details,
            'table' => $table,
            ]
        );

        $view->setTemplate('case/manage');
        return $view;
    }

    /**
     * Method to return the bundle required for complaints
     *
     * @return array
     */
    private function getComplaintBundle()
    {
        return array(
            'properties' => array(
                'id'
            ),
            'children' => array(
                'complaints' => array(
                    'properties' => array(
                        'id',
                        'complaintDate',
                        'description',
                        'complainant'
                    ),
                    'children' => array(
                        'complainant' => array(
                            'properties' => array(
                                'id',
                                'person'
                            ),
                           'children' => array(
                               'person' => array(
                                   'properties' => array(
                                       'firstName',
                                       'middleName',
                                       'surname',
                                   )
                               )
                           )
                        )
                    )
                )
            )
        );
    }
}
