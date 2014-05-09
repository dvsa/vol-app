<?php

/**
 * Case Impounding Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Olcs\Controller;

use Zend\View\Model\ViewModel;

/**
 * Class to manage Impounding
 */
class CaseImpoundingController extends CaseController
{

    /**
     * Show a table of impounding data for the given case
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $licenceId = $this->fromRoute('licence');
        $caseId = $this->fromRoute('case');

        if (!$caseId || !$licenceId) {
            return $this->notFoundAction();
        }

        $action = $this->fromPost('action');

        if ($action) {
            return $this->redirectToAction(strtolower($action));
        }

        $this->setBreadcrumb(array('licence_case_list/pagination' => array('licence' => $licenceId)));

        $bundle = $this->getBundle();

        $results = $this->makeRestCall(
            'VosaCase', 'GET', array(
            'id' => $caseId, 'bundle' => json_encode($bundle))
        );
        print_r($results);

        $impoundings = $this->formatForTable($results['impoundings']);

        $variables = array(
            'tab' => 'impounding',
            'table' => $this->buildTable('Impounding', $impoundings, array())
        );

        $caseVariables = $this->getCaseVariables($caseId, $variables);
        $view = $this->getView($caseVariables);
        $view->setTemplate('case/manage');

        return $view;
    }

    /**
     * Add impounding data for a case
     *
     * @return ViewModel
     */
    public function addAction()
    {
        $caseId = $this->fromRoute('case');
        $licenceId = $this->fromRoute('licence');

        $this->setBreadcrumb(
            array(
                'licence_case_list/pagination' => array('licence' => $licenceId),
                'case_impounding' => array('licence' => $licenceId, 'case' => $caseId)
            )
        );

        $form = $this->generateFormWithData(
            'impounding',
            'processAddImpounding',
            array(),
            true
        );

        $view = $this->getView(
            [
                'params' => [
                    'pageTitle' => 'Add impounding',
                    'pageSubTitle' => ''
                ],
                'form' => $form
            ]
        );

        $view->setTemplate('form');

        return $view;
    }

    public function processAddImpounding ($data)
    {
        $formattedData = $this->formatForSave($data);

        $result = $this->processAdd($formattedData, 'Impounding');

        if (isset($result['id'])) {
            return $this->redirectToAction();
        }

        return $this->redirectToAction('add');
    }

    /**
     * Loads the edit impounding page
     *
     * @return ViewModel
     */
    public function editAction()
    {
        $caseId = $this->fromRoute('case');
        $licenceId = $this->fromRoute('licence');
        $impoundingId = $this->fromRoute('id');

        $bundle = $this->getFormBundle();

        $details = $this->makeRestCall('Impounding', 'GET', array('id' => $impoundingId, 'bundle' => json_encode($bundle)));

        if (empty($details)) {
            return $this->notFoundAction();
        }

        $data['details'] = $this->formatDataForForm($details);

        $this->setBreadcrumb(
            array(
                'licence_case_list/pagination' => array('licence' => $licenceId),
                'case_impounding' => array('licence' => $licenceId, 'case' => $caseId)
            )
        );

        $form = $this->generateFormWithData(
            'impounding',
            'processEditImpounding',
            $data,
            true
        );

        $view = $this->getView(
            [
                'params' => [
                    'pageTitle' => 'Edit impounding',
                    'pageSubTitle' => ''
                ],
                'form' => $form
            ]
        );

        $view->setTemplate('form');

        return $view;
    }

    /**
     *
     * Formats data for use in a table
     *
     * @param array $data
     * @return array
     */
    private function formatForSave ($data)
    {
        $formatted = array_merge(array(), $data['outcome'], $data['application_details']);

        $formatted['hearingLocation'] = $data['hearing']['hearingLocation'];
        $formatted['hearingDate'] = $this->joinHearingDateAndTime($data['hearing']['hearingDate'], $data['hearing']['hearingTime']);
        $formatted['id'] = $data['id'];
        $formatted['case'] = $data['case'];
        $formatted['version'] = $data['version'];

        return $formatted;
    }

    /**
     *
     * Formats data for use in a table
     *
     * @param array $results
     * @return array $results
     */
    private function formatForTable($results)
    {
        if (!empty($results)) {
            foreach ($results as $key => $result) {
                $results[$key]['tcName'] = $result['presidingTc']['tcName'];
            }
        }

        return $results;
    }

    /**
     * Formats data for use in the form
     */
    private function formatDataForForm($results){
        //$results['outcome'] = $results['outcome']['itemValue'];
        $results['presidingTc'] = $results['presidingTc']['tcName'];

        if (!empty($results['hearingDate'])) {
            $results['hearingTime'] = date('H:i', strtotime($results['hearingDate']));
        }

        return $results;
    }

    /**
     * Redirects to the selected action or if no action to the index
     *
     * @param string $action
     */
    private function redirectToAction($action = null)
    {
        return $this->redirect()->toRoute(
            'case_impounding',
            array(
                'action' => $action,
            ),
            array(),
            true
        );
    }

    /**
     * Hearing date and time are separate fields on the form but are one field in the database
     *
     * @param string $hearingDate
     * @param string $hearingTime
     *
     * @return string
     */
    private function joinHearingDateAndTime($hearingDate, $hearingTime){
        return $hearingDate . ' ' . $hearingTime . ':00';
    }

    /**
     * Method to return the bundle required for impounding
     *
     * @return array
     */
    private function getBundle()
    {
        return array(
            'properties' => array(
                'id'
            ),
            'children' => array(
                'impoundings' => array(
                    'properties' => array(
                        'id',
                        'applicationReceiptDate',
                        'outcomeSentDate'
                    ),
                    'children' => array(
                        'presidingTc' => array(
                            'properties' => array(
                                'tcName'
                            ),
                        ),
                        'outcome' => array(
                            'properties' => array(
                                'tcName'
                            ),
                        ),
                    )
                )
            )
        );
    }

    private function getFormBundle()
    {
        return array(
            'properties' => array(
                'id',
                'applicationReceiptDate',
                'outcomeSentDate',
                'hearingDate'
            ),
            'children' => array(
                'presidingTc' => array(
                    'properties' => array(
                        'tcName'
                    ),
                ),
                'outcome' => array(
                    'properties' => array(
                        'itemValue'
                    ),
                ),
            )
        );
    }
}
