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
     * Show a table of statements for the given case
     *
     * @return object
     */
    public function indexAction()
    {
        $caseId = $this->fromRoute('case');

        $results = $this->makeRestCall('Statement', 'GET', array('caseId' => $caseId));

        $variables = array('tab' => 'statements', 'table' => $this->buildTable('statement', $results));

        $view = $this->getView($this->getCaseVariables($caseId, $variables));

        $view->setTemplate('case/manage');

        return $view;
    }
}
