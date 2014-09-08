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
     * Identifier name
     *
     * @var string
     */
    protected $identifierName = 'case';

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'case';

    /**
     * The current page's extra layout, over and above the
     * standard base template
     *
     * @var string
     */
    protected $pageLayout = 'case';

    protected $pageLayoutInner = 'case/inner-layout';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'Cases';

    /**
     * Data map
     *
     * @var array
     */
    protected $dataMap = array(
        'main' => array(
            'mapFrom' => array(
                'fields'
            )
        )
    );

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

    protected $detailsView = 'case/overview';

    /**
     * Holds an array of variables for the default
     * index list page.
     */
    protected $listVars = [
        'licence',
        'application',
        'transportManager'
    ];

    /**
     * This action is the case overview page.
     */
    public function overviewAction()
    {
        return $this->detailsAction();

        $view = $this->getView([]);

        $view->setTemplate('case/overview');

        return $this->renderView($view);
    }

    public function redirectAction()
    {
        return $this->redirect()->toRoute('case', ['action' => 'overview'], [], true);
    }

    /* public function convictionsAction()
    {
        return $this->redirect()->toRoute('case', ['action' => 'overview'], [], true);
    } */

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
        return $this->editAction();
    }

    /**
     * Edit a new case
     *
     * @return ViewModel
     */
    public function editAction()
    {
        // we don't want the ewrapping view/layout
        $this->pageLayout = null;

        $result = $this->loadCurrent();

        //die ('<pre>' . print_r($result, 1));

        // Data to eventually populate the form.
        $data = [];

        if ($this->fromRoute('case') && $result) { // edit

            $data += $result;
            $categories = $data['submissionSections'];
            unset($result['submissionSections']);
            $data['submissionSections'] = $this->unFormatCategories($categories);
            $data['licence'] = $data['licence']['id'];

        } else { // add

            // A case can belong to many things, not just a licence.
            $licence = $this->fromRoute('licence');
            if (!empty($licence)) {
                $data['licence'] = $licence;
            }
        }

        $data['fields'] = $data;

        $form = $this->generateFormWithData(
            'case',
            'processSaveCase',
            $data
        );

        $view = $this->getView(['form' => $form]);
        $view->setTemplate('form');
        return $this->renderView($view);
    }

    /**
     * Process updating the case
     *
     * @param type $data
     */
    public function processSaveCase($data)
    {

        if (empty($data['fields']['id'])) { // new

            $data['fields']['openDate'] = date('Y-m-d H:i:s');

            // This should be the logged in user.
            $data['fields']['owner'] = $this->getLoggedInUser();

            // This needs looking at - it might not be a case type of licence.
            if (isset($data['fields']['licence']) && !empty($data['fields']['licence'])) {
                $data['fields']['caseType'] = 'case_t_lic';
            } else {
                $data['fields']['caseType'] = 'case_t_app';
            }
        }
        //die('<pre>' . print_r($data, 1));

        $data['fields']['submissionSections'] = $this->formatCategories($data['submissionSections']);

        $case = $this->processSave($data);

        if (!empty($case)) {
            $caseId = $case['id'];
        } else {
            $caseId = $this->getIdentifier();
        }

        $this->redirect()->toRoute('case', array('case' => $caseId, 'action' => 'overview'));
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
    /* public function isUserDefinedConvictionCategory($categoryId)
    {
        $userDefined = array(168);
        return in_array($categoryId, $userDefined);
    } */

    /**
     * Returns true or false depending on whether a case has an appeal which hasn't been withdrawn
     *
     * @param int $caseId
     * @return bool
     */
    /* public function caseHasAppeal($caseId)
    {
        $appeal = $this->makeRestCall('Appeal', 'GET', array('case' => $caseId, 'isWithdrawn' => 0));
        return ($appeal['Count'] ? true : false);
    } */

    /**
     * Checks whether a stay already exists for the given case and stay type (only one should be allowed)
     *
     * @param int $caseId
     * @param int $stayTypeId

     * @return boolean
     */
    /* public function caseHasStay($caseId, $stayTypeId)
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
    } */
}
