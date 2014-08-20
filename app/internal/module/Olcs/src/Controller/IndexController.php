<?php

/**
 * IndexController
 *
 * @author Mike Cooper <michael.cooper@valtech.co.uk>
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller;

use Common\Controller\FormActionController;
use Olcs\Controller\AbstractController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Olcs\Controller\Traits\TaskSearchTrait;

/**
 * IndexController
 *
 * @author Mike Cooper <michael.cooper@valtech.co.uk>
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class IndexController extends AbstractController
{
    use TaskSearchTrait;

    const MAX_LIMIT = 100;

    protected $pageTitle = 'Home';
    protected $pageSubTitle = 'Subtitle';

    public function indexAction()
    {
        $filters = $this->mapTaskFilters();

        $view = new ViewModel(
            array(
                'table' => $this->getTaskTable($filters),
                'form'  => $this->getTaskForm($filters),
                'inlineScript' => $this->loadScripts(['tasks'])
            )
        );
        $view->setTemplate('index/home');
        $view->setTerminal($this->getRequest()->isXmlHttpRequest());

        return $this->renderView($view);
    }

    /**
     * Retrieve a list of entities, filtered by a certain key.
     * The consumer doesn't control what the entities and keys are; they
     * simply provide a key and a value which we look up in a map
     *
     * @return JSON
     */
    public function taskFilterAction()
    {
        $key = $this->params()->fromRoute('type');
        $value = $this->params()->fromRoute('value');
        $map = array(
            'users' => array(
                'entity' => 'User',
                'field' => 'team'
            ),
            'sub-categories' => array(
                'entity' => 'TaskSubCategory',
                'field' => 'category'
            )
        );

        if (!isset($map[$key])) {
            // @TODO handle separately?
            throw new \Exception("Invalid task filter key: " . $key);
        }

        $lookup = $map[$key];

        // e.g. array("category_id" => 12)
        $search = array(
            $lookup['field'] => $value
        );

        $results = $this->getListData($lookup['entity'], $search);
        $viewResults = array();

        // iterate over the list data and just convert it to a more
        // JS friendly format (key/val assoc isn't quite such a neat
        // fit for frontend)
        foreach ($results as $id => $result) {
            $viewResults[] = array(
                'value' => $id,
                'label' => $result
            );
        }

        return new JsonModel($viewResults);
    }
}
