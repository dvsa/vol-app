<?php

namespace Olcs\Controller\BusReg;

use Common\Controller\Lva\AbstractController;
use Common\Service\Cqrs\Exception\NotFoundException;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Query\Bus\BusRegBrowseExport;
use Dvsa\Olcs\Transfer\Query\Bus\BusRegBrowseList;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Session\Container;
use Laminas\View\Model\ViewModel;
use Olcs\Form\Model\Form\BusRegBrowseForm as Form;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Class BusRegBrowseController
 */
class BusRegBrowseController extends AbstractController
{
    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FormHelperService $formHelper
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param TableFactory $tableFactory
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        protected FormHelperService $formHelper,
        protected FlashMessengerHelperService $flashMessengerHelper,
        protected TableFactory $tableFactory
    ) {
        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * Index action
     *
     * @return \Laminas\Http\Response|ViewModel
     */
    #[\Override]
    public function indexAction()
    {
        /** @var \Laminas\Form\Form $form */
        $form = $this->formHelper->createForm(Form::class);

        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $form->setData($this->getIncomingData());

            if ($form->isValid()) {
                $formData = $form->getData();

                if ($this->isButtonPressed('export')) {
                    // export data
                    $response = $this->handleExport($formData['fields']);

                    if ($response !== null) {
                        return $response;
                    }
                } else {
                    // browse data
                    return $this->redirect()->toRoute(
                        'search-bus/browse/results',
                        [],
                        ['query' => $formData, 'code' => 303]
                    );
                }
            }
        }

        $view = new ViewModel(['searchForm' => $form]);
        $view->setTemplate('search/index-bus-browse.phtml');

        return $view;
    }

    /**
     * Handle export
     *
     * @param array $criteria Criteria
     *
     * @return \Laminas\Http\Response|null
     */
    private function handleExport($criteria)
    {
        try {
            $response = $this->handleQuery(
                BusRegBrowseExport::create(
                    [
                        'trafficAreas' => $criteria['trafficAreas'],
                        'status' => $criteria['status'],
                        'acceptedDate' => $criteria['acceptedDate'],
                    ]
                )
            );
            if ($response->isOk()) {
                // return HTTP response from the api
                $httpResponse = $response->getHttpResponse();

                // but make sure we only return allowed headers
                $headers = new \Laminas\Http\Headers();
                $allowedHeaders = ['Content-Disposition', 'Content-Encoding', 'Content-Type', 'Content-Length'];

                foreach ($httpResponse->getHeaders() as $header) {
                    if (in_array($header->getFieldName(), $allowedHeaders)) {
                        $headers->addHeader($header);
                    }
                }
                $httpResponse->setHeaders($headers);

                return $httpResponse;
            }

            $this->flashMessengerHelper->addCurrentUnknownError();
        } catch (NotFoundException) {
            // no results found
            $this->flashMessengerHelper
                ->addErrorMessage('selfserve.search.busreg.browse.no-results');
        }

        return null;
    }

    /**
     * Results action
     *
     * @return ViewModel
     */
    public function resultsAction()
    {
        $view = new ViewModel(
            [
                'filterForm' => $this->initialiseFilterForm(),
                'backRoute' => 'search-bus/browse',
                'results' => $this->handleBrowse($this->getIncomingData()['fields'])
            ]
        );
        $view->setTemplate('search/bus-browse-results.phtml');

        return $view;
    }

    /**
     * Handle browse
     *
     * @param array $criteria Criteria
     *
     * @return string
     */
    private function handleBrowse($criteria)
    {
        $params = $this->params();

        $pagination = [
            'page' => $params->fromQuery('page', 1),
            'sort' => $params->fromQuery('sort', 'id'),
            'order' => $params->fromQuery('order', 'DESC'),
            'limit' => $params->fromQuery('limit', 10),
            'query' => $params->fromQuery(),
        ];

        $response = $this->handleQuery(
            BusRegBrowseList::create(
                [
                    'trafficAreas' => $criteria['trafficAreas'],
                    'status' => $criteria['status'],
                    'acceptedDate' => $criteria['acceptedDate'],
                ] + $pagination
            )
        );

        if ($response->isOk()) {
            $result = $response->getResult();
        } else {
            $this->flashMessengerHelper->addCurrentUnknownError();
            $result = [];
        }

        /** @var \Common\Service\Table\TableBuilder $tableBuilder */
        $tableBuilder = $this->tableFactory;

        $busRegistrationTable = $tableBuilder->buildTable(
            'bus-reg-browse',
            $result,
            $pagination,
            false
        );

        return $busRegistrationTable;
    }

    /**
     * Initialise the filter form
     *
     * @return \Common\Form\Form
     */
    private function initialiseFilterForm()
    {
        /** @var \Common\Form\Form $form */
        $form = $this->formHelper->createForm(Form::class);

        // make sure we always submit to the browse action
        $form->setAttribute('action', $this->url()->fromRoute('search-bus/browse'));

        // update action buttons
        $form->get('form-actions')->get('submit')->setLabel('search.form.filter.update_button');

        // populate values
        $form->populateValues($this->getIncomingData());

        return $form;
    }

    /**
     * Get incoming data
     *
     * @return array
     */
    private function getIncomingData()
    {
        $params = $this->params();

        // POST data should overwrite GET values
        $incomingParameters = array_merge(
            (array)$params->fromQuery(),
            (array)$params->fromPost()
        );

        $this->storeSearchUrl($params->fromRoute(), $incomingParameters);

        return $incomingParameters;
    }

    /**
     * Store search params in the session to generate 'Back to search results' links
     * Taken from route params and query params stored in the session
     *
     * @param array $routeParams Route params
     * @param array $queryParams Query params
     *
     * @return void
     */
    private function storeSearchUrl($routeParams, $queryParams)
    {
        $sessionSearch = new Container('searchQuery');

        $sessionSearch->route = 'search-bus/browse/results';
        $sessionSearch->routeParams = $routeParams;
        $sessionSearch->queryParams = $queryParams;
    }
}
