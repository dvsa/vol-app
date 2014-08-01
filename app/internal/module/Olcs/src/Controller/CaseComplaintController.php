<?php

/**
 * Case Complaint Controller
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace Olcs\Controller;

use Common\Controller\CrudInterface;
/**
 * Case Complaint Controller
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 */
class CaseComplaintController extends CaseController implements CrudInterface
{
    public function deleteAction()
    {
        $this->response->setStatusCode(501);

        return array(
            'content' => 'Delete Method Not Implemented in ' . __CLASS__
        );
    }

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

        $this->setBreadcrumb(array('licence_case_list/pagination' => array('licence' => $licenceId)));

        // checks for CRUD and redirects as required
        $this->checkForCrudAction('complaint', array('case' => $caseId, 'licence' => $licenceId), 'id');

        // no crud, generate the main complaints table
        $view = $this->getView();
        $tabs = $this->getTabInformationArray();
        $action = 'complaints';

        $case = $this->getCase($caseId);

        $summary = $this->getCaseSummaryArray($case);

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
