<?php

namespace Olcs\Controller\BusReg;

use Common\Controller\Lva\AbstractController;
use Common\Rbac\User;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Query\BusRegSearchView\BusRegSearchViewList as ListDto;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\View\Model\ViewModel;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Class BusRegRegistrationsController
 */
class BusRegRegistrationsController extends AbstractController
{
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
     * Lists all Bus Reg's with filter search form
     *
     * @return ViewModel|\Laminas\Http\Response
     */
    #[\Override]
    public function indexAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $postData = $request->getPost();

            return $this->processSearch($postData);
        }

        $params = [
            'organisationId'    => $this->params()->fromQuery('organisationId', null),
            'busRegStatus'      => $this->params()->fromQuery('busRegStatus', null),
            'licId'             => $this->params()->fromQuery('licId', null),
            'page'              => $this->params()->fromQuery('page', 1),
            'order'             => $this->params()->fromQuery('order', 'ASC'),
            'limit'             => $this->params()->fromQuery('limit', 25),
        ];

        $params['sort'] = $this->params()->fromQuery('sort', 'licNo, routeNo');

        $query = ListDto::create($params);

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

        $filterForm = $this->getFilterForm($params);

        // setup layout and view
        $layout = $this->generateLayout(
            [
                'pageTitle' => 'bus-registrations-index-title',
                'searchForm' => $filterForm,
                'activeTab' => 'registrations',
                'showNav' => false,
                'tabs' => $this->generateTabs(),
            ]
        );

        $content = $this->generateContent(
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
     * @param (array|mixed)[] $params
     *
     * @return string
     *
     * @psalm-param array{organisationId: mixed, busRegStatus: mixed, licId: mixed, page: mixed, order: mixed, limit: mixed, sort: mixed, query: array{organisationId: mixed, busRegStatus: mixed, licId: mixed, page: mixed, order: mixed, limit: mixed, sort: mixed}} $params
     */
    private function generateTable($result, array $params)
    {
        /** @var \Common\Service\Table\TableBuilder $tableBuilder */
        $tableBuilder = $this->tableFactory;

        $tableName = 'busreg-registrations';

        $busRegistrationTable = $tableBuilder->buildTable(
            $tableName,
            ['Results' => $result['results'], 'Count' => $result['count']],
            $params,
            false
        );

        return $busRegistrationTable;
    }

    /**
     * Process the search, simply sets up the GET params and redirects
     * @param $data
     * @return \Laminas\Http\Response
     */
    private function processSearch($data)
    {
        $params = $this->params()->fromQuery();

        $params['organisationId'] = empty($data['fields']['organisationId']) ?
            null : $data['fields']['organisationId'];
        $params['busRegStatus'] = empty($data['fields']['busRegStatus']) ? null : $data['fields']['busRegStatus'];
        $params['licId'] = empty($data['fields']['licId']) ? null : $data['fields']['licId'];

        // initialise search results to page 1
        $params['page'] = 1;

        return $this->redirect()->toRoute(null, [], ['query' => $params], true);
    }

    /**
     * Set up the layout with title, subtitle and content
     *
     * @param null $title
     * @param null $subtitle
     * @param (array|false|mixed|string)[] $data
     *
     * @return \Laminas\View\Model\ViewModel
     *
     * @psalm-param array{pageTitle?: 'bus-registrations-index-title', searchForm?: mixed, activeTab?: 'registrations', showNav?: false, tabs?: array} $data
     */
    private function generateLayout(array $data = [])
    {
        $layout = new \Laminas\View\Model\ViewModel(
            $data
        );

        $layout->setTemplate('layouts/search');

        return $layout;
    }

    /**
     * Generate page content
     *
     * @param $template
     * @param array $data
     *
     * @return ViewModel
     *
     * @psalm-param 'olcs/bus-registration/index' $template
     */
    private function generateContent(string $template, $data = [])
    {
        $content = new ViewModel($data);

        $content->setTemplate($template);
        return $content;
    }

    /**
     * Get and setup the filter form
     *
     * @param (array|mixed)[] $params
     *
     * @return mixed
     *
     * @psalm-param array{organisationId: mixed, busRegStatus: mixed, licId: mixed, page: mixed, order: mixed, limit: mixed, sort: mixed, query: array{organisationId: mixed, busRegStatus: mixed, licId: mixed, page: mixed, order: mixed, limit: mixed, sort: mixed}} $params
     */
    public function getFilterForm(array $params)
    {
        /** @var \Common\Form\Form $filterForm */
        $filterForm = $this->formHelper->createForm('BusRegRegistrationsFilterForm');

        if ($this->currentUser()->getUserData()['userType'] !== User::USER_TYPE_LOCAL_AUTHORITY) {
            // remove Organisation name filter for organisations
            $filterForm->get('fields')->remove('organisationId');
        } else {
            // removed licence no filter for LAs
            $filterForm->get('fields')->remove('licId');
        }

        $filterForm->setData(
            [
                'fields' => [
                    'organisationId' => $params['organisationId'],
                    'licId' => $params['licId'],
                    'busRegStatus' => $params['busRegStatus']
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
                ]
            )
        ) {
            return [
                0 => [
                    'label' => 'busreg-tab-title-registrations',
                    'route' => 'busreg-registrations',
                    'active' => true
                ],
                1 => [
                    'label' => 'busreg-tab-title-applications',
                    'route' => 'bus-registration'
                ]
            ];
        }
        return [];
    }
}
