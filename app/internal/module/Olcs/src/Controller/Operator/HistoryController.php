<?php

namespace Olcs\Controller\Operator;

use Dvsa\Olcs\Transfer\Query\EventHistory\EventHistory as ItemDto;
use Laminas\Form\FormInterface;
use Laminas\View\Model\ViewModel;
use Olcs\Data\Mapper\EventHistory as Mapper;
use Olcs\Form\Model\Form\EventHistory as EventHistorytForm;

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

    /**
     * indexAction
     *
     * @return array|ViewModel
     */
    public function indexAction()
    {
        $view = $this->getView();

        $view->setTemplate('pages/table');
        $view->setTerminal($this->getRequest()->isXmlHttpRequest());

        $response = $this->getListData();

        if ($response->isClientError() || $response->isServerError()) {
            $this->flashMessengerHelper->addErrorMessage('unknown-error');
            return $this->renderView($view);
        }

        if ($response->isOk()) {
            $tableName = 'event-history';

            $params = $this->getListParamsForTable();

            $data = $response->getResult();

            $table = $this->tableFactory->buildTable($tableName, $data, $params, false);
            $table->removeColumn('appId');

            $view->{'table'} = $table;
        }

        return $this->renderView($view);
    }

    /**
     * get method list data
     */
    public function getListData()
    {
        $params = $this->getListParams();

        $dto = new \Dvsa\Olcs\Transfer\Query\Processing\History();
        $dto->exchangeArray($params);

        $query = $this->transferAnnotationBuilder->createQuery($dto);

        return $this->queryService->send($query);
    }

    /**
     * get method List Params
     *
     * @return array
     */
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

    /**
     * get method  List Params for table
     *
     * @return array
     */
    public function getListParamsForTable()
    {
        $params = $this->getListParams();

        $params['query'] = $this->getRequest()->getQuery();

        return $params;
    }

    /**
     * Proxies to the get query or get param.
     *
     * @param string      $name    name
     * @param null|String $default default
     *
     * @return null|String
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

    /**
     * Edit action
     *
     * @return array
     */
    public function editAction()
    {
        $response = $this->handleQuery(ItemDto::create(['id' => $this->params('id')]));

        if (!$response->isOk()) {
            $this->flashMessengerHelper->addErrorMessage('Unknown error');
            return $this->redirect()->toRouteAjax('operator/processing/history', ['action' => 'index'], [], true);
        }
        $form = $this->getEventHistoryDetailsForm(Mapper::mapFromResult($response->getResult()));
        $this->placeholder()->setPlaceholder('form', $form);
        return $this->viewBuilder()->buildViewFromTemplate('sections/processing/pages/event-history-popup');
    }

    /**
     * Get event history details form
     *
     * @param array $data data
     *
     * @return FormInterface
     */
    protected function getEventHistoryDetailsForm($data)
    {
        $formHelper = $this->formHelper;
        $form = $formHelper->createForm(EventHistorytForm::class);
        $form->setData($data);

        $this->placeholder()->setPlaceholder('readOnlyData', $data['readOnlyData']);

        if (is_array($data['eventHistoryDetails']) && count($data['eventHistoryDetails'])) {
            $form->get('event-history-details')->get('table')->get('table')->setTable(
                $this->getDetailsTable($data['eventHistoryDetails'])
            );
        } else {
            $formHelper->remove($form, 'event-history-details->table');
        }

        return $form;
    }

    /**
     * Get event details table
     *
     * @param array $details details
     */
    protected function getDetailsTable($details)
    {
        return $this->tableFactory
            ->prepareTable('event-history-details', $details);
    }
}
