<?php

namespace Olcs\Controller\Ebsr;

use Common\Controller\Lva\AbstractController;
use Common\Rbac\User;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\Bus\Ebsr\UpdateTxcInbox as UpdateTxcInboxDto;
use Dvsa\Olcs\Transfer\Query\Bus\BusReg as ItemDto;
use Dvsa\Olcs\Transfer\Query\Bus\Ebsr\BusRegWithTxcInbox;
use Dvsa\Olcs\Transfer\Query\Bus\Ebsr\EbsrSubmissionList;
use Dvsa\Olcs\Transfer\Query\Bus\Ebsr\TxcInboxList;
use Dvsa\Olcs\Transfer\Query\Bus\RegistrationHistoryList as BusRegVariationHistoryDto;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Http\Response;
use Laminas\Session\Container;
use Laminas\View\Model\ViewModel;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Class BusRegApplicationsController
 */
class BusRegApplicationsController extends AbstractController
{
    public const TABLE_TXC_INBOX = 'txc-inbox';

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param TableFactory $tableFactory
     * @param FormHelperService $formHelper
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        protected FlashMessengerHelperService $flashMessengerHelper,
        protected TableFactory $tableFactory,
        protected FormHelperService $formHelper
    ) {
        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * On bus registration page we use this to handle the posted data
     *
     * @param array $postData Post data
     *
     * @return null|Response
     */
    private function busRegPostedActionHandler(array $postData)
    {
        if (isset($postData['action'], $postData['table'])) {
            if ($postData['table'] !== self::TABLE_TXC_INBOX) {
                //this is a redirect to the EBSR upload page
                return $this->redirect()->toRoute('bus-registration/ebsr');
            }

            //this is a mark as read request
            if (isset($postData['id'])) {
                return $this->processMarkAsRead($postData);
            }

            $this->flashMessengerHelper->addErrorMessage('select-at-least-one-row');
        }

        return $this->processSearch($postData);
    }

    /**
     * Lists all EBSR's with filter search form
     *
     * @return Response|ViewModel|null
     */
    public function indexAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $postData = $request->getPost()->toArray();
            return $this->busRegPostedActionHandler($postData);
        }

        $userData = $this->currentUser()->getUserData();

        $params = [
            'status'    => $this->params()->fromQuery('status'),
            'page'      => $this->params()->fromQuery('page', 1),
            'order'     => $this->params()->fromQuery('order', 'DESC'),
            'limit'     => $this->params()->fromQuery('limit', 25),
        ];

        if ($userData['userType'] === User::USER_TYPE_LOCAL_AUTHORITY) {
            $params['sort'] = $this->params()->fromQuery('sort', 'createdOn');
            $query = TxcInboxList::create($params);
            $formName = 'BusRegApplicationsFilterForm';
        } else {
            $params['sort'] = $this->params()->fromQuery('sort', 'submittedDate');
            $query = EbsrSubmissionList::create($params);
            $formName = 'BusRegApplicationsOperatorFilterForm';
        }

        // set query params for pagination
        $params['query'] = $params;

        $response = $this->handleQuery($query);

        if ($response->isClientError() || $response->isServerError()) {
            $this->flashMessengerHelper->addCurrentUnknownError();
        }

        $busRegistrationTable = '';
        if ($response->isOk()) {
            $result = $response->getResult();
            $busRegistrationTable = $this->generateTable($result, $params);
        }

        $filterForm = $this->getFilterForm($params, $formName);

        // setup layout and view
        $layout = $this->generateLayout(
            [
                'pageTitle' => 'bus-registrations-index-title',
                'searchForm' => $filterForm,
                'showNav' => false,
                'tabs' => $this->generateTabs()
            ]
        );

        $content = $this->generateView(
            'olcs/bus-registration/index',
            [
                'busRegistrationTable' => $busRegistrationTable,
            ]
        );

        $layout->addChild($content, 'content');

        return $layout;
    }

    /**
     * Generates one of two tables depending on user logged in.
     * LAs get the txc-inbox table to match the results returned. Operators get the ebsr-submissions table.
     *
     * @param array $result array of results
     * @param array $params array of params
     *
     * @return string
     */
    private function generateTable($result, $params)
    {
        /** @var \Common\Service\Table\TableBuilder $tableBuilder */
        $tableBuilder = $this->tableFactory;

        $userData = $this->currentUser()->getUserData();

        if ($userData['userType'] === User::USER_TYPE_LOCAL_AUTHORITY) {
            $tableName = self::TABLE_TXC_INBOX;
        } else {
            $tableName = 'ebsr-submissions';
        }

        $busRegistrationTable = $tableBuilder->buildTable(
            $tableName,
            ['Results' => $result['results'], 'Count' => $result['count']],
            $params,
            false
        );

        return $busRegistrationTable;
    }
    /**
     * Process those marked in table as read
     *
     * @param array $data data array
     *
     * @return array|Response
     */
    private function processMarkAsRead($data)
    {
        $command = UpdateTxcInboxDto::create(
            [
                'ids' => $data['id'],
            ]
        );

        $response = $this->handleCommand($command);

        if ($response->isClientError() || $response->isServerError()) {
            $this->flashMessengerHelper->addCurrentUnknownError();
        }

        $params['status'] = $this->params()->fromQuery('status');

        return $this->redirect()->toRoute(null, [], ['query' => $params], false);
    }

    /**
     * Process the search, simply sets up the GET params and redirects
     *
     * @param array $data data array
     *
     * @return Response
     */
    private function processSearch($data)
    {
        $params = $this->params()->fromQuery();
        $params['status'] = empty($data['fields']['status']) ? null : $data['fields']['status'];

        // initialise search results to page 1
        $params['page'] = 1;

        return $this->redirect()->toRoute(null, [], ['query' => $params], true);
    }

    /**
     * Search result :: Bus registration details page
     *
     * @return ViewModel|array|null
     */
    public function searchDetailsAction()
    {
        $id = $this->params()->fromRoute('busRegId');

        return $this->details(
            ItemDto::create(['id' => $id]),
            'layouts/entity-view',
            [
                'urlBackToSearch' => $this->getUrlBackToSearchResult(),
            ],
            true
        );
    }

    /**
     * Bus registration details page
     *
     * @return Response|ViewModel|array|null
     */
    public function detailsAction()
    {
        $id = $this->params()->fromRoute('busRegId');

        $identity = $this->currentUser()->getIdentity();

        if ($identity === null || $identity->isAnonymous()) {
            // redir to the public version of the bus reg page
            return $this->redirect()->toRoute('search-bus/details', ['busRegId' => $id], ['code' => 303]);
        }

        return $this->details(
            BusRegWithTxcInbox::create(['id' => $id]),
            'olcs/bus-registration/detail',
            [
                'backLink' => [
                    'url' => $this->url()->fromRoute('busreg-registrations'),
                ],
            ]
        );
    }

    /**
     * Prepare view model for Details action with specified parameters
     *
     * @param QueryInterface $query        the query
     * @param string         $temlate      the template
     * @param array          $options      array of options
     * @param bool           $isSearchPage Is this for the search version of the page
     *
     * @return array|null|ViewModel
     */
    private function details(QueryInterface $query, $temlate, $options = [], $isSearchPage = false)
    {
        $response = $this->handleQuery($query);

        if (!$response instanceof \Common\Service\Cqrs\Response) {
            return $this->notFoundAction();
        }

        if (!$response->isOk()) {
            $this->flashMessengerHelper->addCurrentUnknownError();
            return null;
        }

        //  build view
        $result = $response->getResult();

        $layout = new ViewModel(
            [
                'pageTitle' => 'search.bus-reg.details.title',
                'pageSubTitle' => $result['regNo'],
            ] + $options
        );

        $layout
            ->setTemplate($temlate)
            ->addChild($this->detailsContent($result, $isSearchPage), 'content');

        return $layout;
    }

    /**
     * Prepare content of Details view
     *
     * @param array $results      array of results
     * @param bool  $isSearchPage Is this for the search version of the page
     *
     * @return ViewModel
     */
    private function detailsContent(array $results, $isSearchPage)
    {
        $documents = [];

        $txcInboxs = (!empty($results['txcInboxs']) ? reset($results['txcInboxs']) : []);

        foreach (['pdfDocument', 'routeDocument', 'zipDocument'] as $doc) {
            if (empty($txcInboxs[$doc])) {
                continue;
            }
            $documents[] = $txcInboxs[$doc];
        }

        // setup layout and view
        return $this->generateView(
            'olcs/bus-registration/partial/details-content',
            [
                'registrationDetails' => $results,
                'documents' => $documents,
                'variationHistoryTable' => $this->fetchVariationHistoryTable($results['id'], $isSearchPage),
            ]
        );
    }

    /**
     * Method to generate the Variation History table
     *
     * @param int  $busRegId     the bus reg id
     * @param bool $isSearchPage Is this for the search version of the page
     *
     * @return null|string
     */
    private function fetchVariationHistoryTable($busRegId, $isSearchPage): ?string
    {
        /** @var \Common\Service\Table\TableBuilder $tableBuilder */
        $tableBuilder = $this->tableFactory;

        $query = BusRegVariationHistoryDto::create(
            [
                'id' => $busRegId,
                'sort' => 'variationNo',
                'order' => 'DESC'
            ]
        );

        $response = $this->handleQuery($query);

        if ($response->isClientError() || $response->isServerError()) {
            $this->flashMessengerHelper->addErrorMessage('unknown-error');
        }

        if ($response->isOk()) {
            $result = $response->getResult();
            return $tableBuilder->buildTable(
                'bus-reg-variation-history',
                $result,
                ['url' => $this->plugin('url'), 'isSearchPage' => $isSearchPage],
                false
            );
        }

        return null;
    }

    /**
     * Set up the layout with title, subtitle and content
     *
     * @param array $data array of view variables
     *
     * @return ViewModel
     */
    private function generateLayout($data = [])
    {
        $layout = new ViewModel($data);
        $layout->setTemplate('layouts/search');

        return $layout;
    }

    /**
     * Generate page content
     *
     * @param string $template the template
     * @param array  $data     array of variables
     *
     * @return ViewModel
     */
    private function generateView($template, $data = [])
    {
        $content = new ViewModel($data);
        $content->setTemplate($template);

        return $content;
    }

    /**
     * generate a link back to the search results
     *
     * @return string
     */
    private function getUrlBackToSearchResult()
    {
        $params = new Container('searchQuery');

        $queryParams = [];
        if (!empty($params->queryParams)) {
            $queryParams = ['query' => $params->queryParams];
        }

        return $this->url()->fromRoute(
            !empty($params->route) ? $params->route : 'search',
            (array)$params->routeParams,
            $queryParams
        );
    }

    /**
     * Get and setup the filter form
     *
     * @param array  $params   array of parameters
     * @param string $formName name of the form
     *
     * @return \Laminas\Form\FormInterface
     */
    private function getFilterForm($params, $formName)
    {
        /** @var \Laminas\Form\FormInterface $filterForm */
        $filterForm = $this->formHelper->createForm($formName);

        $filterForm->setData(
            [
                'fields' => [
                    'status' => $params['status']
                ]
            ]
        );

        return $filterForm;
    }

    /**
     * Privagte method to generate the tabs config array. Only operators and LAs can see the tabs. This *should* never
     * be executed by any other user type because of RBAC.
     *
     * @return array
     */
    private function generateTabs()
    {
        if (
            in_array(
                $this->currentUser()->getUserData()['userType'],
                [
                User::USER_TYPE_LOCAL_AUTHORITY,
                User::USER_TYPE_OPERATOR,
                User::USER_TYPE_TRANSPORT_MANAGER
                ],
                true
            )
        ) {
            return [
                0 => [
                    'label' => 'busreg-tab-title-registrations',
                    'route' => 'busreg-registrations'
                ],
                1 => [
                    'label' => 'busreg-tab-title-applications',
                    'route' => 'bus-registration',
                    'active' => true
                ]
            ];
        }
        return [];
    }
}
