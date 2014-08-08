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

    /**
     * @codeCoverageIgnore
     */
    public function indexAction()
    {
        $tasks = $this->makeRestCall(
            'Task',
            'GET',
            array(
                'userId' => 123
            )
        );

        $table = $this->buildTable('tasks', $tasks);

        $data = [];

        $form = $this->generateFormWithData(
            'tasks-home', 'processTaskFilters', $data
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
}
