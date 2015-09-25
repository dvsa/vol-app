<?php

/**
 * History Controller
 */
namespace Olcs\Controller\Application\Processing;

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
        $view = $this->getViewWithApplication();

        $params = [
            'application' => $this->getQueryOrRouteParam('application'),
            'page'    => $this->getQueryOrRouteParam('page', 1),
            'sort'    => $this->getQueryOrRouteParam('sort', 'id'),
            'order'   => $this->getQueryOrRouteParam('order', 'desc'),
            'limit'   => $this->getQueryOrRouteParam('limit', 10),
        ];

        $params['query'] = $this->getRequest()->getQuery();

        /**
         * @todo need to migrate this
         */
        $bundle = [
            'children' => [
                'eventHistoryType' => [],
                'user' => [
                    'children' => [
                        'contactDetails' => [
                            'children' => [
                                'person' => [],
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $results = $this->makeRestCall('EventHistory', 'GET', $params, $bundle);

        $view->table = $this->getTable('event-history', $results, $params);

        $view->setTemplate('pages/table');

        return $this->renderView($view);
    }
}
