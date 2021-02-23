<?php

declare(strict_types=1);

namespace Olcs\Controller\Licence\Vehicle;

use Common\Controller\Plugin\HandleCommand;
use Common\Controller\Plugin\HandleQuery;
use Common\Form\Elements\Custom\OlcsCheckbox;
use Common\Form\Elements\Types\AbstractInputSearch;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\ResponseHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableBuilder;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\Licence\UpdateVehicles;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\Licence\GoodsVehiclesExport;
use Dvsa\Olcs\Transfer\Query\Licence\Licence;
use Dvsa\Olcs\Transfer\Query\Licence\Vehicles;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Form;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\Mvc\Router\RouteMatch;
use Olcs\Form\Model\Form\Vehicle\ListVehicleSearch;
use Laminas\View\Model\ViewModel;
use Laminas\Http\PhpEnvironment\Response as HttpResponse;
use Olcs\Form\Model\Form\Vehicle\OCRSOptIn;
use Olcs\Table\TableEnum;

/**
 * @see ListVehicleControllerFactory
 */
class ListVehicleController
{
    public const FORMAT_HTML = 'html';
    public const FORMAT_CSV = 'csv';
    public const QUERY_KEY_SORT_CURRENT_VEHICLES_TABLE = 'sort-c';
    public const QUERY_KEY_ORDER_CURRENT_VEHICLES_TABLE = 'order-c';
    public const QUERY_KEY_SORT_REMOVED_VEHICLES_TABLE = 'sort-r';
    public const QUERY_KEY_ORDER_REMOVED_VEHICLES_TABLE = 'order-r';
    protected const DEFAULT_REMOVED_VEHICLES_TABLE_LIMIT = 10;
    protected const DEFAULT_CURRENT_VEHICLES_TABLE_LIMIT = 10;

    /**
     * @var HandleCommand
     */
    protected $commandHandler;

    /**
     * @var HandleQuery
     */
    protected $queryHandler;

    /**
     * @var TranslationHelperService
     */
    protected $translator;

    /**
     * @var Url
     */
    protected $urlHelper;

    /**
     * @var ResponseHelperService
     */
    protected $responseHelper;

    /**
     * @var TableFactory
     */
    protected $tableFactory;

    /**
     * @var FormHelperService
     */
    protected $formHelper;

    /**
     * @var FlashMessengerHelperService
     */
    protected $flashMessengerHelper;

    /**
     * @param HandleCommand $commandHandler
     * @param HandleQuery $queryHandler
     * @param TranslationHelperService $translator
     * @param Url $urlHelper
     * @param ResponseHelperService $responseHelper
     * @param TableFactory $tableFactory
     * @param FormHelperService $formHelper
     * @param FlashMessengerHelperService $flashMessengerHelper
     */
    public function __construct(
        HandleCommand $commandHandler,
        HandleQuery $queryHandler,
        TranslationHelperService $translator,
        Url $urlHelper,
        ResponseHelperService $responseHelper,
        TableFactory $tableFactory,
        FormHelperService $formHelper,
        FlashMessengerHelperService $flashMessengerHelper
    )
    {
        $this->commandHandler = $commandHandler;
        $this->queryHandler = $queryHandler;
        $this->translator = $translator;
        $this->urlHelper = $urlHelper;
        $this->responseHelper = $responseHelper;
        $this->tableFactory = $tableFactory;
        $this->formHelper = $formHelper;
        $this->flashMessengerHelper = $flashMessengerHelper;
    }

    /**
     * Handles a request from a user to list the vehicles associated with one of their licences.
     *
     * @param Request $request
     * @param RouteMatch $routeMatch
     * @return Response|ViewModel
     */
    public function indexAction(Request $request, RouteMatch $routeMatch)
    {
        $licenceId = (int) $routeMatch->getParam('licence');
        $urlQueryData = $request->getQuery()->toArray();
        $format = $request->getQuery()->get('format') ?? static::FORMAT_HTML;
        if ($format === static::FORMAT_HTML) {
            $licence = $this->getLicence(Licence::create(['id' => $licenceId]));
            $licenceVehicleList = $this->listLicenceVehicles(Vehicles::create([
                'id' => $licenceId,
                'page' => (int) ($urlQueryData['page'] ?? 1),
                'limit' => (int) ($urlQueryData['limit'] ?? static::DEFAULT_CURRENT_VEHICLES_TABLE_LIMIT),
                'sort' => $urlQueryData[static::QUERY_KEY_SORT_CURRENT_VEHICLES_TABLE] ?? AbstractVehicleController::DEFAULT_TABLE_SORT_COLUMN,
                'order' => $urlQueryData[static::QUERY_KEY_ORDER_CURRENT_VEHICLES_TABLE] ?? AbstractVehicleController::DEFAULT_TABLE_SORT_ORDER,
                'vrm' => $urlQueryData['vehicleSearch'][AbstractInputSearch::ELEMENT_INPUT_NAME] ?? null,
            ]));

            $shareVehicleInfoState = (($licence['organisation']['confirmShareVehicleInfo'] ?? 'N') === 'Y');

            $response = $this->renderHtmlResponse($request, [
                'title' => $this->isSearchResultsPage($request) ? 'licence.vehicle.list.search.header' : 'licence.vehicle.list.header',
                'licence' => $licence,
                'backLink' => $this->urlHelper->fromRoute('licence/vehicle/GET', ['licence' => $licenceId]),
                'shareVehicleInfoState' => $shareVehicleInfoState,
                'exportCurrentAndRemovedCsvAction' => $this->buildCurrentAndRemovedCsvUrl($licenceId),
                'toggleRemovedAction' => $this->buildToggleRemovedVehiclesUrl($licenceId, $urlQueryData),
                'bottomContent' => $this->buildChooseDifferentActionUrl($licenceId),
                'currentLicenceVehicleList' => $licenceVehicleList,
            ]);
        } else {
            $removedLicenceVehicleList = $this->listLicenceVehicles(GoodsVehiclesExport::create(['id' => $licenceId, 'includeRemoved' => true]));
            $response = $this->renderCsvResponse($request, [
                'removedLicenceVehicleList' => $removedLicenceVehicleList,
            ]);
        }
        return $response;
    }

    /**
     * Handles a request from a user to change the user's opt-in preference for OCRS.
     *
     * @param Request $request
     * @param RouteMatch $routeMatch
     * @return Response|ViewModel
     */
    public function postAction(Request $request, RouteMatch $routeMatch)
    {
        $licenceId = (int) $routeMatch->getParam('licence');

        $form = $this->createOcrsOptInForm($request->getPost()->toArray());
        if (!$form->isValid()) {
            return $this->indexAction($request, $routeMatch);
        }

        $complianceCheckbox = $form->getData()['ocrsCheckbox'];

        $updateVehicles = UpdateVehicles::create([
            'id' => $licenceId,
            'shareInfo' => $complianceCheckbox
        ]);

        $this->commandHandler->__invoke($updateVehicles);

        return $this->indexAction($request, $routeMatch);
    }

    /**
     * @param int $licenceId
     * @return string
     */
    protected function buildChooseDifferentActionUrl(int $licenceId): string
    {
        return $this->translator->translateReplace('licence.vehicle.generic.choose-different-action', [
            $this->urlHelper->fromRoute('licence/vehicle/GET', ['licence' => $licenceId]),
        ]);
    }

    /**
     * Checks the request for presence of vehicle search data to decide if the page
     * to show should be search results
     *
     * @param Request $request
     * @return bool
     */
    protected function isSearchResultsPage(Request $request): bool
    {
        $request = $this->filterSearchQuery($request->getQuery()->toArray());
        return array_key_exists('vehicleSearch', $request);
    }

    /**
     * Filter out unneeded variables from the vehicle search query if present
     *
     * @param array $query
     * @return array
     */
    protected function filterSearchQuery(array $query): array
    {
        if (empty($query['vehicleSearch'][AbstractInputSearch::ELEMENT_INPUT_NAME])) {
            unset($query['vehicleSearch']);
        } else {
            unset($query['vehicleSearch'][AbstractInputSearch::ELEMENT_SUBMIT_NAME]);
        }

        return $query;
    }

    /**
     * @param int $licenceId
     * @return string
     */
    protected function buildCurrentAndRemovedCsvUrl(int $licenceId): string
    {
        return $this->urlHelper->fromRoute('licence/vehicle/list/GET', ['licence' => $licenceId], ['query' => [
            'format' => 'csv',
            'includeRemoved' => '',
        ]]);
    }

    /**
     * Creates a response from a set of data; formatted as html.
     *
     * @param Request $request
     * @param array $data
     * @return ViewModel
     */
    protected function renderHtmlResponse(Request $request, array $data): ViewModel
    {
        $urlQueryParams = $request->getQuery()->toArray();
        $view = new ViewModel();
        $view->setTemplate('pages/licence/vehicle/list');

        // Build current vehicle table
        $data['currentVehiclesTable'] = $this->buildHtmlCurrentLicenceVehiclesTable($request, $data['currentLicenceVehicleList']);

        $ocrsFormPreData = [
            'ocrsCheckbox' => $data['shareVehicleInfoState']
        ];

        $view->setVariable('ocrsForm', $this->createOcrsOptInForm($ocrsFormPreData));

        unset($data['currentLicenceVehicleList']);
        if ($data['currentVehiclesTable']->getTotal() > $data['currentVehiclesTable']->getLimit() || $this->isSearchResultsPage($request)) {
            $searchFormUrl = $this->urlHelper->fromRoute('licence/vehicle/list/GET', ['licence' => $data['licence']['id']]);
            $view->setVariable('searchForm', $this->createSearchForm($searchFormUrl, $urlQueryParams));
            $view->setVariable('clearUrl', $this->buildSearchClearUrl($request));
        }

        // @todo (coming soon in VOL-136) Build removed vehicle table
//        $removedLicenceVehicleList = $data['removedLicenceVehicleList'];
//        if (! is_null($removedLicenceVehicleList)) {
//            $data['removedVehiclesTable'] = $this->buildHtmlRemovedLicenceVehiclesTable($request, $removedLicenceVehicleList);
//            $tableTotal = $data['removedVehiclesTable']->getTotal();
//            $data['removedVehicleTableTitle'] = $this->translator->translateReplace(
//                sprintf('licence.vehicle.list.section.removed.header.title.%s', $tableTotal === 1 ? 'singular' : 'plural'),
//                [$tableTotal]
//            );
//            unset($data['removedLicenceVehicleList']);
//        }

        return $view->setVariables($data);
    }

    /**
     * @param Request $request
     * @return string
     */
    protected function buildSearchClearUrl(Request $request): string
    {
        $urlQueryParams = $request->getQuery();
        $urlQueryParams->offsetUnset('vehicleSearch');
        $url = $request->getUri();
        $url->setQuery($urlQueryParams->toString());
        return $url->toString();
    }

    /**
     * @param int $licenceId
     * @param array $queryParams
     * @return string
     */
    protected function buildToggleRemovedVehiclesUrl(int $licenceId, array $queryParams = []): string
    {
        if (array_key_exists('includeRemoved', $queryParams)) {
            unset($queryParams['includeRemoved']);
            unset($queryParams[static::QUERY_KEY_SORT_REMOVED_VEHICLES_TABLE]);
            unset($queryParams[static::QUERY_KEY_ORDER_REMOVED_VEHICLES_TABLE]);
        } else {
            $queryParams['includeRemoved'] = '';
        }
        return $this->urlHelper->fromRoute('licence/vehicle/list/GET', ['licence' => $licenceId], ['query' => $queryParams]);
    }

    /**
     * Creates a response from a set of data; formatted as csv.
     *
     * @param Request $request
     * @param array $data
     * @return Response
     */
    protected function renderCsvResponse(Request $request, array $data): Response
    {
        $licenceVehicles = [
            'count' => $data['currentLicenceVehicleList']['count'],
            'results' => $data['currentLicenceVehicleList']['results'],
        ];

        if (isset($data['removedLicenceVehicleList'])) {
            $licenceVehicles['count'] = $data['removedLicenceVehicleList']['count'] ?? 0;
            $licenceVehicles['results'] = $data['removedLicenceVehicleList']['results'] ?? [];
        }

        $table = $this->tableFactory->getTableBuilder()->prepareTable('licence-vehicle-list-export-current-and-removed', $licenceVehicles);
        return $this->responseHelper->tableToCsv(new HttpResponse(), $table, 'vehicles');
    }

    /**
     * @param AbstractQuery $query
     * @return array|mixed
     */
    protected function listLicenceVehicles(AbstractQuery $query)
    {
        return $this->queryHandler->__invoke($query)->getResult();
    }

    /**
     * @param Licence $query
     * @return array|mixed
     */
    protected function getLicence(Licence $query)
    {
        return $this->queryHandler->__invoke($query)->getResult();
    }

    /**
     * Creates a OCRS Opt-In form.
     *
     * @param array $data
     * @return Form
     */
    protected function createOcrsOptInForm(array $data = []): Form
    {
        $form = $this->formHelper->createForm(OCRSOptIn::class, true, false);
        $form->setData($data);

        if (in_array('ocrsCheckbox', $data)) {
            $checked = $data['ocrsCheckbox'] === 'Y';

            $checkbox =  $form->get('ocrsCheckbox');
            assert($checkbox instanceof OlcsCheckbox, '$checkbox is not an instance of OlcsCheckbox');
            $checkbox->setChecked($checked);
        }

        return $form;
    }

    /**
     * @param string $action
     * @param array $data
     * @return Form
     */
    protected function createSearchForm(string $action, array $data = []): Form
    {
        $form = $this->formHelper->createForm(ListVehicleSearch::class, true, false);
        assert($form instanceof Form, 'Expected instance of Form');
        $form->get('vehicleSearch')->setOption('legend', 'licence.vehicle.table.search.list.legend');
        $form->setAttribute('action', $action);

        $searchFormData = array_intersect_key($data, array_flip(['vehicleSearch']));
        if (! empty($searchFormData)) {
            $form->setData($searchFormData);
            $form->isValid();
        }

        // Add hidden fields for any other data that is not search related
        $extraFormData = array_diff_key($data, array_flip(['vehicleSearch']));
        while (! empty($extraFormData)) {
            end($extraFormData);
            $key = key($extraFormData);
            $value = array_pop($extraFormData);
            if (is_array($value)) {
                foreach ($value as $arrayItemKey => $arrayItemValue) {
                    $extraFormData[sprintf('%s[%s]', $key, $arrayItemKey)] = $arrayItemValue;
                }
                continue;
            }
            $hiddenInputElement = new Hidden();
            $hiddenInputElement->setAttribute('name', $key);
            $hiddenInputElement->setAttribute('value', $value);
            $form->add($hiddenInputElement);
        }

        $form->remove('security');
        $form->prepare();
        return $form;
    }

    /**
     * Creates a new vehicle table.
     *
     * @param Request $request
     * @param array $currentLicenceVehicleList
     * @return TableBuilder
     */
    protected function buildHtmlCurrentLicenceVehiclesTable(Request $request, array $currentLicenceVehicleList): TableBuilder
    {
        $requestQueryParams = $request->getQuery()->toArray();

        $params = [
            'page' => (int) (array_key_exists('page', $requestQueryParams) && !empty($requestQueryParams['page']) ? $requestQueryParams['page'] : 1),
            'sort' => $requestQueryParams[static::QUERY_KEY_SORT_CURRENT_VEHICLES_TABLE] ?? null,
            'order' => $requestQueryParams[static::QUERY_KEY_ORDER_CURRENT_VEHICLES_TABLE] ?? null,
            'query' => $requestQueryParams,
            'limit' => (int) ($requestQueryParams['limit'] ?? static::DEFAULT_CURRENT_VEHICLES_TABLE_LIMIT),
        ];

        $table = $this->tableFactory->getTableBuilder();

        $table->setUrlParameterNameMap([
            'sort' => static::QUERY_KEY_SORT_CURRENT_VEHICLES_TABLE,
            'order' => static::QUERY_KEY_ORDER_CURRENT_VEHICLES_TABLE,
        ]);

        $table = $table->prepareTable('licence-vehicles', $currentLicenceVehicleList, $params);

        $totalVehicles = $currentLicenceVehicleList['count'];
        if ($this->isSearchResultsPage($request)) {
            $table = $this->alterTableForSearchView($table, $totalVehicles);
        } else {
            $table = $this->alterTableForDefaultView($table, $totalVehicles);
        }

        // Always prefix the table title with the table total
        $table->setSetting('showTotal', true);

        // No action is needed in the view
        $table->removeColumn('action');

        if ($table->getTotal() <= $table->getLimit()) {

            // Disable pagination when the query has fewer total results then the table item limit
            $table->setSettings(array_diff_key($table->getSettings(), array_flip(['paginate'])));
        }

        return $table;
    }

    /**
     * Alter the vehicle table for search results view
     *
     * @param TableBuilder $table
     * @param int $totalVehicles
     * @return TableBuilder
     */
    protected function alterTableForSearchView(TableBuilder $table, int $totalVehicles): TableBuilder
    {
        switch ($totalVehicles) {
            case 0:
                $title = AbstractVehicleController::TABLE_SEARCH_TITLE_EMPTY;
                break;
            case 1:
                $title = AbstractVehicleController::TABLE_SEARCH_TITLE_SINGULAR;
                break;
            default:
                $title = AbstractVehicleController::TABLE_SEARCH_TITLE_PLURAL;
        }
        $table->setVariable('title', $this->translator->translate($title));
        $table->setSetting('overrideTotal', false);
        return $table;
    }

    /**
     * Alter vehicle table to default view
     *
     * @param TableBuilder $table
     * @param int $totalVehicles
     * @return TableBuilder
     */
    protected function alterTableForDefaultView(TableBuilder $table, int $totalVehicles): TableBuilder
    {
        $title = $totalVehicles == 1 ? AbstractVehicleController::TABLE_TITLE_SINGULAR : AbstractVehicleController::TABLE_TITLE_PLURAL;
        $table->setVariable('title', $this->translator->translateReplace($title, [$totalVehicles]));
        return $table;
    }

    /**
     * Creates a new vehicle table.
     *
     * @param Request $request
     * @param array $currentLicenceVehicleList
     * @return TableBuilder
     */
    protected function buildHtmlRemovedLicenceVehiclesTable(Request $request, array $currentLicenceVehicleList): TableBuilder
    {
        $requestQueryParams = $request->getQuery()->toArray();
        $params = [
            'page' => 1,
            'sort' => $requestQueryParams[static::QUERY_KEY_SORT_REMOVED_VEHICLES_TABLE] ?? null,
            'order' => $requestQueryParams[static::QUERY_KEY_ORDER_REMOVED_VEHICLES_TABLE] ?? null,
            'query' => $requestQueryParams,
            'limit' => static::DEFAULT_REMOVED_VEHICLES_TABLE_LIMIT,
        ];
        $table = $this->tableFactory->getTableBuilder();
        $table->setUrlParameterNameMap([
            'sort' => static::QUERY_KEY_SORT_REMOVED_VEHICLES_TABLE,
            'order' => static::QUERY_KEY_ORDER_REMOVED_VEHICLES_TABLE
        ]);
        $table = $table->prepareTable('licence-vehicles', $currentLicenceVehicleList, $params);

        // Always prefix the table title with the table total
        $table->setSetting('showTotal', true);

        // No action is needed in the view
        $table->removeColumn('action');

        // Disable pagination when the query has fewer total results then the table item limit
        $table->setSettings(array_diff_key($table->getSettings(), array_flip(['paginate'])));

        return $table;
    }
}
