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

        $this->checkForCrudAction('case_statement', array('case' => $caseId), 'statement');

        $results = $this->makeRestCall('Statement', 'GET', array('caseId' => $caseId));

        $variables = array('tab' => 'statements', 'table' => $this->buildTable('statement', $results));

        $view = $this->getView($this->getCaseVariables($caseId, $variables));

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
        $caseId = $this->fromRoute('case');

        $form = $this->generateFormWithData(
            'statement', 'processAddStatement', array('case' => $caseId)
        );

        $view = $this->getView(
            [
                'params' => [
                    'pageTitle' => 'Add statement',
                    'pageSubTitle' => ''
                ],
                'form' => $form
            ]
        );

        $view->setTemplate('form');

        return $view;
    }
}
