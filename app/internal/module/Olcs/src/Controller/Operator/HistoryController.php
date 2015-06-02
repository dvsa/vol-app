<?php

/**
 * History Controller
 */
namespace Olcs\Controller\Operator;

/**
 * History Controller
 */
class HistoryController extends OperatorController
{
    /**
     * @var string
     */
    protected $section = 'history';

    /**
     * @var string
     */
    protected $subNavRoute = 'operator_processing';

    /*public function indexAction()
    {
        $view = $this->getView();

        $params = [
            'organisation' => $this->getQueryOrRouteParam('organisation'),
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
    }*/

    public function indexAction()
    {
        $view = $this->getView();

        $view->setTemplate('partials/table');
        $view->setTerminal($this->getRequest()->isXmlHttpRequest());

        $response = $this->getListData();

        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {

            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            return $this->renderView($view);
        }

        if ($response->isOk()) {

            $tableName = 'event-history';

            $params = $this->getListParamsForTable();

            $data = $response->getResult();

            $view->{'table'} = $this->getServiceLocator()->get('Table')->buildTable($tableName, $data, $params, false);
        }

        return $this->renderView($view);
    }

    /**
     * @return Response
     */
    public function getListData()
    {
        $params = $this->getListParams();

        $dto = new \Dvsa\Olcs\Transfer\Query\Processing\History();
        $dto->exchangeArray($params);

        $query = $this->getServiceLocator()->get('TransferAnnotationBuilder')
            ->createQuery($dto);

        return $this->getServiceLocator()->get('QueryService')->send($query);
    }

    public function getListParams()
    {
        $params = [
            'organisation' => $this->getQueryOrRouteParam('organisation'),
            'page'    => $this->getQueryOrRouteParam('page', 1),
            'sort'    => $this->getQueryOrRouteParam('sort', 'eventDatetime'),
            'order'   => $this->getQueryOrRouteParam('order', 'DESC'),
            'limit'   => $this->getQueryOrRouteParam('limit', 10),
        ];

        return $params;
    }

    public function getListParamsForTable()
    {
        $params = $this->getListParams();

        $params['query'] = $this->getRequest()->getQuery();

        return $params;
    }

    /**
     * Proxies to the get query or get param.
     *
     * @param mixed $name
     * @param mixed $default
     * @return mixed
     */
    public function getQueryOrRouteParam($name, $default = null)
    {
        if ($queryValue = $this->params()->fromQuery($name, $default)) {
            return $queryValue;
        }

        if ($queryValue = $this->params()->fromRoute($name, $default)) {
            return $queryValue;
        }

        return $default;
    }
}
