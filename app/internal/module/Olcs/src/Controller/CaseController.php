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
        $params = $this->params()->fromPost();
        $params = array_merge($params, array('case' => $caseId, 'licence' => $licence));

        if (isset($params['action'])) {
            return $this->forward()->dispatch(
                'SubmissionController',
                $params
            );
        }

        $tabs = $this->getTabInformationArray();

        if (!array_key_exists($action, $tabs)) {
            return $this->notFoundAction();
        }

        $view = $this->getView();

        $case = $this->getCase($caseId);

        $summary = $this->getCaseSummaryArray($case);

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
                'submissions' => $submissionsTable
            )
        );

        $view->setTemplate('case/manage');
        return $view;
    }

    public function getSubmissions($caseId)
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
                    )
                )
            )
        );

        $config = $this->getServiceLocator()->get('Config');
        $submissionActions = $config['static-list-data'];
        $results = $this->makeRestCall('Submission', 'GET', array('vosaCase' => $caseId), $bundle);

        foreach ($results['Results'] as $k => $result) {

            $results['Results'][$k]['status'] = 'Draft';

            foreach ($result['submissionActions'] as $action) {

                $results['Results'][$k]['urgent'] = $action['urgent'];

                if (isset($action['userRecipient']['name'])) {
                    $results['Results'][$k]['currentlyWith'] = $action['userRecipient']['name'];
                }

                $actions = isset($submissionActions['submission_'.$action['submissionActionType']])
                    ? $submissionActions['submission_'.$action['submissionActionType']] : '';

                $results['Results'][$k]['status'] = isset($actions[$action['submissionActionStatus']])
                    ? $actions[$action['submissionActionStatus']] : '';

                $results['Results'][$k]['type'] = ucfirst($action['submissionActionType']);

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

    /**
     * Gets a variable from the route
     *
     * @param string $param
     * @param mixed $default
     * @return type
     */
    public function fromRoute($param, $default = null)
    {
        return $this->params()->fromRoute($param, $default);
    }

    /**
     * Gets a variable from postdata
     *
     * @param string $param
     * @param mixed $default
     * @return type
     */
    public function fromPost($param, $default = null)
    {
        return $this->params()->fromPost($param, $default);
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
                'url' => $pm->get('url')->fromRoute('case_prohibition', ['tab' => 'prohibitions'], [], true),
            ],
            'annual_test_history' => [
                'key' => 'annual_test_history',
                'label' => 'Annual test history',
                'url' => $pm->get('url')->fromRoute(
                    'case_annual_test_history',
                    ['tab' => 'annual_test_history'],
                    [],
                    true
                ),
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
                'url' => $pm->get('url')->fromRoute('case_complaints', ['tab' => 'complaints'], [], true),
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
            'documents' => [
                'key' => 'documents',
                'label' => 'Documents',
                'url' => $pm->get('url')->fromRoute('case_manage', ['tab' => 'documents'], [], true),
            ],
            'notes' => [
                'key' => 'notes',
                'label' => 'Notes',
                'url' => $pm->get('url')->fromRoute('case_manage', ['tab' => 'notes'], [], true),
            ],
            'conditions-undertakings' => [
                'key' => 'conditions-undertakings',
                'label' => 'Conditions &amp; Undertakings',
                'url' => $pm->get('url')->fromRoute(
                    'case_conditions_undertakings',
                    ['tab' => 'conditions-undertakings'],
                    [],
                    true
                ),
            ],
            'impounding' => [
                'key' => 'impounding',
                'label' => 'Impounding',
                'url' => $pm->get('url')->fromRoute(
                    'case_impounding',
                    ['tab' => 'impounding', 'action' => null],
                    [],
                    true
                ),
            ],
            'revoke' => [
                'key' => 'revoke',
                'label' => 'In-Office revocation',
                'url' => $pm->get('url')->fromRoute('case_revoke', ['tab' => 'revoke', 'action' => 'index'], [], true),
            ],
            'pi' => [
                'key' => 'pi',
                'label' => 'Public inquiry',
                'url' => $pm->get('url')->fromRoute('case_pi', ['tab' => 'pi', 'action' => 'index'], [], true),
            ],
        ];

        return $tabs;
    }

    public function getCaseSummaryArray(array $case)
    {
        $categoryNames = array();

        $config = $this->getServiceLocator()->get('Config');
        $static = $config['static-list-data'];

        $entityType = '';

        if (isset($static['business_types'][$case['licence']['organisation']['organisationType']])) {
            $entityType = $static['business_types'][$case['licence']['organisation']['organisationType']];
        }

        if (isset($case['categories']) && !empty($case['categories'])) {

            foreach ($case['categories'] as $category) {
                $categoryNames[] = $category['name'];
            }
        }

        $opentimeDate = date('d/m/Y', strtotime($case['openTime']));

        $smmary = [
            'description' => [
                'label' => 'Description',
                'value' => $case['description'],
                'url' => ''
            ],
            'open_date' => [
                'label' => 'Open date',
                'value' => $opentimeDate,
                'url' => ''
            ],
            'licence_type' => [
                'label' => 'Licence type',
                'value' => $case['licence']['licenceType'],
                'url' => ''
            ],
            'entity_type' => [
                'label' => 'Entity type',
                'value' => $entityType,
                'url' => ''
            ],
            'categories' => [
                'label' => 'Categories',
                'value' => implode(', ', $categoryNames),
                'url' => ''
            ],
            'status' => [
                'label' => 'Status',
                'value' => $case['status'],
                'url' => ''
            ],
            'licence_status' => [
                'label' => 'Licence status',
                'value' => $case['licence']['licenceStatus'],
                'url' => ''
            ],
            'ecms' => [
                'label' => 'ECMS',
                'value' => $case['ecms'],
                'url' => ''
            ],
        ];

        return $smmary;
    }

    /**
     * List of cases if we have a licence
     */
    public function indexAction()
    {
        $licence = $this->fromRoute('licence');

        if (empty($licence)) {

            return $this->notFoundAction();
        }

        $action = $this->fromPost('action');

        if (!empty($action)) {

            $action = strtolower($action);

            if ($action !== 'add') {

                $id = $this->fromPost('id');

                if (empty($id)) {
                    return $this->crudActionMissingId();
                }

                return $this->redirect()->toRoute(
                    'licence_case_action',
                    array(
                        'action' => $action,
                        'case' => $id,
                        'licence' => $licence
                    )
                );
            } else {
                return $this->redirect()->toRoute(
                    'licence_case_action',
                    array('action' => $action, 'licence' => $licence)
                );
            }
        }

        $pageData = $this->getPageData($licence);

        $pagination['url'] = $this->url();
        $pagination['licence'] = $this->fromRoute('licence');
        $pagination['page'] = $this->fromRoute('page', 1);
        $pagination['sort'] = $this->fromRoute('sort', 'caseNumber');
        $pagination['order'] = $this->fromRoute('order', 'desc');
        $pagination['limit'] = $this->fromRoute('limit', 10);

        $results = $this->makeRestCall('VosaCase', 'GET', $pagination);

        $table = $this->getServiceLocator()->get('Table')->buildTable('case', $results, $pagination);

        $view = $this->getView(array('licence' => $licence, 'table' => $table, 'data' => $pageData));
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
        $licence = $this->fromRoute('licence');
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

        $view = $this->getView(['form' => $form, 'data' => $pageData]);
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
        $licence = $this->fromRoute('licence');
        $case = $this->fromRoute('case');
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
            $result
        );

        $pageData = $this->getPageData($licence);

        $view = $this->getView(['form' => $form, 'data' => $pageData]);
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
        $licence = $this->fromRoute('licence');
        $case = $this->fromRoute('case');

        $result = $this->makeRestCall('VosaCase', 'GET', array('id' => $case, 'licence' => $licence));

        if (empty($result)) {
            return $this->notFoundAction();
        }

        $this->makeRestCall('VosaCase', 'DELETE', array('id' => $case));

        $this->redirect()->toRoute('licence_case_list', array('licence' => $licence));
    }

    /**
     * Process adding the case
     *
     * @todo Additional fields are required for persisting - Find out where these fields come from
     *
     * @param type $data
     */
    public function processAddCase($data)
    {
        // Additional fields (Mocked for now)
        $data['caseNumber'] = 12345678;
        $data['openTime'] = date('Y-m-d H:i:s');
        $data['owner'] = 7;

        $licence = $this->fromRoute('licence');

        $data['categories'] = $this->formatCategories($data['categories']);
        $data = array_merge($data, $data['fields']);

        $result = $this->processAdd($data, 'VosaCase');

        if (isset($result['id'])) {
            $this->redirect()->toRoute(
                'case_manage',
                array(
                    'licence' => $licence,
                    'case' => $result['id'],
                    'tab' => 'overview')
            );
        }
    }

    /**
     * Process updating the case
     *
     * @param type $data
     */
    public function processEditCase($data)
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
            'summary' => $this->getCaseSummaryArray($case)
        );

        return array_merge($defaults, $variables);
    }

    /**
     * Returns true if the category id is a type allowing a user defined description
     *
     * @param int $categoryId
     *
     * @return bool
     */
    public function isUserDefinedConvictionCategory($categoryId)
    {
        $userDefined = array(168);
        return in_array($categoryId, $userDefined);
    }
}
