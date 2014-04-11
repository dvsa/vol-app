<?php

/**
 * Case Statement Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller;

/**
 * Case Statement Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CaseStatementController extends CaseController
{
    public function indexAction()
    {
        $view = $this->getView();
        $tabs = $this->getTabInformationArray();
        $action = 'statements';
        $caseId = $this->fromRoute('case');
        $case = $this->getCase($caseId);
        $summary = $this->getCaseSummaryArray($case);
        $details = $this->getCaseDetailsArray($case);

        //$results = $this->makeRestCall('', 'GET', array('licence' => $licence));

        $data['url'] = $this->getPluginManager()->get('url');

        $table = $this->getServiceLocator()->get('Table')->buildTable('statement', $results, $data);

        $view->setVariables([
            'case' => $case,
            'tabs' => $tabs,
            'tab' => $action,
            'summary' => $summary,
            'details' => $details,
            'table' => $table
        ]);

        $view->setTemplate('case/manage');
        return $view;
    }
}
