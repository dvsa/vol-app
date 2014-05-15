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
 * @todo For Breadcrumbs we need to pull the real licence id in
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
        $this->setBreadcrumb(array('licence_case_list/pagination' => array('licence' => 1)));

        $caseId = $this->fromRoute('case');

        $this->checkForCrudAction('case_statement', array('case' => $caseId), 'statement', true);

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

        $this->setBreadcrumb(
            array(
                'licence_case_list/pagination' => array('licence' => 1),
                'case_statement' => array('case' => $caseId)
            )
        );

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

        $this->setBreadcrumb(
            array(
                'licence_case_list/pagination' => array('licence' => 1),
                'case_statement' => array('case' => $caseId)
            )
        );

        $statementId = $this->fromRoute('statement');

        $bundle = array(
            'children' => array(
                'requestorsAddress'
            )
        );

        $details = $this->makeRestCall('Statement', 'GET', array('id' => $statementId), $bundle);

        if (empty($details)) {
            return $this->notFoundAction();
        }

        $data = $this->formatDataForEditForm($details);
        $data['case'] = $caseId;

        $data['requestorsAddress']['country'] = 'country.' . $data['requestorsAddress']['country'];

        $form = $this->generateFormWithData(
            'statement',
            'processEditStatement',
            $data,
            true
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
     * Format the data for the edit form
     *
     * @param array $data
     * @return array
     */
    private function formatDataForEditForm($data)
    {
        $data['details'] = $data;

        $data['details']['statementType'] = 'statement_type.' . $data['details']['statementType'];
        $data['details']['contactType'] = 'contact_type.' . $data['details']['contactType'];

        return $data;
    }

    /**
     * Delete action
     */
    public function deleteAction()
    {
        $caseId = $this->fromRoute('case');

        $bundle = array(
            'children' => array(
                'case' => array(
                    'properties' => 'ALL',
                )
            )
        );

        $statementId = $this->fromRoute('statement');

        // Check that the statement belongs to the case before deleting
        $results = $this->makeRestCall('Statement', 'GET', array('id' => $statementId), $bundle);

        if (isset($results['case']) && $results['case']['id'] == $caseId) {

            $this->makeRestCall('Statement', 'DELETE', array('id' => $statementId));
            return $this->redirect()->toRoute('case_statement', ['statement'=>''], [], true);
        }

        return $this->notFoundAction();
    }

    /**
     * Process the add post
     *
     * @param array $data
     */
    public function processAddStatement($data)
    {
        $data = $this->processDataBeforePersist($data);

        $this->processAdd($data, 'Statement');

        $this->redirect()->toRoute(
            'case_statement',
            ['case'=>$this->fromRoute('case'), 'licence'=>$this->fromRoute('licence')],
            [],
            false
        );
    }

    /**
     * Process the edit post
     *
     * @param array $data
     */
    public function processEditStatement($data)
    {
        $data = $this->processDataBeforePersist($data);

        $this->processEdit($data, 'Statement');

        $bookmarks = $this->mapDocumentData($data);

        $documentData = $this->sendPost('Olcs\Document\Generate', [
            'bookmarks' => $bookmarks,
            'country' => 'en_GB',
            'templateId' => 'S43_Letter'
            ]);

        $this->redirect()->toRoute(
            'case_statement',
            ['case'=>$this->fromRoute('case'), 'licence'=>$this->fromRoute('licence')],
            [],
            false
        );
    }

    public function mapDocumentData($data)
    {
        $bookmarks = [];
        $bookmarks['TAName'] = '<TAName>';
        $bookmarks['TAAddress_2'] = '<TAAddress_2>';
        $bookmarks['Address_1'] = $this->concatAddress($data['addresses']['requestorsAddress']);
        $bookmarks['Ref'] = '<Ref>';
        $bookmarks['Name'] = '<Name>';
        $bookmarks['RequestMode'] = '<RequestMode>';
        $bookmarks['RequestDate'] = '<RequestDate>';
        $bookmarks['UserKnownAs'] = '<UserKnownAs>';
        $bookmarks['AuthorisorTeam'] = '<AuthorisorTeam>';
        $bookmarks['AuthorisorName2'] = '<AuthorisorName2>';
        $bookmarks['AuthorisedDecision'] = '<AuthorisedDecision>';
        $bookmarks['AuthorisorName3'] = '<AuthorisorName3>';

        return $bookmarks;
    }

    public function concatAddress($data)
    {
        return $data['addressLine1'] . ', ' .
               $data['addressLine2'] . ', ' .
               $data['addressLine3'] . ', ' .
               $data['addressLine4'] . ', ' .
               $data['city'] . ', ' .
               $data['postcode'] . ', ' .
               $data['country'];
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
