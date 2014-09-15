<?php

/**
 * Case Conviction Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace Olcs\Controller\Cases\Conviction;

use Zend\View\Model\ViewModel;
use Zend\Json\Json as Json;
//use Olcs\Controller\Traits\DeleteActionTrait;
use Olcs\Controller\Traits\DefendantSearchTrait;
// Olcs
use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits as ControllerTraits;

/**
 * Case Conviction Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class ConvictionController extends OlcsController\CrudAbstract
{
    use DefendantSearchTrait;
    use ControllerTraits\CaseControllerTrait;

    /**
     * Identifier name
     *
     * @var string
     */
    protected $identifierName = 'conviction';

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'conviction';

    /**
     * The current page's extra layout, over and above the
     * standard base template, a sibling of the base though.
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
    protected $service = 'Conviction';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represneted by a single navigation id.
     */
    protected $navigationId = 'case_details_convictions';

    /**
     * Holds an array of variables for the default
     * index list page.
     */
    protected $listVars = [
        'case',
    ];

    /**
     * Data map
     *
     * @var array
    */
    protected $dataMap = array(
        'main' => array(
            'mapFrom' => array(
                'data'
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
            'case' => array(
                'properties' => 'ALL'
            ),
            'convictionCategory' => array(
                'properties' => array(
                    'id',
                    'description'
                ),
                'children' => array(
                    'parent' => array(
                        'properties' => array(
                            'id',
                            'description'
                        )
                    )
                )
            ),
            'defendantType' => array(
                'properties' => 'ALL'
            )
        )
    );

    public function indexAction()
    {
        return parent::indexAction();
    }

    /**
     * Dealt action
     */
    public function dealtAction()
    {
        $conviction = $this->loadCurrent();

        $data = array_intersect_key($conviction, array_flip(['id', 'version']));
        $data['isDealtWith'] = 'Y';

        $this->save($data);

        $this->addSuccessMessage('Successfully marked as "Dealt with"');

        return $this->redirect()->toRoute('conviction', ['action' => 'index', 'conviction' => null], [], true);
    }

    /**
     * add action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function addAction()
    {
        $this->getServiceLocator()->get('Navigation')->findOneBy('id', 'case_details_convictions')->setActive();

        $routeParams = $this->getParams(array('case', 'id'));

        $form = $this->generateFormWithData(
            'conviction',
            'processConviction',
            ['case' => $this->getQueryOrRouteParam('case')]
        );

        $request = $this->getRequest();
        if ($request->isGet()) {
            $form->get('defendant-details')->remove('personSearch');
            $form->get('defendant-details')->remove('personFirstname');
            $form->get('defendant-details')->remove('personLastname');
            $form->get('defendant-details')->remove('birthDate');
            $form->get('defendant-details')->remove('search');
        } else {
            $posted = $request->getPost();
        }

        $parentCategory = $this->getConvictionParentCategories();

        $form->get('offence')
            ->get('parentCategory')
            ->setValueOptions($parentCategory);

        if (isset($posted['offence']['parentCategory']) && $posted['offence']['parentCategory']) {
            $subCategory = $this->getConvictionSubCategories($posted['offence']['parentCategory']);

            foreach ($subCategory['Results'] as $category) {
                $formSubCategory[$category['id']] = $category['description'];
            }

            $form->get('offence')
                ->get('convictionCategory')
                ->setValueOptions($formSubCategory);
        }

        $this->getViewHelperManager()->get('placeholder')->getContainer('form')->set($form);
        $this->loadScripts(['conviction']);

        $view = new ViewModel(
            array(
                'params' => array(
                    'pageTitle' => 'add-conviction',
                    'pageSubTitle' => 'Please add conviction details'
                )
            )
        );

        $view->setTemplate('crud/form');
        return $this->renderView($view);
    }

    /**
     * The edit action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        $data = $this->loadCurrent();

        if (isset($data['case'])) {
            $data['case'] = $data['case']['id'];
        }

        if (!empty($data['convictionCategory'])) {
            $data['parentCategory'] = $data['convictionCategory']['parent']['id'];

            //check for user defined text
            if (!$this->isUserDefinedConvictionCategory($data['convictionCategory']['id'])) {
                $data['categoryText'] = $data['convictionCategory']['description'];
            }

            $data['convictionCategory'] = $data['convictionCategory']['id'];
        }

        $data['defendantType'] = $data['defendantType']['id'];

        $data['defendant-details'] = $data;
        $data['offence'] = $data;

        // set entity data to make form builder aware of fieldsets it has to
        // generate
        $this->setEntityData($data);

        //die('<pre>' . print_r($data, 1));

        $form = $this->generateFormWithData(
            'conviction',
            'processConviction',
            $data
        );

        $parentCategory = $this->getConvictionParentCategories();

        $form->get('offence')
            ->get('parentCategory')
            ->setValueOptions($parentCategory);

        $formSubCategory = array();

        $request = $this->getRequest();

        if ($request->isGet()) {
            if ($data['defendantType'] == 'def_t_op') {
                $form->get('defendant-details')->remove('personSearch');
                $form->get('defendant-details')->remove('personFirstname');
                $form->get('defendant-details')->remove('personLastname');
                $form->get('defendant-details')->remove('birthDate');
                $form->get('defendant-details')->remove('search');
                $form->get('defendant-details')->remove('operatorSearch');
                $form->get('defendant-details')->remove('entity-list');
                $form->get('defendant-details')->remove('select');
            } else {
                $form->get('defendant-details')->remove('personSearch');
                $form->get('defendant-details')->remove('search');
            }
        } else {
            $posted = $request->getPost();
        }

        if (isset($posted['offence']['parentCategory']) && $posted['offence']['parentCategory']) {
            $data['parentCategory'] = $posted['offence']['parentCategory'];
        }

        if (isset($data['parentCategory'])) {
            $subCategory = $this->getConvictionSubCategories($data['parentCategory']);

            foreach ($subCategory['Results'] as $category) {
                $formSubCategory[$category['id']] = $category['description'];
            }
        }
        $form->get('offence')
            ->get('convictionCategory')
            ->setValueOptions($formSubCategory);

        $this->getViewHelperManager()->get('placeholder')->getContainer('form')->set($form);
        $this->loadScripts(['conviction']);

        $view = new ViewModel(
            array(
                'params' => array(
                    'pageTitle' => 'edit-conviction',
                    'pageSubTitle' => 'Edit the conviction'
                )
            )
        );

        $view->setTemplate('crud/form');
        return $this->renderView($view);
    }

    /**
     * Processes the conviction form
     *
     * @param array $data
     */
    public function processConviction($data)
    {
        $data = array_merge_recursive($data, $data['defendant-details'], $data['offence']);

        //two unsets here keeps line length under 120
        //keeps phpunit happy as it isn't detecting the code has
        //been run when the parameters are on more than one line!
        $fieldsToUnset = [
            'defendant-details', 'cancel-conviction', 'offence',
            'save', 'cancel', 'conviction','conviction-operator'
        ];
        foreach ($fieldsToUnset as $field) {
            if (isset($data[$field])) {
                unset($data[$field]);
            }
        }

        //we only have category text in the conviction table for the user defined type
        if (!$this->isUserDefinedConvictionCategory($data['convictionCategory'])) {
            $data['categoryText'] = '';
        }

        $routeParams = $this->getParams(array('action', 'licence', 'case'));

        //die('<pre>' . print_r($data, 1));

        if (strtolower($routeParams['action']) == 'edit' || strtolower($routeParams['action']) == 'dealt') {
            unset($data['case'], $data['parentCategory']);
            $this->processEdit($data, 'Conviction');
        } else {
            $this->processAdd($data, 'Conviction');
        }

        $this->addSuccessMessage('Saved sucessfully');

        return $this->redirect()->toRoute('conviction', ['action' => 'index', 'conviction' => null], [], true);
    }

    /**
     * Gets categories (used in ajax call)
     *
     * @return \Zend\Http\PhpEnvironment\Response
     */
    public function categoriesAction()
    {
        $response = $this->getResponse();
        $parent = $this->fromPost('parent', null);

        if (!$parent) {
            $response->setContent(Json::encode(array('success' => 1, 'error' => 'Category not found')));
        } else {
            $categories = $this->getConvictionSubCategories($parent);

            $response->setContent(Json::encode(array('success' => 1, 'categories'=> $categories['Results'])));
        }

        return $response;
    }

    /**
     * Gets conviction parent categories
     *
     * @return array
     */
    private function getConvictionParentCategories()
    {
        $bundle = array(
            'children' => array(
                'parent' => array(
                    'properties' => array(
                        'id'
                    )
                )
            )
        );

        $categories = $this->makeRestCall(
            'ConvictionCategory',
            'GET',
            array(
                'limit' => 'all',
                'sort' => 'description',
                'bundle' => Json::encode($bundle)
            )
        );

        $parentCategory = array();

        //not efficient but can't match against a null value in bundler
        foreach ($categories['Results'] as $category) {
            if (empty($category['parent'])) {
                $parentCategory[$category['id']] = $category['description'];
            }
        }

        return $parentCategory;
    }

    /**
     * Gets sub categories
     *
     * @param int $parent
     * @return array
     */
    private function getConvictionSubCategories($parent)
    {
        $bundle = array(
            'properties' => array(
                'id',
                'description'
            ),
        );

        return $this->makeRestCall(
            'ConvictionCategory',
            'GET',
            array(
                'parent' => $parent,
                'limit' => 'all',
                'sort' => 'description',
                'bundle' => Json::encode($bundle)
            )
        );
    }

    /**
     * Creates and returns the comment form.
     *
     * @param array $case
     * @return \Zend\Form\Form
     */
    public function generateCommentForm($case)
    {
        $data = [];
        $data['main'] = $case;

        $form = $this->generateForm(
            'ConvictionComment',
            'saveCommentForm'
        );
        $form->setData($data);

        return $form;
    }

    /**
     * Saves the comment form.
     *
     * @param array $data
     */
    public function saveCommentForm($data)
    {
        if (isset($data['main'])) {
            $data = $data + $data['main'];
            unset($data['main']);
        }

        $data = array_intersect_key($data, array_flip(['id', 'convictionNote', 'version']));
        $this->processEdit($data, 'Cases');

        return $this->redirect()->toRoute('case_convictions', [], [], true);
    }

    private function getIndexBundle()
    {
        return array(
            'children' => array(
                'convictionCategory' => array(
                    'properties' => array(
                        'id',
                        'description'
                    )
                ),
                'defendantType' => array(
                    'properties' => 'ALL'
                )
            )
        );
    }


    public function getLegacyOffencesTable($legacyOffencesResults)
    {
        $legacyOffencesTable = $this->getTable('legacyOffences', $legacyOffencesResults);

        return $legacyOffencesTable;
    }

    public function viewOffenceAction()
    {
        $postParams = $this->params()->fromPost();
        $routeParams = $this->params()->fromRoute();

        $this->setBreadcrumb(array('licence_case_list/pagination' => array('licence' => $routeParams['licence'])));

        $caseId = $routeParams['case'];
        $offenceId = $routeParams['offenceId'];

        $case = $this->getCase($caseId, $offenceId);

        $offence = false;
        if (!empty($case['legacyOffences'])) {
            foreach ($case['legacyOffences'] as $legacyOffence) {

                if ($legacyOffence['id'] ==  $offenceId) {
                    $offence = $legacyOffence;
                }
            }
        }
        $summary = $this->getCaseSummaryArray($case);

        $view = $this->getView();
        $tabs = $this->getTabInformationArray();

        $action = 'view';

        $view->setVariables(
            [
                'case' => $case,
                'offence' => $offence,
                'tabs' => $tabs,
                'tab' => $action,
                'summary' => $summary
            ]
        );
        $view->setTemplate('case/view-offence');
        return $view;
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
