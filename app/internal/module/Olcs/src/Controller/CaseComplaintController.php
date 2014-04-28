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
        $this->setBreadcrumb(array('licence_case_list/pagination' => array('licence' => 7)));

        $view = $this->getView();
        $tabs = $this->getTabInformationArray();
        $action = 'complaints';
        $caseId = $this->fromRoute('case');

        $case = $this->getCase($caseId);

        $summary = $this->getCaseSummaryArray($case);
        $details = $this->getCaseDetailsArray($case);

        $bundle = array(
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

        $results = $this->makeRestCall('VosaCase', 'GET', array('id' => $caseId, 'bundle' => json_encode($bundle)));

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
}
