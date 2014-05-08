<?php

/**
 * CaseConditionUndertaking Controller
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace Olcs\Controller;

/**
 * CaseConditionUndertaking Controller
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 */
class CaseConditionUndertakingController extends CaseController
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
        $conditionUndertakingId = $this->fromRoute('complaint');

        $this->setBreadcrumb(array('licence_case_list/pagination' => array('licence' => $licenceId)));

        // checks for CRUD and redirects as required
        $this->checkForCrudAction('conditionUndertaking', array('case' => $caseId, 'licence' => $licenceId), 'id');

        // no crud, generate the main complaints table
        $view = $this->getView();
        $tabs = $this->getTabInformationArray();
        $action = 'conditions-undertakings';

        $case = $this->getCase($caseId);

        $summary = $this->getCaseSummaryArray($case);
        $details = $this->getCaseDetailsArray($case);

        $bundle = $this->getConditionUndertakingBundle();

        $conditionResults = $this->makeRestCall(
            'VosaCase', 'GET', array(
            'case' => $caseId, 'conditionType' => 'condition', 'bundle' => json_encode($bundle))
        );
var_dump($conditionResults);exit;
        $undertakingResults = $this->makeRestCall(
            'ConditionUndertaking', 'GET', array(
            'case' => $caseId, 'conditionType' => 'undertaking', 'bundle' => json_encode($bundle))
        );

        $data = [];
        $data['url'] = $this->getPluginManager()->get('url');

        $conditionsTable = $this->buildTable('conditions', $conditionResults, $data);
        $undertakingsTable = $this->buildTable('undertakings', $undertakingResults, $data);

        $view->setVariables(
            [
            'case' => $case,
            'tabs' => $tabs,
            'tab' => $action,
            'summary' => $summary,
            'details' => $details,
            'conditionsTable' => $conditionsTable,
            'undertakingsTable' => $undertakingsTable,
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
    private function getConditionUndertakingBundle()
    {

        return array(
            'properties' => array(
                'id',
            ),
            'children' => array(
                'conditionUndertakings' => array(
                    'properties' => array(
                        'id',
                        'addedVia',
                        'isDraft',
                        'attachedTo',
                        'case',
                        'isFulfilled'
                    )
                )
            )
        );
        return array(
            'properties' => array(
                'id',
                'addedVia',
                'isDraft',
                'attachedTo',
                'case',
                'isFulfilled'
            ),
            'children' => array(
                'case' => array(
                    'properties' => array(
                        'id'
                    )
                )
            )
        );
    }
}
