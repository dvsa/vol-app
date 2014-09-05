<?php

/**
 * Case Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller\Cases;

use Zend\View\Model\ViewModel;
use Olcs\Controller\Cases\AbstractController as AbstractCasesController;

/**
 * Case Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CaseController extends AbstractCasesController
{
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
     * This action is the case overview page.
     */
    public function overviewAction()
    {
        $view = $this->getView([]);

        $view->setTemplate('case/overview');

        return $this->renderView($view);
    }

    public function redirectAction()
    {
        return $this->redirect()->toRoute('case', ['action' => 'overview'], [], true);
    }

    public function convictionsAction()
    {
        return $this->redirect()->toRoute('case', ['action' => 'overview'], [], true);
    }


    /**
     * Gets the case by ID.
     *
     * @param integer $id
     * @return array
     */
    public function getCase($id = null)
    {
        if (is_null($id)) {
            $id = $this->getIdentifier();
        }

        return $this->load($id);
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

        $view = $this->getView();
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
        $case = $this->fromRoute('case');

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
     * Process adding the case
     *
     * @param type $data
     */
    public function processAddCase($data)
    {
        // @todo sort this out - Additional fields (Mocked for now)
        $data['openDate'] = date('Y-m-d H:i:s');

        // This should be the logged in user.
        $data['owner'] = 7;

        // This needs looking at - it might not be a case type of licence.
        $data['caseType'] = 'case_t_lic';

        $data['submissionSections'] = $this->formatCategories($data['submissionSections']);
        $data = array_merge($data, $data['fields']);

        $result = $this->processAdd($data, 'Cases');

        if (isset($result['id'])) {
            $this->redirect()->toRoute('case', array('case' => $result['id'], 'action' => 'overview'));
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

        $this->redirect()->toRoute('case', array('case' => $data['id'], 'action' => 'overview'));
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
