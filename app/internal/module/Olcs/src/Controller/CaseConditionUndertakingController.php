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

        $table = $this->params()->fromPost('table');

        $id = $this->fromRoute('id');

        $this->setBreadcrumb(array('licence_case_list/pagination' => array('licence' => $licenceId)));

        if (!empty($table)) {
            // checks for CRUD and redirects as required
            $this->checkForCrudAction($table, array('case' => $caseId, 'licence' => $licenceId), 'id');
        }

        // no crud, generate the main complaints table
        $view = $this->getView();
        $tabs = $this->getTabInformationArray();
        $action = 'conditions-undertakings';

        $case = $this->getCase($caseId);

        $summary = $this->getCaseSummaryArray($case);

        $conditionsTable = $this->generateConditionTable($caseId);
        $undertakingsTable = $this->generateUndertakingTable($caseId);

        $view->setVariables(
            [
            'case' => $case,
            'tabs' => $tabs,
            'tab' => $action,
            'summary' => $summary,
            'conditionsTable' => $conditionsTable,
            'undertakingsTable' => $undertakingsTable,
            ]
        );

        $view->setTemplate('case/manage');
        return $view;
    }

    /**
     * Method to generate the conditions table
     *
     * @param id $caseId
     * @return string
     */
    public function generateConditionTable($caseId)
    {
        $bundle = $this->getConditionUndertakingBundle('condition');

        $conditionResults = $this->makeRestCall(
            'VosaCase', 'GET', array(
            'id' => $caseId, 'bundle' => json_encode($bundle))
        );

        // add caseId to results
        for ($i=0; $i<count($conditionResults['conditionUndertakings']); $i++) {
            $conditionResults['conditionUndertakings'][$i]['caseId'] = $caseId;
        }

        $data = [];
        $data['url'] = $this->getPluginManager()->get('url');

        $conditionsTable = $this->buildTable('conditions', $conditionResults['conditionUndertakings'], $data);

        return $conditionsTable;
    }

    /**
     * Method to generate the undertakings table
     *
     * @param id $caseId
     * @return string
     */
    public function generateUndertakingTable($caseId)
    {
        $bundle = $this->getConditionUndertakingBundle('undertaking');

        $undertakingResults = $this->makeRestCall(
            'VosaCase', 'GET', array(
            'id' => $caseId, 'bundle' => json_encode($bundle))
        );

        // add caseId to results
        for ($i=0; $i<count($undertakingResults['conditionUndertakings']); $i++) {
            $undertakingResults['conditionUndertakings'][$i]['caseId'] = $caseId;
        }

        $data = [];
        $data['url'] = $this->getPluginManager()->get('url');

        $undertakingsTable = $this->buildTable('undertakings', $undertakingResults['conditionUndertakings'], $data);

        return $undertakingsTable;
    }

    /**
     * Method to return the bundle required for conditionundertakings
     * by conditionType
     *
     * @todo remove address lines when address entity finalised
     * @return array
     */
    private function getConditionUndertakingBundle($conditionType)
    {

        return array(
            'properties' => array(
                'id'
            ),
            'children' => array(
                'conditionUndertakings' => array(
                    'criteria' => array(
                        'conditionType' => $conditionType,
                    ),
                    'properties' => array(
                        'id',
                        'addedVia',
                        'isDraft',
                        'attachedTo',
                        'isFulfilled',
                        'operatingCentre'
                    ),
                    'children' => array(
                        'operatingCentre' => array(
                            'properties' => array(
                                'address',
                            ),
                            'children' => array(
                                'address' => array(
                                    'properties' => array(
                                        'addressLine1',
                                        'addressLine2',
                                        'addressLine3',
                                        'addressLine4',
                                        'paon_desc',
                                        'saon_desc',
                                        'street',
                                        'locality',
                                        'postcode',
                                        'country'
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
