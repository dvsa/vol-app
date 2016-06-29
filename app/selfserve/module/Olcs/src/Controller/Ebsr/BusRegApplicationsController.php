<?php

namespace Olcs\Controller\Ebsr;

use Common\Controller\Lva\AbstractController;
use Common\Rbac\User;
use Common\RefData;
use Dvsa\Olcs\Transfer\Command\Bus\Ebsr\UpdateTxcInbox as UpdateTxcInboxDto;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Query\Bus\Ebsr\EbsrSubmissionList;
use Dvsa\Olcs\Transfer\Query\Bus\Ebsr\TxcInboxList;
use Dvsa\Olcs\Transfer\Query\Bus\RegistrationHistoryList as BusRegVariationHistoryDto;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;

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
            'subType'   => $this->params()->fromQuery('subType'),
            'status'    => $this->params()->fromQuery('status'),
            'page'      => $this->params()->fromQuery('page', 1),
            'order'     => $this->params()->fromQuery('order', 'DESC'),
            'limit'     => $this->params()->fromQuery('limit', 25),
        ];

        if ($userData['userType'] === User::USER_TYPE_LOCAL_AUTHORITY) {
            $params['sort'] = $this->params()->fromQuery('sort', 'createdOn');
            $query = TxcInboxList::create($params);
        } else {
            $params['sort'] = $this->params()->fromQuery('sort', 'submittedDate');
            $query = EbsrSubmissionList::create($params);
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

        $filterForm = $this->getFilterForm($params);

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
     * @param $result
     * @param $params
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
     * @param $data
     * @return array
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
            $params['subType'] = $this->params()->fromQuery('subType');
            $params['status'] = $this->params()->fromQuery('status');

            return $this->redirect()->toRoute(null, $params, [], false);
        }

        return null;
    }

    /**
     * Process the search, simply sets up the GET params and redirects
     *
     * @param $data
     */
    private function processSearch($data)
    {
        $params = $this->params()->fromQuery();

        $params['subType'] = empty($data['fields']['subType']) ? null : $data['fields']['subType'];
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
        return $this->details(
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
        return $this->details(
            'olcs/bus-registration/detail',
            [
                'backUrl' => $this->generateLinkBackToBusRegs(),
            ]
        );
    }

    /**
     * Prepare view model for Details action with specified parameters
     *
     * @param string $temlate
     * @param array $options
     *
     * @return null|ViewModel
     */
    private function details($temlate, $options = [])
    {
        $id = $this->params()->fromRoute('busRegId');

        //  request data from Api
        $query = Query\Bus\Ebsr\BusRegWithTxcInbox::create(['id' => $id]);

        $response = $this->handleQuery($query);

        if ($response->isNotFound()) {
            return $this->notFoundAction();

        } else {
            if (!$response->isOk()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addCurrentUnknownError();
                return null;
            }
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
     * @param array $results
     *
     * @return ViewModel
     */
    private function detailsContent(array $results)
    {
        $documents = [];

        if ($this->isGranted(RefData::PERMISSION_SELFSERVE_EBSR_DOCUMENTS)) {
            $txcInboxs = (!empty($results['txcInboxs']) ? reset($results['txcInboxs']) : []);

            foreach (['pdfDocument', 'routeDocument', 'zipDocument'] as $doc) {
                if (empty($txcInboxs[$doc])) {
                    continue;
                }
                $documents[] = $txcInboxs[$doc];
            }
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
     * @param $busRegId
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
     * @param null $title
     * @param null $subtitle
     * @return \Zend\View\Model\ViewModel
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
     * @param $template
     * @param array $data
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
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        /** @var \Zend\Http\Header\Referer $header */
        $header = $request->getHeader('referer');

        return [
            'url' => $header->uri()->getPath(),
            'label' => 'bus-registrations-index-title',
        ];
    }

    /**
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
     * @param $params
     * @return \Zend\Form\FormInterface
     */
    private function getFilterForm($params)
    {
        /** @var \Zend\Form\FormInterface $filterForm */
        $filterForm = $this->getServiceLocator()->get('Helper\Form')->createForm('BusRegApplicationsFilterForm');

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
                User::USER_TYPE_OPERATOR
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
