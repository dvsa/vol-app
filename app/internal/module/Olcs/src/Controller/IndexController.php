<?php

/**
 * IndexController
 *
 * @author Mike Cooper <michael.cooper@valtech.co.uk>
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller;

use Common\Controller\FormActionController;
use Zend\View\Model\ViewModel;

/**
 * IndexController
 *
 * @author Mike Cooper <michael.cooper@valtech.co.uk>
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class IndexController extends FormActionController
{
    protected $enableCsrf = false;

    /**
     * @codeCoverageIgnore
     */
    public function indexAction()
    {
        $filters = $this->filterRequest();

        $search = array_merge(
            $filters,
            array(
                'userId' => $this->getLoggedInUser()
            )
        );

        $tasks = $this->makeRestCall(
            'Task',
            'GET',
            $search
        );

        $table = $this->buildTable('tasks', $tasks);

        $form = $this->generateFormWithData(
            'tasks-home', null, $filters
        );

        $view = new ViewModel();
        $view->setVariables(
            array(
                'table' => $table,
                'form'  => $form
            )
        );

        $view->setTemplate('index/home');
        return $view;
    }

    /**
     * Inspect the request to see if we have any filters set, and
     * if necessary, filter them down to a valid subset
     *
     * @return array
     */
    protected function filterRequest()
    {
        return $this->getRequest()->getQuery()->toArray();
    }
}
