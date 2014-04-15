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
    /**
     * Show statements
     *
     * @return object
     */
    public function indexAction()
    {
        $caseId = $this->fromRoute('case');

        $this->checkForCrudAction('case_statement', array('case' => $caseId), 'statement');

        $tabs = $this->getTabInformationArray();
        $action = 'statements';

        $case = $this->getCase($caseId);

        $summary = $this->getCaseSummaryArray($case);
        $details = $this->getCaseDetailsArray($case);

        $results = $this->makeRestCall('Statement', 'GET', array('caseId' => $caseId));

        $data['url'] = $this->getPluginManager()->get('url');

        $table = $this->getServiceLocator()->get('Table')->buildTable('statement', $results, $data);

        $view = $this->getView([
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

    /**
     * Add statement action
     *
     * @return object
     */
    public function addAction()
    {
        $form = $this->generateFormWithData(
            'statement', 'processAddStatement', array(
            )
        );

        $view = $this->getView([
            'params' => [
                'pageTitle' => 'Add statement',
                'pageSubTitle' => ''
            ],
            'form' => $form
        ]);

        $view->setTemplate('form');

        return $view;
    }
}
