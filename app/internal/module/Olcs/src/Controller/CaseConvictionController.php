<?php

/**
 * Case Conviction Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace Olcs\Controller;

use Zend\View\Model\ViewModel;
use Zend\Json\Json as Json;
use Olcs\Controller\Traits\DeleteActionTrait;
use Olcs\Controller\Traits\DefendantSearchTrait;

/**
 * Case Conviction Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class CaseConvictionController extends CaseController
{
    use DeleteActionTrait;
    use DefendantSearchTrait;

    public function getDeleteServiceName()
    {
        return 'Conviction';
    }

    public function indexAction()
    {
        $postParams = $this->params()->fromPost();
        $routeParams = $this->params()->fromRoute();

        $this->setBreadcrumb(array('licence_case_list/pagination' => array('licence' => $routeParams['licence'])));

        if (isset($postParams['action'])) {
            return $this->redirect()->toRoute(
                $postParams['table'],
                array(
                    'licence' => $routeParams['licence'],
                    'case' => $routeParams['case'],
                    'id' => isset($postParams['id']) ? $postParams['id'] : '',
                    'action' => strtolower(
                        $postParams['action']
                    )
                )
            );
        }

        $view = $this->getView();
        $tabs = $this->getTabInformationArray();
        $action = 'convictions';
        $caseId = $routeParams['case'];

        $case = $this->getCase($caseId);

        $form = $this->generateCommentForm($case);

        $summary = $this->getCaseSummaryArray($case);

        $bundle = $this->getIndexBundle();

        $results = $this->makeRestCall(
            'Conviction',
            'GET',
            array(
                'case' => $caseId,
                'sort' => 'dateOfConviction',
                'order' => 'DESC',
                'bundle' => json_encode($bundle)
            )
        );

        $config = $this->getServiceLocator()->get('Config');

        foreach ($results['Results'] as $key => $row) {
            if (!$this->isUserDefinedConvictionCategory($row['category']['id'])) {
                $results['Results'][$key]['categoryText'] = $row['category']['description'];
            }

            if (isset($config['static-list-data']['defendant_types'][$row['defType']])) {
                $results['Results'][$key]['defType'] = $config['static-list-data']['defendant_types'][$row['defType']];
            }
        }

        $data = [];
        $data['url'] = $this->url();

        $table = $this->getServiceLocator()->get('Table')->buildTable('convictions', $results, $data);

        $view->setVariables(
            [
                'case' => $case,
                'tabs' => $tabs,
                'tab' => $action,
                'summary' => $summary,
                'table' => $table,
                'commentForm' => $form,
            ]
        );

        $view->setTemplate('case/manage');
        return $view;
    }

    /**
     * Dealt action
     */
    public function dealtAction()
    {
        $params = $this->getParams(['id', 'case', 'licence']);

        if (!isset($params['id']) || !is_numeric($params['id'])) {
            return $this->notFoundAction();
        }

        $case = $this->makeRestCall('Conviction', 'GET', array('id' => $params['id']));

        $data = array_intersect_key($case, array_flip(['id', 'version']));
        $data['dealtWith'] = 'Y';

        $this->processEdit($data, 'Conviction');

        return $this->redirect()->toRoute(
            'case_convictions',
            [
                'case' => $params['case'],
                'licence' => $params['licence']
            ]
        );
    }

    /**
     * add action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function addAction()
    {
        $routeParams = $this->getParams(array('case', 'licence', 'id'));

        if (isset($_POST['cancel-conviction'])) {
            return $this->redirect()->toRoute(
                'case_convictions',
                array('case' => $routeParams['case'], 'licence' => $routeParams['licence'])
            );
        }

        // Below is for setting route params for the breadcrumb
        $this->setBreadcrumb(
            array(
                'licence_case_list/pagination' => array('licence' => $routeParams['licence']),
                'case_convictions' => array('case' => $routeParams['case'], 'licence' => $routeParams['licence'])
            )
        );

        $data = array('case' => $routeParams['case']);
        $results = $this->makeRestCall('Cases', 'GET', array('id' => $routeParams['case']));

        if (empty($routeParams['case']) || empty($routeParams['licence']) || empty($results)) {
            return $this->getResponse()->setStatusCode(404);
        }

        $form = $this->generateFormWithData(
            'conviction',
            'processConviction',
            $data
        );

        $request = $this->getRequest();
        if ($request->isGet())
        {
            $form->get('defendant-details')->remove('personSearch');
            $form->get('defendant-details')->remove('personFirstname');
            $form->get('defendant-details')->remove('personLastname');
            $form->get('defendant-details')->remove('dateOfBirth');
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
                ->get('category')
                ->setValueOptions($formSubCategory);
        }

        $view = new ViewModel(
            array(
                'form' => $form,
                'inlineScript' => $this->getServiceLocator()->get('Script')->loadFiles(['conviction']),
                'params' => array(
                    'pageTitle' => 'add-conviction',
                    'pageSubTitle' => 'Please add conviction details'
                )
            )
        );

        $view->setTemplate('conviction/form');
        return $view;
    }

    /**
     * The edit action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        $routeParams = $this->getParams(
            array(
                'case',
                'licence',
                'id',
            )
        );
        if (isset($_POST['cancel-conviction'])) {
            return $this->redirect()->toRoute(
                'case_convictions',
                array('case' => $routeParams['case'], 'licence' => $routeParams['licence'])
            );
        }

        $this->setBreadcrumb(
            array(
                'licence_case_list/pagination' => array('licence' => $routeParams['licence']),
                'case_convictions' => array('case' => $routeParams['case'], 'licence' => $routeParams['licence'])
            )
        );

        $bundle = array(
            'children' => array(
                'case' => array(
                    'properties' => 'ALL'
                ),
                'category' => array(
                    'properties' => array(
                        'id',
                        'description'
                    ),
                    'children' => array(
                        'parent' => array(
                            'properties' => 'id'
                        )
                    )
                )
            )
        );

        $data = $this->makeRestCall('Conviction', 'GET', array('id' => $routeParams['id']), $bundle);

        if (isset($data['case'])) {
            $data['case'] = $data['case']['id'];
        }

        if (empty($routeParams['case']) || empty($routeParams['licence']) || empty($data)) {
            return $this->getResponse()->setStatusCode(404);
        }

        if (!empty($data['category'])) {
            $data['parentCategory'] = $data['category']['parent']['id'];

            //check for user defined text
            if (!$this->isUserDefinedConvictionCategory($data['category']['id'])) {
                $data['categoryText'] = $data['category']['description'];
            }

            $data['category'] = $data['category']['id'];
        }

        $data['id'] = $routeParams['id'];
        $data['defendant-details'] = $data;
        $data['offence'] = $data;

        // set entity data to make form builder aware of fieldsets it has to
        // generate
        $this->setEntityData($data);
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
            if ($data['defType'] == 'defendant_type.operator') {
                $form->get('defendant-details')->remove('personSearch');
                $form->get('defendant-details')->remove('personFirstname');
                $form->get('defendant-details')->remove('personLastname');
                $form->get('defendant-details')->remove('dateOfBirth');
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
            ->get('category')
            ->setValueOptions($formSubCategory);

        $view = new ViewModel(
            array(
                'form' => $form,
                'inlineScript' => $this->getServiceLocator()->get('Script')->loadFiles(['conviction']),
                'params' => array(
                    'pageTitle' => 'edit-conviction',
                    'pageSubTitle' => 'Edit the conviction'
                )
            )
        );

        $view->setTemplate('conviction/form');
        return $view;
    }

    /**
     * Processes the conviction form
     *
     * @param array $data
     */
    public function processConviction($data)
    {
        $data = array_merge($data, $data['defendant-details'], $data['offence']);

        //two unsets here keeps line length under 120
        //keeps phpunit happy as it isn't detecting the code has
        //been run when the parameters are on more than one line!
        unset(
        $data['defendant-details'], $data['cancel-conviction'], $data['offence'], $data['save']
        );

        unset(
        $data['cancel'], $data['conviction'], $data['conviction-operator']
        );

        //we only have category text in the conviction table for the user defined type
        if (!$this->isUserDefinedConvictionCategory($data['category'])) {
            $data['categoryText'] = '';
        }

        $routeParams = $this->getParams(array('action', 'licence', 'case'));

        if (strtolower($routeParams['action']) == 'edit' || strtolower($routeParams['action']) == 'dealt') {
            unset($data['case'], $data['parentCategory']);
            $result = $this->processEdit($data, 'Conviction');
        } else {
            $result = $this->processAdd($data, 'Conviction');
        }

        return $this->redirect()->toRoute(
            'case_convictions',
            array(
                'case' => $routeParams['case'],
                'licence' => $routeParams['licence']
            )
        );
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
        $form = $this->generateForm(
            'conviction-comment',
            'saveCommentForm'
        );
        $form->setData($case);

        return $form;
    }

    /**
     * Saves the comment form.
     *
     * @param array $data
     */
    public function saveCommentForm($data)
    {
        $data = array_intersect_key($data, array_flip(['id', 'convictionData', 'version']));
        $this->processEdit($data, 'Cases');

        return $this->redirect()->toRoute('case_convictions', [], [], true);
    }

    private function getIndexBundle()
    {
        return array(
            'children' => array(
                'category' => array(
                    'properties' => array(
                        'id',
                        'description'
                    )
                )
            )
        );
    }
}
