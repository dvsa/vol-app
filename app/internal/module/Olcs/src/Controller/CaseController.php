<?php

/**
 * Case Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller;

use Zend\View\Model\ViewModel;

/**
 * Case Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CaseController extends AbstractController
{
    use Traits\LicenceControllerTrait;

    protected $title;
    protected $subTitle;

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'Cases';

    /**
     * Holds the Data Bundle
     *
     * @var array
     */
    protected $dataBundle = array(
        'children' => array(
            'submissionSections' => array(
                'properties' => array(
                    'id',
                    'description'
                )
            ),
            'legacyOffences' => array(
                'properties' => 'ALL',
            ),
            'caseType' => array(
                'properties' => 'id',
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
     * This action is the case overview page.
     */
    public function overviewAction()
    {
        $view = $this->getViewWithLicence();

        $view->{'case'} = $this->getCase($this->fromRoute('id'));

        $view->setTemplate('case/overview');

        return $this->renderView($view, $this->title, $this->subTitle);
    }

    /**
     * Gives us a list of submissions for the case.
     *
     * @param unknown $caseId
     * @return NULL
     */
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

    /**
     * Gets the case by ID.
     *
     * @param integer $caseId
     * @return array
     */
    public function getCase($caseId)
    {
        return $this->load($caseId);
    }

    /**
     * List of cases. Moved to Licence controller's cases method.
     *
     * @return void
     */
    public function indexAction()
    {
        return $this->redirect()->toRoute('licence/cases', [], [], true);
    }

    /**
     * Add a new case to a licence
     *
     * @return ViewModel
     */
    public function addAction()
    {
        $licence = $this->fromRoute('licence');

        $view = $this->getViewWithLicence();
        $form = $this->generateFormWithData('case', 'processAddCase', array('licence' => $licence));
        $view->{'form'} = $form;
        $view->setTemplate('/form');

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
        $case = $this->fromRoute('id');

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
