<?php

/**
 * Case Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller;

use Common\Controller\FormActionController;
use Zend\View\Model\ViewModel;

/**
 * Case Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CaseController extends FormActionController
{

    /**
     * Manage action.
     */
    public function manageAction()
    {
        $caseId = $this->fromRoute('case');
        $licence = $this->fromRoute('licence');
        $action = $this->fromRoute('tab');

        $this->setBreadcrumb(array('licence_case_list/pagination' => array('licence' => $licence)));

        if ($this->params()->fromPost('action')) {
            return $this->redirect()->toRoute($this->params()->fromPost('table'), array('licence' => $licence,
                        'case' => $caseId,
                        'id' => $this->params()->fromPost('id') ? $this->params()->fromPost('id') : '',
                        'action' => strtolower($this->params()->fromPost('action'))));
        }

        $view = $this->getView();

        $tabs = $this->getTabInformationArray();

        if (!array_key_exists($action, $tabs)) {
            return $this->notFoundAction();
        }

        $case = $this->getCase($caseId);

        $summary = $this->getCaseSummaryArray($case);
        $details = $this->getCaseDetailsArray($case);

        // -- submissions

        $submissionsResults = $this->getSubmissions($caseId);
        $submissionsData = [];
        $submissionsData['url'] = $this->getPluginManager()->get('url');

        $submissionsTable = $this->getServiceLocator()->get('Table')->buildTable(
            'submission',
            $submissionsResults,
            $submissionsData
        );

        // -- submissions

        $view->setVariables(
            array(
                'case' => $case,
                'tabs' => $tabs,
                'tab' => $action,
                'summary' => $summary,
                'details' => $details,
                'submissions' => $submissionsTable
            )
        );

        $view->setTemplate('case/manage');
        return $view;
    }

    private function checkForSubmissions()
    {

    }

    public function getSubmissions($case)
    {
        $bundle = array(
            'children' => array(
                'submissionActions' => array(
                    'properties' => 'ALL',
                    'children' => array(
                        'userSender' => array(
                            'properties' => 'ALL'
                        ),
                        'userRecipient' => array(
                            'properties' => 'ALL'
                        ),
                        'submissionActionStatus' => array(
                            'properties' => 'ALL',
                            'children' => array(
                                'submissionActionStatusType' => array(
                                    'properties' => 'ALL',
                                )
                            )
                        )
                    )
                )
            )
        );

        $results = $this->makeRestCall('Submission', 'GET', array('vosaCase' => $case), $bundle);
        foreach ($results['Results'] as $k => $result) {
            $this->makeRestCall('SubmissionAction', 'GET', array('submission' => $result['id']));
            foreach ($result['submissionActions'] as $action) {

                //$results['Results'][$k]['actions'][$ak] = $action;
                $results['Results'][$k]['urgent'] = $action['urgent'];

                if (isset($action['userRecipient']['displayName'])) {

                    $results['Results'][$k]['currentlyWith'] = $action['userRecipient']['displayName'];
                }

                if (isset($action['submissionActionStatus']['name'])) {

                    // set the submission status at the top level.
                    $results['Results'][$k]['status'] = $action['submissionActionStatus']['name'];

                    // Get the submission action status type - this gives us the current submission type
                    $results['Results'][$k]['type'] =
                        $action['submissionActionStatus']['submissionActionStatusType']['name'];
                }

                //We only need the data from the top action - which is the latest.
                break;
            }
        }

        return $results;
    }

    public function getView(array $params = null)
    {
        return new ViewModel($params);
    }

    public function fromRoute($param, $default = null)
    {
        return $this->params()->fromRoute($param, $default);
    }

    /**
     * Gets the case ID.
     *
     * @param integer $caseId
     * @return array
     */
    public function getCase($caseId)
    {
        $bundle = array(
            'children' => array(
                'categories' => array(
                    'properties' => array(
                        'id',
                        'name'
                    )
                ),
                'licence' => array(
                    'properties' => 'ALL',
                    'children' => array(
                        'trafficArea' => array(
                            'properties' => 'ALL'
                        ),
                        'organisation' => array(
                            'properties' => 'ALL'
                        )
                    )
                )
            )
        );

        $case = $this->makeRestCall('VosaCase', 'GET', array('id' => $caseId), $bundle);

        return $case;
    }

    /**
     * Returns tab information as an array.
     *
     * @return array
     */
    public function getTabInformationArray()
    {
        $pm = $this->getPluginManager();

        $tabs = [
            'overview' => [
                'key' => 'overview',
                'label' => 'Overview',
                'url' => $pm->get('url')->fromRoute('case_manage', ['tab' => 'overview'], [], true),
            ],
            'convictions' => [
                'key' => 'convictions',
                'label' => 'Convictions',
                'url' => $pm->get('url')->fromRoute('case_convictions', ['tab' => 'convictions'], [], true),
            ],
            'prohibitions' => [
                'key' => 'prohibitions',
                'label' => 'Prohibitions',
                'url' => $pm->get('url')->fromRoute('case_manage', ['tab' => 'prohibitions'], [], true),
            ],
            'penalties' => [
                'key' => 'penalties',
                'label' => 'Penalties',
                'url' => $pm->get('url')->fromRoute('case_penalty', ['tab' => 'penalties', 'action' => null], [], true),
            ],
            'erru' => [
                'key' => 'erru',
                'label' => 'ERRU Penalties',
                'url' => $pm->get('url')->fromRoute('case_manage', ['tab' => 'erru'], [], true),
            ],
            'statements' => [
                'key' => 'statements',
                'label' => 'Statements',
                'url' => $pm->get('url')->fromRoute('case_statement', ['action' => null], [], true),
            ],
            'complaints' => [
                'key' => 'complaints',
                'label' => 'Complaints',
                'url' => $pm->get('url')->fromRoute('case_manage', ['tab' => 'complaints'], [], true),
            ],
            'si' => [
                'key' => 'si',
                'label' => 'Serious infringement',
                'url' => $pm->get('url')->fromRoute('case_manage', ['tab' => 'si'], [], true),
            ],
            'stays' => [
                'key' => 'stays',
                'label' => 'Stays & Appeals',
                'url' => $pm->get('url')->fromRoute('case_manage', ['tab' => 'stays'], [], true),
            ],
            'complaints' => [
                'key' => 'complaints',
                'label' => 'Complaints',
                'url' => $pm->get('url')->fromRoute('case_complaints', ['tab' => 'complaints'], [], true),
            ],
            'reports' => [
                'key' => 'reports',
                'label' => 'Reports',
                'url' => $pm->get('url')->fromRoute('case_manage', ['tab' => 'reports'], [], true),
            ],
            'documents' => [
                'key' => 'documents',
                'label' => 'Documents',
                'url' => $pm->get('url')->fromRoute('case_manage', ['tab' => 'documents'], [], true),
            ],
            'notes' => [
                'key' => 'notes',
                'label' => 'Notes',
                'url' => $pm->get('url')->fromRoute('case_manage', ['tab' => 'notes'], [], true),
            ]
        ];

        return $tabs;
    }

    public function getCaseSummaryArray(array $case)
    {
        /* echo '<pre>';
        die(print_r($case, 1)); */

        $categoryNames = array();

        if (isset($case['categories']) && !empty($case['categories'])) {

            foreach ($case['categories'] as $category) {
                $categoryNames[] = $category['name'];
            }
        }

        $smmary = [

            'case_number' => [
                'label' => 'Case number',
                'value' => $case['caseNumber'],
                'url' => '',
            ],
            'operator_name' => [
                'label' => 'Operator name',
                'value' => $case['licence']['organisation']['name'],
                'url' => ''
            ],
            'licence_number' => [
                'label' => 'Licence number',
                'value' => $case['licence']['licenceNumber'],
                'url' => ''
            ],
            'ecms' => [
                'label' => 'ECMS',
                'value' => $case['ecms'],
                'url' => ''
            ],
            'categories' => [
                'label' => 'Categories',
                'value' => implode(', ', $categoryNames),
                'url' => ''
            ],
            'summary' => [
                'label' => 'Summary',
                'value' => $case['description'],
                'url' => ''
            ],
        ];

        return $smmary;
    }

    public function getCaseDetailsArray(array $case)
    {
        $opentimeDate = date('d/m/Y', strtotime($case['openTime']));
        $licenceStartDate = date('d/m/Y', strtotime($case['licence']['startDate']));

        $details = [

            'open_date' => [
                'label' => 'Open date',
                'value' => $opentimeDate,
                'url' => '',
            ],
            'traffic_area' => [
                'label' => 'Traffic area',
                'value' => $case['licence']['trafficArea']['areaName'],
                'url' => '',
            ],
            'status' => [
                'label' => 'Status',
                'value' => ucfirst($case['status']),
                'url' => '',
            ],
            'entity_type' => [
                'label' => 'Entity type',
                'value' => $case['licence']['organisation']['organisationType'],
                'url' => '',
            ],
            'licence_start_date' => [
                'label' => 'Licence start date',
                'value' => $licenceStartDate,
                'url' => '',
            ],
            'licence_type' => [
                'label' => 'Licence type',
                'value' => $case['licence']['licenceType'],
                'url' => '',
            ],
            'licence_category' => [
                'label' => 'Licence category',
                'value' => $case['licence']['goodsOrPsv'],
                'url' => '',
            ],
            'licence_status' => [
                'label' => 'Licence status',
                'value' => $case['licence']['licenceStatus'],
                'url' => '',
            ],
        ];

        return $details;
    }

    /**
     * List of cases if we have a licence
     */
    public function indexAction()
    {
        $licence = $this->params()->fromRoute('licence');

        if (empty($licence)) {

            return $this->notFoundAction();
        }

        $action = $this->params()->fromPost('action');

        if (!empty($action)) {

            $action = strtolower($action);

            if ($action !== 'add') {

                $id = $this->params()->fromPost('id');

                if (empty($id)) {

                    $this->crudActionMissingId();
                } else {

                    $this->redirect()->toRoute(
                        'licence_case_action',
                        array(
                            'action' => $action,
                            'case' => $id,
                            'licence' => $licence
                        )
                    );
                }
            } else {

                $this->redirect()->toRoute('licence_case_action', array('action' => $action, 'licence' => $licence));
            }
        }

        $pageData = $this->getPageData($licence);

        $results = $this->makeRestCall('VosaCase', 'GET', array('licence' => $licence));

        $data['url'] = $this->getPluginManager()->get('url');

        $table = $this->getServiceLocator()->get('Table')->buildTable('case', $results, $data);

        $view = new ViewModel(['licence' => $licence, 'table' => $table, 'data' => $pageData]);
        $view->setTemplate('case/list');
        return $view;
    }

    /**
     * Add a new case to a licence
     *
     * @return ViewModel
     */
    public function addAction()
    {
        $licence = $this->params()->fromRoute('licence');
        $this->setBreadcrumb(array('licence_case_list/pagination' => array('licence' => $licence)));

        if (empty($licence)) {
            return $this->notFoundAction();
        }

        $results = $this->makeRestCall('Licence', 'GET', array('id' => $licence));

        if (empty($results)) {
            return $this->notFoundAction();
        }

        $form = $this->generateFormWithData(
            'case',
            'processAddCase',
            array('licence' => $licence)
        );

        $pageData = $this->getPageData($licence);

        $view = new ViewModel(['form' => $form, 'data' => $pageData]);
        $view->setTemplate('case/add');
        return $view;
    }

    /**
     * Edit a new case
     *
     * @return ViewModel
     */
    public function editAction()
    {
        $licence = $this->params()->fromRoute('licence');
        $case = $this->params()->fromRoute('case');
        $this->setBreadcrumb(array('licence_case_list/pagination' => array('licence' => $licence)));

        $bundle = array(
            'children' => array(
                'categories' => array(
                    'properties' => array(
                        'id'
                    )
                ),
                'licence' => array(
                    'properties' => array(
                        'id'
                    )
                )

            )
        );

        $result = $this->makeRestCall(
            'VosaCase',
            'GET',
            array('id' => $case, 'licence' => $licence),
            $bundle
        );

        if (empty($result)) {
            return $this->notFoundAction();
        }

        $categories = $result['categories'];
        unset($result['categories']);

        $result['fields'] = $result;

        $result['categories'] = $this->unFormatCategories($categories);

        $result['licence'] = $result['licence']['id'];

        $form = $this->generateFormWithData(
            'case',
            'processEditCase',
            $result,
            true
        );

        $pageData = $this->getPageData($licence);

        $view = new ViewModel(['form' => $form, 'data' => $pageData]);
        $view->setTemplate('case/edit');
        return $view;
    }

    /**
     * Get page data from licence id
     *
     * @param int $licence
     */
    private function getPageData($licence)
    {
        $bundle = [
            'children' => [
                'organisation' => [
                    'properties' => 'ALL'
                ]
            ]
        ];

        $licenceData = $this->makeRestCall('Licence', 'GET', array('id' => $licence), $bundle);

        return array(
            'organisation' => $licenceData['organisation']['name'],
            'licence' => $licenceData['licenceNumber']
        );
    }

    public function deleteAction()
    {
        $licence = $this->params()->fromRoute('licence');
        $case = $this->params()->fromRoute('case');

        $result = $this->makeRestCall('VosaCase', 'GET', array('id' => $case, 'licence' => $licence));

        if (empty($result)) {
            return $this->notFoundAction();
        }

        $this->makeRestCall('VosaCase', 'DELETE', array('id' => $case));

        $this->redirect()->toUrl('/case/' . $licence);
    }

    /**
     * Process adding the case
     *
     * @todo Additional fields are required for persisting - Find out where these fields come from
     *
     * @param type $data
     */
    protected function processAddCase($data)
    {
        // Additional fields (Mocked for now)
        $data['caseNumber'] = 12345678;
        $data['openTime'] = date('Y-m-d H:i:s');
        $data['owner'] = 7;

        $data['categories'] = $this->formatCategories($data['categories']);
        $data = array_merge($data, $data['fields']);

        $result = $this->processAdd($data, 'VosaCase');

        if (isset($result['id'])) {
            $this->redirect()->toRoute('case_manage', array('case' => $result['id'], 'tab' => 'overview'));
        }
    }

    /**
     * Process updating the case
     *
     * @param type $data
     */
    protected function processEditCase($data)
    {
        $data['categories'] = $this->formatCategories($data['categories']);
        $data = array_merge($data, $data['fields']);

        $this->processEdit($data, 'VosaCase');

        $this->redirect()->toRoute('licence_case_list', array('licence' => $data['licence']));
    }

    /**
     * Format categories into a single dimension array
     *
     * @param array $categories
     * @return array
     */
    private function formatCategories($categories = array())
    {
        $return = array();

        foreach ($categories as $array) {

            foreach ($array as $category) {

                $return[] = str_replace('case_category.', '', $category);
            }
        }

        return $return;
    }

    /**
     * Format the categories from the REST response into the form's format
     *
     * @todo Look at re-factoring this
     *
     * @param array $categories
     * @return array
     */
    private function unFormatCategories($categories = array())
    {
        $config = $this->getServiceLocator()->get('Config');

        $formattedCategories = array();

        $translations = array();

        foreach ($config['static-list-data'] as $key => $array) {

            if (preg_match('/case_categories_([a-z]+)/', $key, $matches)) {

                foreach (array_keys($array) as $id) {

                    $translations[str_replace('case_category.', '', $id)] = $matches[1];
                }
            }
        }

        foreach ($categories as $category) {

            if (!isset($formattedCategories[$translations[$category['id']]])) {
                $formattedCategories[$translations[$category['id']]] = array();
            }

            $formattedCategories[$translations[$category['id']]][] = 'case_category.' . $category['id'];
        }

        return $formattedCategories;
    }

    /**
     * Extend and retrieve the required case variables
     *
     * @param int $caseId
     * @param array $variables
     * @return array
     */
    public function getCaseVariables($caseId, $variables = array())
    {
        $case = $this->getCase($caseId);

        $defaults = array(
            'case' => $case,
            'tabs' => $this->getTabInformationArray(),
            'details' => $this->getCaseDetailsArray($case),
            'summary' => $this->getCaseSummaryArray($case)
        );

        return array_merge($defaults, $variables);
    }
}
