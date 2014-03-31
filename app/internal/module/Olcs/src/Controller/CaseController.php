<?php

/**
 * Case Cotnroller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller;

use Common\Controller\FormActionController;
use Zend\View\Model\ViewModel;

/**
 * Case Cotnroller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

class CaseController extends FormActionController
{
    /**
     * List of cases if we have a licence
     */
    public function indexAction()
    {
        $licence = $this->params()->fromRoute('licence');

        if (empty($licence)) {

            return $this->notFoundAction();
        }

        $results = $this->makeRestCall('VosaCase', 'GET', array('licence' => $licence));

        $view = new ViewModel(['results' => $results]);
        $view->setTemplate('results');
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

        // If we don't have a licence id
        if (empty($licence)) {
            $this->redirect()->toUrl('/');
        }

        $form = $this->generateFormWithData(
            'case',
            'processAddCase',
            array(
                'licence' => $licence
            )
        );

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('form');
        return $view;
    }

    /**
     * Edit a new case
     *
     * @return ViewModel
     */
    public function editAction()
    {
        $case = $this->params()->fromRoute('case');

        $result = $this->makeRestCall('VosaCase', 'GET', array('id' => $case));

        $categories = $result['categories'];
        unset($result['categories']);

        $result['fields'] = $result;

        $result['categories'] = $this->unFormatCategories($categories);

        $form = $this->generateFormWithData(
            'case',
            'processEditCase',
            $result
        );

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('form');
        return $view;
    }

    /**
     * Process adding the case
     *
     * @todo Additional fields are required for persisting - Find out where these fields come from
     * @todo Decide where to send the user afterwards
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
            $this->redirect()->toUrl('/case/edit/' . $result['id']);
        }
    }

    /**
     * Process updating the case
     *
     * @todo Decide what to do on success
     *
     * @param type $data
     */
    protected function processEditCase($data)
    {
        $data['categories'] = $this->formatCategories($data['categories']);
        $data = array_merge($data, $data['fields']);

        $result = $this->processEdit($data, 'VosaCase');

        $this->redirect()->toUrl('/case/edit/' . $data['id']);
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

        foreach ($categories as $type => $array) {

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

        foreach ($categories as $categoryId) {

            if (!isset($formattedCategories[$translations[$categoryId]])) {
                $formattedCategories[$translations[$categoryId]] = array();
            }

            $formattedCategories[$translations[$categoryId]][] = 'case_category.' . $categoryId;
        }

        return $formattedCategories;
    }
}
