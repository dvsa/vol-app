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
class CaseController extends AbstractController
{
    protected $title;
    protected $subTitle;

    /**
     * @param $caseId
     *
     * Quick method to generate titles - needs to be done properly at some stage
     */
    public function getTitles($caseId)
    {
        $case = $this->getCase($caseId);

        $this->title = 'Case ' . $caseId;

        $this->subTitle = $case['licence']['organisation']['name'] . ' ' . '#' . $case['licence']['licNo'];
    }

    /**
     * Manage action.
     */
    public function manageAction()
    {
        $caseId = $this->fromRoute('case');
        $licence = $this->fromRoute('licence');
        $action = $this->fromRoute('tab');
        $this->getTitles($caseId);

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

        return $this->renderView($view, $this->title, $this->subTitle);
    }

    public function getSubmissions($caseId)
    {
        $bundle = array(
            'children' => array(
                'submissionActions' => array(
                    'properties' => 'ALL',
                    'children' => array(
                        'senderUser' => array(
                            'properties' => 'ALL'
                        ),
                        'recipientUser' => array(
                            'properties' => 'ALL'
                        ),
                    )
                )
            )
        );

        $config = $this->getServiceLocator()->get('Config');
        $submissionActions = $config['static-list-data'];
        $results = $this->makeRestCall('Submission', 'GET', array('case' => $caseId), $bundle);

        foreach ($results['Results'] as $k => &$result) {

            $result['status'] = 'Draft';

            foreach ($result['submissionActions'] as $action) {

                $result['urgent'] = $action['urgent'];

                if (isset($action['recipientUser']['name'])) {
                    $result['currentlyWith'] = $action['recipientUser']['name'];
                }

                $actions = isset($submissionActions['submission_'.$action['submissionActionType']])
                    ? $submissionActions['submission_'.$action['submissionActionType']] : '';

                $result['status'] = isset($actions[$action['submissionActionStatus']])
                    ? $actions[$action['submissionActionStatus']] : '';

                $result['type'] = ucfirst($action['submissionActionType']);

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
                'submissionSections' => array(
                    'properties' => array(
                        'id',
                        'description'
                    )
                ),
                'legacyOffences' => array(

                ),
                'licence' => array(
                    'properties' => 'ALL',
                    'children' => array(
                        'status' => array(
                            'properties' => array('id')
                        ),
                        'licenceType' => array(
                            'properties' => array('id')
                        ),
                        'goodsOrPsv' => array(
                            'properties' => array('id')
                        ),
                        'trafficArea' => array(
                            'properties' => 'ALL'
                        ),
                        'organisation' => array(
                            'properties' => 'ALL',
                            'children' => array(
                                'type' => array(
                                    'properties' => array('id')
                                )
                            )
                        )
                    )
                )
            )
        );

        $case = $this->makeRestCall('Cases', 'GET', array('id' => $caseId), $bundle);

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
                'label' => 'Appeal & Stays',
                'url' => $pm->get('url')->fromRoute('case_manage', ['tab' => 'stays'], [], true),
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

        if (isset($static['business_types'][$case['licence']['organisation']['type']['id']])) {
            $entityType = $static['business_types'][$case['licence']['organisation']['type']['id']];
        }

        if (isset($case['submissionSections']) && !empty($case['submissionSections'])) {

            foreach ($case['submissionSections'] as $category) {
                $categoryNames[] = $category['description'];
            }
        }

        $openDate = date('d/m/Y', strtotime($case['openDate']));

        $summary = [
            'description' => [
                'label' => 'Description',
                'value' => $case['description'],
                'url' => ''
            ],
            'open_date' => [
                'label' => 'Open date',
                'value' => $openDate,
                'url' => ''
            ],
            'licence_type' => [
                'label' => 'Licence type',
                'value' => $case['licence']['licenceType']['id'],
                'url' => ''
            ],
            'entity_type' => [
                'label' => 'Entity type',
                'value' => $entityType,
                'url' => ''
            ],
            'submissionSections' => [
                'label' => 'Categories',
                'value' => implode(', ', $categoryNames),
                'url' => ''
            ],
            'status' => [
                'label' => 'Status',
                'value' => ($case['closeDate'] == null ? 'Open' : 'Closed'),
                'url' => ''
            ],
            'licence_status' => [
                'label' => 'Licence status',
                'value' => $case['licence']['status']['id'],
                'url' => ''
            ],
            'ecmsNo' => [
                'label' => 'ECMS',
                'value' => $case['ecmsNo'],
                'url' => ''
            ],
        ];

        return $summary;
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

        $pagination = [];
        $pagination['url'] = $this->url();
        $pagination['licence'] = $this->fromRoute('licence');
        $pagination['page'] = $this->fromRoute('page', 1);
        $pagination['sort'] = $this->fromRoute('sort', 'id');
        $pagination['order'] = $this->fromRoute('order', 'desc');
        $pagination['limit'] = $this->fromRoute('limit', 10);

        $bundle = array(
            'children' => array(
                'caseType' => array(
                    'properties' => 'ALL'
                )
            )
        );

        $results = $this->makeRestCall('Cases', 'GET', $pagination, $bundle);

        $table = $this->getServiceLocator()->get('Table')->buildTable('case', $results, $pagination);

        $licenceData = $this->getLicence($licence);

        $view = $this->getView(
            array(
                'licence' => $licence,
                'licenceData' => $licenceData,
                'table' => $table,
                'data' => $pageData
            )
        );
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

        $form = $this->generateFormWithData('case', 'processAddCase', array('licence' => $licence));

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
                'submissionSections' => array(
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
            'Cases',
            'GET',
            array('id' => $case, 'licence' => $licence),
            $bundle
        );

        if (empty($result)) {
            return $this->notFoundAction();
        }

        $categories = $result['submissionSections'];
        unset($result['submissionSections']);

        $result['fields'] = $result;

        $result['submissionSections'] = $this->unFormatCategories($categories);

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
                'status' => array(
                    'properties' => array('id')
                ),
                'licenceType' => array(
                    'properties' => array('id')
                ),
                'goodsOrPsv' => array(
                    'properties' => array('id')
                ),
                'organisation' => [
                    'properties' => 'ALL'
                ]
            ]
        ];

        $licenceData = $this->makeRestCall('Licence', 'GET', array('id' => $licence), $bundle);

        return array(
            'organisation' => $licenceData['organisation']['name'],
            'licence' => $licenceData['licNo']
        );
    }

    public function deleteAction()
    {
        $licence = $this->fromRoute('licence');
        $case = $this->fromRoute('case');

        $result = $this->makeRestCall('Cases', 'GET', array('id' => $case, 'licence' => $licence));

        if (empty($result)) {
            return $this->notFoundAction();
        }

        $this->makeRestCall('Cases', 'DELETE', array('id' => $case));

        $this->redirect()->toRoute('licence/cases', array('licence' => $licence));
    }

    /**
     * Process adding the case
     *
     * @param type $data
     */
    public function processAddCase($data)
    {
        // @todo sort this out - Additional fields (Mocked for now)
        $data['openDate'] = date('Y-m-d H:i:s');
        $data['owner'] = 7;
        $data['caseType'] = 'case_t_lic';

        $licence = $this->fromRoute('licence');

        $data['submissionSections'] = $this->formatCategories($data['submissionSections']);
        $data = array_merge($data, $data['fields']);

        $result = $this->processAdd($data, 'Cases');

        if (isset($result['id'])) {
            $this->redirect()->toRoute(
                'case_manage',
                array(
                    'licence' => $licence,
                    'case' => $result['id'],
                    'tab' => 'overview'
                )
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
        $data['submissionSections'] = $this->formatCategories($data['submissionSections']);
        $data = array_merge($data, $data['fields']);

        $this->processEdit($data, 'Cases');

        $this->redirect()->toRoute('licence/cases', array('licence' => $data['licence']));
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

    /**
     * Returns true or false depending on whether a case has an appeal which hasn't been withdrawn
     *
     * @param int $caseId
     * @return bool
     */
    public function caseHasAppeal($caseId)
    {
        $appeal = $this->makeRestCall('Appeal', 'GET', array('case' => $caseId, 'isWithdrawn' => 0));
        return ($appeal['Count'] ? true : false);
    }

    /**
     * Checks whether a stay already exists for the given case and stay type (only one should be allowed)
     *
     * @param int $caseId
     * @param int $stayTypeId

     * @return boolean
     */
    public function caseHasStay($caseId, $stayTypeId)
    {
        $result = $this->makeRestCall(
            'Stay',
            'GET',
            array(
                'stayType' => $stayTypeId,
                'case' => $caseId,
                'isWithdrawn' => 0
            )
        );

        return $result['Count'] ? true : false;
    }
}
