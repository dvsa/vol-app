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

    /**
     * Edit statement action
     *
     * @return object
     */
    public function editAction()
    {
        $caseId = $this->fromRoute('case');

        $statementId = $this->fromRoute('statement');

        $form = $this->generateFormWithData(
            'statement',
            'processEditStatement',
            array(
                'case' => $caseId,
                'id' => $statementId
            )
        );

        $view = $this->getView(
            [
                'params' => [
                    'pageTitle' => 'Edit statement',
                    'pageSubTitle' => ''
                ],
                'form' => $form
            ]
        );

        $view->setTemplate('form');

        return $view;
    }

    /**
     * Process the add post
     *
     * @param array $data
     */
    public function processAddStatement($data)
    {
        $data = $this->processDataBeforePersist($data);

        $results = $this->processAdd($data, 'Statement');

        print '<pre>';
        print_r($results);
        print '</pre>';
    }

    /**
     * Process the edit post
     *
     * @param array $data
     */
    public function processEditStatement($data)
    {
        $data = $this->processDataBeforePersist($data);

        $results = $this->processEdit($data, 'Statement');

        print '<pre>';
        print_r($results);
        print '</pre>';
    }

    /**
     * Pre-persist data processing
     *
     * @param array $data
     * @return array
     */
    protected function processDataBeforePersist($data)
    {
        $data = array_merge($data, $data['details']);

        unset($data['details']);

        $data = $this->processAddressData($data, 'requestorsAddress');

        $data['statementType'] = str_replace('statement_type.', '', $data['statementType']);
        $data['contactType'] = str_replace('contact_type.', '', $data['contactType']);

        return $data;
    }
}
