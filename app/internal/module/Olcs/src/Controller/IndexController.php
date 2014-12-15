<?php

/**
 * IndexController
 *
 * @author Mike Cooper <michael.cooper@valtech.co.uk>
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller;

use Olcs\Controller\AbstractController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Olcs\Controller\Traits\TaskSearchTrait;

/**
 * IndexController
 *
 * @author Mike Cooper <michael.cooper@valtech.co.uk>
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class IndexController extends AbstractController
{
    use TaskSearchTrait;

    const MAX_LIMIT = 100;

    protected $pageTitle = 'Home';
    protected $pageSubTitle = 'Subtitle';

    public function indexAction()
    {
        $redirect = $this->processTasksActions();
        if ($redirect) {
            return $redirect;
        }

        $filters = $this->mapTaskFilters();

        $this->loadScripts(['tasks', 'table-actions']);

        $view = new ViewModel(
            array(
                'table' => $this->getTaskTable($filters, true, true),
                'form'  => $this->getTaskForm($filters),
            )
        );
        $view->setTemplate('pages/index');
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
    public function entityListAction()
    {
        $key = $this->params()->fromRoute('type');
        $value = $this->params()->fromRoute('value');
        $map = array(
            'users' => array(
                'entity' => 'User',
                'field' => 'team'
            ),
            'task-sub-categories' => array(
                'entity' => 'TaskSubCategory',
                'field' => 'category'
            ),
            'document-sub-categories' => array(
                'entity' => 'DocumentSubCategory',
                'field' => 'category',
                'title' => 'description'
            ),
            'document-templates' => array(
                'entity' => 'DocTemplate',
                'field' => 'documentSubCategory',
                'title' => 'description'
            )
        );

        if (!isset($map[$key])) {
            // handle separately?
            throw new \Exception("Invalid entity filter key: " . $key);
        }

        $lookup = $map[$key];

        // e.g. array("category_id" => 12)
        $search = array(
            $lookup['field'] => $value
        );

        $titleKey = isset($lookup['title']) ? $lookup['title'] : 'name';

        $results = $this->getListDataFromBackend($lookup['entity'], $search, $titleKey);
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
