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
     *
     * @todo Handle 404
     */
    public function indexAction()
    {
        $licence = $this->params()->fromRoute('licence');

        if (empty($licence)) {

            return $this->notFoundAction();
        }

        $results = $this->makeRestCall('VosaCase', 'GET', array('licence' => $licence));

        $settings = array(
            'columns' => array(
                'id' => array(
                    'Title' => 'ID',
                    'Format' => '<a href="/case/' . $licence . '/edit/:VALUE">:VALUE</a>'
                ),
                'caseNumber' => array(
                    'Title' => 'Case Number'
                ),
                'status' => array(
                    'Title' => 'Status'
                ),
                'description' => array(
                    'Title' => 'Description'
                ),
                'ecms' => array(
                    'Title' => 'ECMS'
                )
            )
        );

        $view = new ViewModel(['licence' => $licence, 'count' => $results['Count'], 'table' => $this->buildTable($results['Results'], $settings)]);
        $view->setTemplate('case-list');
        return $view;
    }

    private function buildTable($data, $settings = array())
    {
        if (empty($data)) {
            return '<p>No results</p>';
        }

        $firstRow = $data[0];

        $headers = array();

        foreach ($settings['columns'] as $key => $details) {
            $headers[$key] = $details['Title'];
        }

        $rows = array();

        foreach ($data as $row) {

            $columns = array();

            foreach ($row as $column => $value) {

                if (isset($headers[$column])) {

                    if (is_string($value) || is_numeric($value)) {
                        $val = $value;
                    } else {
                        $val = '';
                    }

                    if (isset($settings['columns'][$column]['Format'])) {

                        $columns[] = str_replace(':VALUE', $value, $settings['columns'][$column]['Format']);
                    } else {
                        $columns[] = $val;
                    }
                }
            }

            $rows[] = '<td>' . implode('</td><td>', $columns) . '</td>';
        }

        $table = '
<table>
    <thead>
        <tr>
            <th>' . implode('</th><th>', $headers) . '</th>
        </tr>
    </thead>
    <tbody><tr>' . implode('</tr><tr>', $rows) . '</tr>
    </tbody>
</table>';

        return $table;
    }

    /**
     * Add a new case to a licence
     *
     * @todo Handle 404 and Bad Request
     *
     * @return ViewModel
     */
    public function addAction()
    {
        $licence = $this->params()->fromRoute('licence');

        if (empty($licence)) {
            die('Bad request');
        }

        $results = $this->makeRestCall('Licence', 'GET', array('id' => $licence));

        if (empty($results)) {
            return $this->notFoundAction();
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
     * @todo Handle 404
     *
     * @return ViewModel
     */
    public function editAction()
    {
        $licence = $this->params()->fromRoute('licence');
        $case = $this->params()->fromRoute('case');

        $result = $this->makeRestCall('VosaCase', 'GET', array('id' => $case, 'licence' => $licence));

        if (empty($result)) {
            return $this->notFoundAction();
        }

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
            $this->redirect()->toUrl('/case/' . $data['licence']);
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

        $this->redirect()->toUrl('/case/' . $data['licence']);
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
