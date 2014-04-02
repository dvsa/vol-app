<?php

/**
 * @package    olcs
 * @subpackage
 * @author     Mike Cooper
 */

namespace Olcs\Controller;

use Common\Controller\FormActionController;
use Zend\View\Model\ViewModel;

class SearchController extends FormActionController
{

    public function indexAction()
    {
        // Below is for setting route params for the breadcrumb
        $this->setBreadcrumb(array('search' => array()));
        $navigation = $this->getServiceLocator()->get('navigation');

        $form = $this->generateFormWithData(
            'search',
            'processSearch'
        );

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('search/index');
        return $view;
    }

    /**
     * Process the search
     *
     * @param array $data
     */
    protected function processSearch($data)
    {
        $data = array_merge($data['search'], $data['advanced']);

        // Person Search Rules
        $personSearch = array(
            'firstName',
            'lastName',
            'dateOfBirth',
            'transportManagerId'
        );

        $searchType = 'operators';

        foreach ($data as $key => $value) {

            if (empty($value)) {
                unset($data[$key]);
            } elseif (in_array($key, $personSearch)) {
                $searchType = 'person';
            }
        }

        $this->redirect()->toUrl('/search/' . $searchType . '?' . http_build_query($data));
    }

    public function personAction()
    {
        $data = $this->params()->fromQuery();

        $results = $this->makeRestCall('PersonSearch', 'GET', $data);

        $view = new ViewModel(['results' => $results]);
        $view->setTemplate('results');
        return $view;
    }

    public function operatorAction()
    {
        $data = $this->params()->fromQuery();

        $results = $this->makeRestCall('OperatorSearch', 'GET', $data);

        $view = new ViewModel(['results' => $results]);
        $view->setTemplate('results-operator');
        return $view;
    }

    protected function redirectUser()
    {
        print 'Doing something like a redirect';
        $this->redirect()->toRoute('olcsHome');
    }

}
