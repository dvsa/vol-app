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
     * @todo Decide where to send people if they don't have an action
     */
    public function indexAction()
    {
        $this->redirect()->toUrl('/');
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

        $result['categories'] = $this->unFormatCategories($result['categories']);

        $result['fields'] = $result;

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
     * @param type $data
     */
    protected function processAddCase($data)
    {
        // @todo Find out where these fields come from
        $data['caseNumber'] = 12345678;
        $data['openTime'] = '2014-01-01 12:00:00';
        $data['owner'] = 7;

        $data['categories'] = $this->formatCategories($data['categories']);
        $data = array_merge($data, $data['fields']);

        $result = $this->processAdd($data, 'VosaCase');

        if (isset($result['id'])) {
            // @todo Decide where to send the user afterwards
            $this->redirect()->toUrl('/case/add/' . $data['licence'] . '?created=' . $result['id']);
        }
    }

    /**
     * Process updating the case
     *
     * @param type $data
     */
    protected function processEditCase($data)
    {
        /*$data['categories'] = $this->formatCategories($data['categories']);
        $data = array_merge($data, $data['fields']);

        $result = $this->processEdit($data, 'VosaCase');

        var_dump($result);*/
    }

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

    private function unFormatCategories($categories = array())
    {
        $return = array();

        foreach ($categories as $category) {

            $return['compliance'] = 'case_category.' . $category;
        }

        return $return;
    }

}
