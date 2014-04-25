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
        if ($this->params()->fromPost('action')) {
            switch (strtolower($this->params()->fromPost('action'))) {
                case 'add':
                    return $this->redirect()->toRoute(
                        'complaint', array('licence' => 7,
                        'case' => $this->fromRoute('case'),
                        'action' => 'add')
                    );
                case 'edit':
                    return $this->redirect()->toRoute(
                        'complaint', array('licence' => 7,
                        'case' => $this->fromRoute('case'),
                        'id' => $this->params()->fromPost('id'),
                        'action' => 'edit')
                    );
                default:
                    break;
            }
        }

        $view = $this->getView();
        $tabs = $this->getTabInformationArray();
        $action = 'complaints';
        $caseId = $this->fromRoute('case');

        $case = $this->getCase($caseId);

        $summary = $this->getCaseSummaryArray($case);
        $details = $this->getCaseDetailsArray($case);

        $results = $this->makeRestCall('Complaint', 'GET', array('vosaCase' => $caseId));

        $data = [];
        $data['url'] = $this->getPluginManager()->get('url');

        $table = $this->getServiceLocator()->get('Table')->buildTable('complaints', $results, $data);

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
