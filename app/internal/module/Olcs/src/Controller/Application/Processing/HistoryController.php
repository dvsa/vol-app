<?php

/**
 * History Controller
 */
namespace Olcs\Controller\Application\Processing;

use Dvsa\Olcs\Transfer\Query\Application\History;
use Zend\View\Model\ViewModel;

/**
 * History Controller
 */
class HistoryController extends AbstractApplicationProcessingController
{
    /**
     * @var string
     */
    protected $section = 'history';

    public function indexAction()
    {
        $params = [
            'id'    => $this->getQueryOrRouteParam('application'),
            'page'  => $this->getQueryOrRouteParam('page', 1),
            'sort'  => $this->getQueryOrRouteParam('sort', 'createdOn'),
            'order' => $this->getQueryOrRouteParam('order', 'desc'),
            'limit' => $this->getQueryOrRouteParam('limit', 10),
        ];

        $response = $this->handleQuery(History::create($params));
        $results = $response->getResult();
//
//        print '<pre>';
//        print_r($results);
//        exit;

        $table = $this->getServiceLocator()->get('Table')->buildTable('event-history', $results, $params);

        $view = new ViewModel(['table' => $table]);
        $view->setTemplate('pages/table');

        return $this->renderView($view);
    }
}
