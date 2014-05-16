<?php

/**
 * Case Impounding Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Olcs\Controller;

use Zend\View\Model\ViewModel;
use Zend\Validator\Date as DateValidator;

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
        $id = $this->fromPost('id');

        if ($action) {
            $action = strtolower($action);

            if ($action == 'add') {
                return $this->redirectToCrud($action, null);
            } elseif ($id) {
                return $this->redirectToCrud($action, $id);
            }
        }

        $this->setBreadcrumb(array('licence_case_list/pagination' => array('licence' => $licenceId)));

        $bundle = $this->getIndexBundle();

        $results = $this->makeRestCall(
            'Impounding',
            'GET',
            array(
                'case' => $caseId,
                'bundle' => json_encode($bundle),
                'sort' => 'applicationReceiptDate',
                'order' => 'DESC'
            )
        );

        $impoundings = $this->formatForTable($results['Results']);

        $variables = array(
            'tab' => 'impounding',
            'headScript' => array('/static/js/impounding.js'),
            'table' => $this->buildTable('impounding', $impoundings, array())
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
        $licenceId = $this->fromRoute('licence');
        $caseId = $this->fromRoute('case');

        $this->setBreadcrumb(
            array(
                'licence_case_list/pagination' => array('licence' => $licenceId),
                'case_impounding' => array('licence' => $licenceId, 'case' => $caseId)
            )
        );

        $form = $this->generateFormWithData(
            'impounding',
            'processAddImpounding',
            array(
                'case' => $caseId
            ),
            true
        );

        $view = $this->getView(
            [
                'params' => [
                    'pageTitle' => 'Add impounding',
                    'pageSubTitle' => ''
                ],
                'form' => $form,
                'headScript' => array('/static/js/impounding.js')
            ]
        );

        $view->setTemplate('impounding/form');

        return $view;
    }

    /**
     * Processes the add impounding form
     *
     * @param array $data
     */
    public function processAddImpounding ($data)
    {
        unset($data['cancel']);

        if ($data['submit'] === '') {
            $formattedData = $this->formatForSave($data);

            $result = $this->processAdd($formattedData, 'Impounding');

            if (isset($result['id'])) {
                return $this->redirectToAction();
            }
        }

        return $this->redirectToAction('add');
    }

    /**
     * Processes the edit impounding form
     *
     * @param array $data
     */
    public function processEditImpounding ($data)
    {
        unset($data['cancel']);

        if ($data['submit'] === '') {
            $formattedData = $this->formatForSave($data);

            $result = $this->processEdit($formattedData, 'Impounding');

            if (empty($result)) {
                return $this->redirect()->toRoute(
                    'case_impounding',
                    array(
                        'action' => null,
                        'id' => null
                    ),
                    array(),
                    true
                );
            }
        }

        return $this->redirectToAction('edit');
    }

    /**
     * Loads the edit impounding page
     *
     * @return ViewModel
     */
    public function editAction()
    {
        $licenceId = $this->fromRoute('licence');
        $caseId = $this->fromRoute('case');
        $impoundingId = $this->fromRoute('id');

        $bundle = $this->getFormBundle();

        $details = $this->makeRestCall('Impounding', 'GET', array('id' => $impoundingId, 'bundle' => json_encode($bundle)));

        if (empty($details)) {
            return $this->notFoundAction();
        }

        $data = $this->formatDataForForm($details);

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
                'form' => $form,
                'headScript' => array('/static/js/impounding.js')
            ]
        );

        $view->setTemplate('impounding/form');

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
        $formatted['presidingTc'] = str_replace('presiding_tc.', '', $formatted['presidingTc']);
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
        $config = $this->getServiceLocator()->get('Config');
        $static = $config['static-list-data'];

        if (!empty($results)) {
            foreach ($results as $key => $result) {
                if (isset($result['presidingTc']['name'])) {
                    $results[$key]['name'] = $result['presidingTc']['name'];
                }

                if (isset($result['outcome']['handle'])  && isset($static['impounding_outcome'][$result['outcome']['handle']])) {
                    $results[$key]['outcome'] = $static['impounding_outcome'][$result['outcome']['handle']];
                }

                if (isset($result['impoundingType']['handle'])  && isset($static['impounding_type'][$result['impoundingType']['handle']])) {
                    $results[$key]['impoundingType'] = $static['impounding_type'][$result['impoundingType']['handle']];
                }
            }
        }

        return $results;
    }

    /**
     * Formats data for use in the form
     *
     * @param array $results
     * @return array
     */
    private function formatDataForForm($results)
    {
        $formatted = array();

        //hearing date fieldset
        if (!empty($results['hearingDate'])) {
            $formatted['hearing']['hearingTime'] = date('H:i', strtotime($results['hearingDate']));
            $formatted['hearing']['hearingDate'] = $results['hearingDate'];
        }

        if (!empty($results['hearingLocation'])) {
            $formatted['hearing']['hearingLocation'] = $results['hearingLocation']['handle'];
        }

        //application details fieldset
        $formatted['application_details'] = array(
            'impoundingType' => $results['impoundingType']['handle'],
            'applicationReceiptDate' => $results['applicationReceiptDate']
        );

        //outcome fieldset
        $formatted['outcome'] = array(
            'outcomeSentDate' => $results['outcomeSentDate'],
            'notes' => $results['notes']
        );

        if (isset($results['presidingTc']['id'])) {
            $formatted['outcome']['presidingTc'] = 'presiding_tc.' . $results['presidingTc']['id'];
        }

        if (isset($results['outcome']['handle'])) {
            $formatted['outcome']['outcome'] = $results['outcome']['handle'];
        }

        $formatted['id'] = $results['id'];
        $formatted['case'] = $results['case']['id'];
        $formatted['version'] = $results['version'];

        return $formatted;
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
     * Redirects to the add or edit action
     *
     * @param string $action
     */
    private function redirectToCrud($action, $id = null)
    {
        return $this->redirect()->toRoute(
            'case_impounding',
            array(
                'action' => $action,
                'id' => $id,
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
    private function joinHearingDateAndTime($hearingDate, $hearingTime)
    {
        $combined = '';

        if (!empty($hearingDate) && !empty($hearingTime)) {
            $combined = $hearingDate . ' ' . $hearingTime . ':00';
        }

        return $combined;
    }

    /**
     * Method to return the bundle required for impounding
     *
     * @return array
     */
    private function getIndexBundle()
    {
        return array(
            'properties' => array(
                'id',
                'applicationReceiptDate',
                'outcomeSentDate'
            ),
            'children' => array(
                'impoundingType' => array(
                    'properties' => array(
                        'handle'
                    )
                ),
                'presidingTc' => array(
                    'properties' => array(
                        'name'
                    ),
                ),
                'outcome' => array(
                    'properties' => array(
                        'handle'
                    ),
                ),
            )
        );
    }

    /**
     * Returns a bundle to populate the add/edit forms
     *
     * @return array
     */
    private function getFormBundle()
    {
        return array(
            'properties' => array(
                'id',
                'applicationReceiptDate',
                'outcomeSentDate',
                'hearingDate',
                'notes',
                'version'
            ),
            'children' => array(
                'impoundingType' => array(
                    'properties' => array(
                        'handle'
                    )
                ),
                'presidingTc' => array(
                    'properties' => array(
                        'id'
                    ),
                ),
                'case' => array(
                    'properties' => array(
                        'id'
                    )
                ),
                'hearingLocation' => array(
                    'properties' => array(
                        'handle'
                    ),
                ),
                'outcome' => array(
                    'properties' => array(
                        'handle'
                    ),
                )
            )
        );
    }
}
