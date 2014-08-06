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
        /*
        $results = $this->makeRestCall(
            'VosaCase', 'GET', array(
            'id' => $caseId, 'bundle' => json_encode($bundle))
        );
         */

        $data = array(
            'url' => $this->getPluginManager()->get('url')
        );

        $tasks = array(
            'Results' => array(
                array(
                    'id' => 1234,
                    'category' => 'Application',
                    'subCategory' => 'Address change assisted digital',
                    'description' => 'Address change',
                    'date' => '2014-04-5 09:00:00',
                    'owner' => 'Gillian Fox',
                    'name' => 'Don Tarmacadam'
                )
            ),
            'Count' => 1
        );

        $table = $this->buildTable('tasks', $tasks, $data);

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
