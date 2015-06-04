<?php

/**
 * History Controller
 */
namespace Olcs\Controller\Licence\Processing;

use Zend\View\Model\ViewModel;

/**
 * History Controller
 */
class HistoryController extends AbstractLicenceProcessingController
{
    /**
     * @var string
     */
    protected $section = 'history';

    public function indexAction()
    {
        $view = $this->getViewWithLicence();

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
    protected function getListData()
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
            'licence' => $this->getQueryOrRouteParam('licence'),
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
