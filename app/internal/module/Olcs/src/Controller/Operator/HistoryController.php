<?php

/**
 * History Controller
 */
namespace Olcs\Controller\Operator;

use Zend\View\Model\ViewModel;

/**
 * History Controller
 */
class HistoryController extends OperatorController
{
    /**
     * @var string
     */
    protected $section = 'history';

    public function indexAction()
    {
        $view = $this->getViewWithOrganisation();

        $params = [
            'organisation' => $this->getQueryOrRouteParam('operator'),
            'page'    => $this->getQueryOrRouteParam('page', 1),
            'sort'    => $this->getQueryOrRouteParam('sort', 'id'),
            'order'   => $this->getQueryOrRouteParam('order', 'desc'),
            'limit'   => $this->getQueryOrRouteParam('limit', 10),
        ];

        $params['query'] = $this->getRequest()->getQuery();

        $bundle = array(
            'children' => array(
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
            )
        );

        $results = $this->makeRestCall('EventHistory', 'GET', $params, $bundle);

        $view->{'table'} = $this->getTable('event-history', $results, $params);

        $view->setTemplate('partials/table');
        $view->setTerminal($this->getRequest()->isXmlHttpRequest());

        return $this->renderView($view);
    }
}
