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
     * Process adding the case
     *
     * @param type $data
     */
    protected function processAddCase($data)
    {
        $data['categories'] = $this->formatCategories($data['categories']);
        $data = array_merge($data, $data['fields']);

        $this->processAdd($data, 'VosaCase');
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

}
