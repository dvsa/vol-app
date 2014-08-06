<?php

/**
 * IndexController
 *
 * @author Mike Cooper <michael.cooper@valtech.co.uk>
 */
namespace Olcs\Controller;

use Common\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * IndexController
 *
 * @author Mike Cooper <michael.cooper@valtech.co.uk>
 */
class IndexController extends AbstractActionController
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

        $view = new ViewModel();
        $view->setVariables(
            array(
                'table' => $table
            )
        );

        $view->setTemplate('index/home');
        return $view;
    }
}
