<?php

namespace Olcs\Controller\Ebsr;

use Common\Controller\Lva\AbstractController;
use Common\Rbac\User;
use Dvsa\Olcs\Transfer\Command\Bus\Ebsr\UpdateTxcInbox as UpdateTxcInboxDto;
use Dvsa\Olcs\Transfer\Query\Bus\Ebsr\EbsrSubmissionList;
use Dvsa\Olcs\Transfer\Query\Bus\Ebsr\TxcInboxList;
use Dvsa\Olcs\Transfer\Query\Bus\Ebsr\BusRegWithTxcInbox;
use Dvsa\Olcs\Transfer\Query\Bus\BusReg as ItemDto;
use Dvsa\Olcs\Transfer\Query\Bus\RegistrationHistoryList as BusRegVariationHistoryDto;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;
use Zend\Http\Response;

/**
 * Class BusRegApplicationsController
 */
class BusRegApplicationsController extends AbstractController
{
    /**
     * Lists all EBSR's with filter search form
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $postData = $request->getPost();

            if (isset($postData['action'], $postData['table'])) {
                //this is a mark as read request
                if ($postData['table'] === 'txc-inbox') {
                    return $this->processMarkAsRead($postData);
                }

                //this is a redirect to the EBSR upload page
                return $this->redirect()->toRoute('bus-registration/ebsr');
            }

            return $this->processSearch($postData);
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

        // handle response
        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addCurrentUnknownError();
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
        $tableBuilder = $this->getServiceLocator()->get('Table');

        $userData = $this->currentUser()->getUserData();

        if ($userData['userType'] === User::USER_TYPE_LOCAL_AUTHORITY) {
            $tableName = 'txc-inbox';
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
     * @return null|Response|\Zend\View\Model\ConsoleModel|ViewModel
     */
    private function processMarkAsRead($data)
    {
        $command = UpdateTxcInboxDto::create(
            [
                'ids' => $data['id'],
            ]
        );

        $response = $this->handleCommand($command);

        // handle response
        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        if ($response->isOk()) {
            $params['status'] = $this->params()->fromQuery('status');

            return $this->redirect()->toRoute(null, $params, [], false);
        }

        return null;
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
     * @return ViewModel
     */
    public function searchDetailsAction()
    {
        $id = $this->params()->fromRoute('busRegId');

        return $this->details(
            ItemDto::create(['id' => $id]),
            'layouts/entity-view',
            [
                'searchResultsLink' => $this->generateLinkBackToSearchResult(),
            ]
        );
    }

    /**
     * Bus registration details page
     *
     * @return ViewModel
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
                'backUrl' => $this->generateLinkBackToBusRegs(),
            ]
        );
    }

    /**
     * Prepare view model for Details action with specified parameters
     *
     * @param QueryInterface $query   the query
     * @param string         $temlate the template
     * @param array          $options array of options
     *
     * @return null|ViewModel
     */
    private function details(QueryInterface $query, $temlate, $options = [])
    {
        $response = $this->handleQuery($query);

        if (!$response instanceof Response || $response->isServerError() || $response->isClientError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addCurrentUnknownError();

            return null;
        }

        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        //  build view
        $result = $response->getResult();

        $licence = $result['licence'];
        $url = $this->url()->fromRoute(
            'entity-view', [
                'entity' => 'licence',
                'entityId' => $licence['id'],
            ]
        );

        $layout = new ViewModel(
            [
                'pageTitle' => $licence['organisation']['name'],
                'pageSubTitleUrl' => [
                    'url' => $url,
                    'label' => $licence['licNo'],
                ],
                'showNav' => false,
            ] + $options
        );

        $layout
            ->setTemplate($temlate)
            ->addChild($this->detailsContent($result), 'content');

        return $layout;
    }

    /**
     * Prepare content of Details view
     *
     * @param array $results array of results
     *
     * @return ViewModel
     */
    private function detailsContent(array $results)
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
                'variationHistoryTable' => $this->fetchVariationHistoryTable($results['id']),
            ]
        );
    }

    /**
     * Method to generate the Variation History table
     *
     * @param int $busRegId the bus reg id
     *
     * @return array|string
     */
    private function fetchVariationHistoryTable($busRegId)
    {
        /** @var \Common\Service\Table\TableBuilder $tableBuilder */
        $tableBuilder = $this->getServiceLocator()->get('Table');

        $query = BusRegVariationHistoryDto::create(
            [
                'id' => $busRegId,
                'sort' => 'variationNo',
                'order' => 'DESC'
            ]
        );

        $response = $this->handleQuery($query);

        // handle response
        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        if ($response->isOk()) {
            $result = $response->getResult();
            return $tableBuilder->buildTable(
                'bus-reg-variation-history',
                $result,
                ['url' => $this->plugin('url')],
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
     * Return back link to Bus registration page
     *
     * @return string
     */
    private function generateLinkBackToBusRegs()
    {
        return [
            'url' => $this->url()->fromRoute('busreg-registrations'),
            'label' => 'bus-registrations-index-title',
        ];
    }

    /**
     * generate a link back to the search results
     *
     * @return string
     */
    private function generateLinkBackToSearchResult()
    {
        $params = new Container('searchQuery');

        $queryParams = [];
        if (!empty($params->queryParams)) {
            $queryParams = ['query' => $params->queryParams];
        }

        return $this->url()->fromRoute('search', (array) $params->routeParams, $queryParams);
    }

    /**
     * Get and setup the filter form
     *
     * @param array  $params   array of parameters
     * @param string $formName name of the form
     *
     * @return \Zend\Form\FormInterface
     */
    private function getFilterForm($params, $formName)
    {
        /** @var \Zend\Form\FormInterface $filterForm */
        $filterForm = $this->getServiceLocator()->get('Helper\Form')->createForm($formName);

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
        if (in_array(
            $this->currentUser()->getUserData()['userType'],
            [
                User::USER_TYPE_LOCAL_AUTHORITY,
                User::USER_TYPE_OPERATOR,
                User::USER_TYPE_TRANSPORT_MANAGER
            ],
            true
        )) {
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
