<?php

/**
 * Conviction controller
 *
 * Manages convictions
 *
 * @author Mike Cooper <michael.cooper@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller;

use Zend\View\Model\ViewModel;
use \Zend\Json\Json as Json;

/**
 * Conviction controller
 *
 * Manages convictions
 */
class ConvictionController extends CaseController
{

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

        $data = array('vosaCase' => $routeParams['case']);
        $results = $this->makeRestCall('VosaCase', 'GET', array('id' => $routeParams['case']));

        if (empty($routeParams['case']) || empty($routeParams['licence']) || empty($results)) {
            return $this->getResponse()->setStatusCode(404);
        }

        $form = $this->generateFormWithData(
            'conviction',
            'processConviction',
            $data,
            true
        );

        $posted = $this->getRequest()->getPost();

        $parentCategory = $this->getConvictionParentCategories();

        $form->get('offence')
            ->get('parentCategory')
            ->setValueOptions($parentCategory);

        if(isset($posted['offence']['parentCategory']) && $posted['offence']['parentCategory']){
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
                'headScript' => array('/static/js/conviction.js'),
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
                'vosaCase' => array(
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

        if (isset($data['vosaCase'])) {
            $data['vosaCase'] = $data['vosaCase']['id'];
        }

        if (empty($routeParams['case']) || empty($routeParams['licence']) || empty($data)) {
            return $this->getResponse()->setStatusCode(404);
        }

        if (!empty($data['category'])) {
            $data['parentCategory'] = $data['category']['parent']['id'];

            //check for user defined text
            if ($data['category']['id'] != 168) {
                $data['categoryText'] = $data['category']['description'];
            }

            $data['category'] = $data['category']['id'];
        }

        $data['id'] = $routeParams['id'];
        $data['defendant-details'] = $data;
        $data['offence'] = $data;

        $form = $this->generateFormWithData(
            'conviction',
            'processConviction',
            $data,
            true
        );

        $parentCategory = $this->getConvictionParentCategories();

        $form->get('offence')
            ->get('parentCategory')
            ->setValueOptions($parentCategory);

        $formSubCategory = array();

        $posted = $this->getRequest()->getPost();

        if(isset($posted['offence']['parentCategory']) && $posted['offence']['parentCategory']){
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
                'headScript' => array(
                    '/static/js/conviction.js'
                ),
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
        if ($data['category'] != 168) {
            $data['categoryText'] = '';
        }

        $routeParams = $this->getParams(array('action', 'licence', 'case'));

        if (strtolower($routeParams['action']) == 'edit' || strtolower($routeParams['action']) == 'dealt') {
            unset($data['vosaCase'], $data['parentCategory']);
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
}
