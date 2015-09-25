<?php

/**
 * Index Controller
 *
 * @author Mike Cooper <michael.cooper@valtech.co.uk>
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller;

use Olcs\Controller\Interfaces\LeftViewProvider;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Olcs\Controller\Traits\TaskSearchTrait;

/**
 * Index Controller
 *
 * @NOTE Migrated (Not converted to a "new" internal controller)
 *
 * @author Mike Cooper <michael.cooper@valtech.co.uk>
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class IndexController extends AbstractController implements LeftViewProvider
{
    use TaskSearchTrait;

    const MAX_LIMIT = 100;

    protected $entityListMap = [
        'users' => [
            'entity' => 'User',
            'field' => 'team',
            'title' => 'loginId'
        ],
        'task-sub-categories' => [
            'entity' => 'SubCategory',
            'field' => 'category',
            'title' => 'subCategoryName',
            'search' => [
                'isTask' => true
            ]
        ],
        'document-sub-categories' => [
            'entity' => 'SubCategory',
            'field' => 'category',
            'title' => 'subCategoryName',
            'search' => [
                'isDoc' => true
            ]
        ],
        'scanning-sub-categories' => [
            'entity' => 'SubCategory',
            'field' => 'category',
            'title' => 'subCategoryName',
            'search' => [
                'isScan' => true
            ]
        ],
        'document-templates' => [
            'entity' => 'DocTemplate',
            'field' => 'subCategory',
            'title' => 'description'
        ],
        'sub-category-descriptions' => [
            'entity' => 'SubCategoryDescription',
            'field' => 'subCategory',
            'title' => 'description'
        ]
    ];

    public function indexAction()
    {
        $redirect = $this->processTasksActions();

        if ($redirect) {
            return $redirect;
        }

        $filters = $this->mapTaskFilters();

        $this->loadScripts(['tasks', 'table-actions', 'forms/filter']);

        $view = new ViewModel(['table' => $this->getTaskTable($filters, true)]);
        $view->setTemplate('pages/table');

        return $this->renderView($view, 'Home');
    }

    public function getLeftView()
    {
        $filters = $this->mapTaskFilters();

        $left = new ViewModel(['form' => $this->getTaskForm($filters)]);
        $left->setTemplate('sections/home/partials/left');

        return $left;
    }

    /**
     * Retrieve a list of entities, filtered by a certain key.
     * The consumer doesn't control what the entities and keys are; they
     * simply provide a key and a value which we look up in a map
     *
     * @return JsonModel
     */
    public function entityListAction()
    {
        $key = $this->params('type');
        $value = $this->params('value');

        if (!isset($this->entityListMap[$key])) {
            // handle separately?
            throw new \Exception('Invalid entity filter key: ' . $key);
        }

        $lookup = $this->entityListMap[$key];

        // e.g. array("category_id" => 12)
        $search = [$lookup['field'] => $value];

        if (isset($lookup['search'])) {
            $search = array_merge($search, $lookup['search']);
        }

        $titleKey = isset($lookup['title']) ? $lookup['title'] : 'name';

        $results = $this->getListDataFromBackend($lookup['entity'], $search, $titleKey);
        $viewResults = [];

        // iterate over the list data and just convert it to a more
        // JS friendly format (key/val assoc isn't quite such a neat
        // fit for frontend)
        foreach ($results as $id => $result) {
            $viewResults[] = [
                'value' => $id,
                'label' => $result
            ];
        }

        return new JsonModel($viewResults);
    }
}
