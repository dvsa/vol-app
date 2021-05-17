<?php

declare(strict_types=1);

namespace Olcs\Controller\Licence\Vehicle;

use Common\Controller\Plugin\HandleCommand;
use Common\Controller\Plugin\HandleQuery;
use Common\Controller\Plugin\Redirect;
use Common\Exception\BailOutException;
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
use Laminas\Filter\StringToUpper;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Form;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\Mvc\Router\RouteMatch;
use Laminas\Validator\InArray;
use Olcs\Form\Model\Form\Vehicle\ListVehicleSearch;
use Laminas\View\Model\ViewModel;
use Laminas\Http\PhpEnvironment\Response as HttpResponse;
use Olcs\Form\Model\Form\Vehicle\OCRSOptIn;
use Olcs\Table\TableEnum;
use Laminas\Stdlib\ResponseInterface;

/**
 * @see ListVehicleControllerFactory
 * @see \OlcsTest\Controller\Licence\Vehicle\ListVehicleControllerTest
 */
class ListVehicleController
{
    const FORMAT_HTML = 'html';
    const FORMAT_CSV = 'csv';
    const QUERY_KEY_SORT_CURRENT_VEHICLES = 'sort-c';
    const QUERY_KEY_ORDER_CURRENT_VEHICLES = 'order-c';
    const QUERY_KEY_SORT_REMOVED_VEHICLES = 'sort-r';
    const QUERY_KEY_ORDER_REMOVED_VEHICLES = 'order-r';
    const QUERY_KEY_INCLUDE_REMOVED = 'includeRemoved';
    const DEFAULT_LIMIT_REMOVED_VEHICLES = 10;
    const DEFAULT_LIMIT_CURRENT_VEHICLES = 10;
    const DEFAULT_SORT_CURRENT_VEHICLES = 'specifiedDate';
    const DEFAULT_ORDER_CURRENT_VEHICLES = 'DESC';
    const DEFAULT_SORT_REMOVED_VEHICLES = 'removalDate';
    const DEFAULT_ORDER_REMOVED_TABLE = 'DESC';

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
    protected $flashMessenger;

    /**
     * @var Redirect
     */
    protected $redirectHelper;

    /**
     * @param HandleCommand $commandHandler
     * @param HandleQuery $queryHandler
     * @param TranslationHelperService $translator
     * @param Url $urlHelper
     * @param ResponseHelperService $responseHelper
     * @param TableFactory $tableFactory
     * @param FormHelperService $formHelper
     * @param FlashMessengerHelperService $flashMessenger
     * @param Redirect $redirectHelper
     */
    public function __construct(
        HandleCommand $commandHandler,
        HandleQuery $queryHandler,
        TranslationHelperService $translator,
        Url $urlHelper,
        ResponseHelperService $responseHelper,
        TableFactory $tableFactory,
        FormHelperService $formHelper,
        FlashMessengerHelperService $flashMessenger,
        Redirect $redirectHelper
    ) {
        $this->commandHandler = $commandHandler;
        $this->queryHandler = $queryHandler;
        $this->translator = $translator;
        $this->urlHelper = $urlHelper;
        $this->responseHelper = $responseHelper;
        $this->tableFactory = $tableFactory;
        $this->formHelper = $formHelper;
        $this->flashMessenger = $flashMessenger;
        $this->redirectHelper = $redirectHelper;
    }

    /**
     * Handles a request from a user to list the vehicles associated with one of their licences.
     *
     * @param Request $request
     * @param RouteMatch $routeMatch
     * @return ViewModel|ResponseInterface
     */
    public function indexAction(Request $request, RouteMatch $routeMatch)
    {
        $licenceId = (int) $routeMatch->getParam('licence');
        $inputFilter = $this->newInputFilter($request->getQuery()->toArray());
        if (! $inputFilter->isValid()) {
            foreach ($inputFilter->getMessages() as $messages) {
                foreach ($messages as $message) {
                    $this->flashMessenger->addErrorMessage($message);
                }
            }
            return $this->redirectHelper->refresh();
        }
        $input = array_filter(array_merge($inputFilter->getValues(), $inputFilter->getUnknown()), function ($val) {
            return $val !== null;
        });

        $format = $input['format'] ?? static::FORMAT_HTML;
        if ($format === static::FORMAT_CSV) {
            $removedLicenceVehicleList = $this->listLicenceVehicles(GoodsVehiclesExport::create(['id' => $licenceId, 'includeRemoved' => true]));
            return $this->renderCsvResponse(['removedLicenceVehicleList' => $removedLicenceVehicleList]);
        }

        $licence = $this->getLicence(Licence::create(['id' => $licenceId]));
        $licenceVehicleList = $this->listLicenceVehicles(Vehicles::create([
            'id' => $licenceId,
            'page' => (int) ($input['page'] ?? 1),
            'limit' => (int) ($input['limit'] ?? static::DEFAULT_LIMIT_CURRENT_VEHICLES),
            'sort' => $input[static::QUERY_KEY_SORT_CURRENT_VEHICLES] ?? static::DEFAULT_SORT_CURRENT_VEHICLES,
            'order' => $input[static::QUERY_KEY_ORDER_CURRENT_VEHICLES] ?? static::DEFAULT_ORDER_CURRENT_VEHICLES,
            'vrm' => $input[ListVehicleSearch::FIELD_VEHICLE_SEARCH][AbstractInputSearch::ELEMENT_INPUT_NAME] ?? null,
        ]));

        $viewData = [
            'title' => $this->isSearchResultsPage($input) ? 'licence.vehicle.list.search.header' : 'licence.vehicle.list.header',
            'licence' => $licence,
            'backLink' => $this->urlHelper->fromRoute('licence/vehicle/GET', ['licence' => $licenceId]),
            'shareVehicleInfoState' => $licence['organisation']['confirmShareVehicleInfo'],
            'exportCurrentAndRemovedCsvAction' => $this->buildCurrentAndRemovedCsvUrl($licenceId),
        ];

        // Build current vehicle data
        $viewData['currentVehiclesTable'] = $this->buildHtmlCurrentLicenceVehiclesTable($input, $licenceVehicleList);

        // Build OCRS data
        $viewData['ocrsForm'] = $this->createOcrsOptInForm(['ocrsCheckbox' => $viewData['shareVehicleInfoState']]);

        // Build search data
        $isSearchResultsPage = $this->isSearchResultsPage($input);
        if ($viewData['currentVehiclesTable']->getTotal() > $viewData['currentVehiclesTable']->getLimit() || $isSearchResultsPage) {
            $searchFormUrl = $this->urlHelper->fromRoute('licence/vehicle/list/GET', ['licence' => $viewData['licence']['id']]);
            $viewData['searchForm'] = $this->createSearchForm($searchFormUrl, $input);
            $viewData['clearUrl'] = $this->buildSearchClearUrl($request);
        }

        // Build removed vehicles data
        if (! $isSearchResultsPage) {
            $viewData = array_merge($viewData, $this->getLatestRemovedVehiclesData($licenceId, $input));
        }

        $view = new ViewModel();
        $view->setTemplate('pages/licence/vehicle/list');
        return $view->setVariables($viewData);
    }

    /**
     * Handles a request from a user to change the user's opt-in preference for OCRS.
     *
     * @param Request $request
     * @param RouteMatch $routeMatch
     * @return Response|ResponseInterface|ViewModel
     * @throws BailOutException
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
     * @param array $data
     * @return InputFilter
     */
    protected function newInputFilter(array $data)
    {
        $filter = new InputFilter();
        $filter->add($this->newSortColumnInput(static::QUERY_KEY_SORT_REMOVED_VEHICLES, ['v.vrm', 'specifiedDate', 'removalDate']));
        $filter->add($this->newSortColumnInput(static::QUERY_KEY_SORT_CURRENT_VEHICLES, ['v.vrm', 'specifiedDate']));
        $filter->add($this->newOrderInput(static::QUERY_KEY_ORDER_REMOVED_VEHICLES));
        $filter->add($this->newOrderInput(static::QUERY_KEY_ORDER_CURRENT_VEHICLES));
        $filter->setData($data);
        return $filter;
    }

    /**
     * @param string $name
     * @return Input
     */
    protected function newInput(string $name): Input
    {
        $input = new Input($name);
        $input->setContinueIfEmpty(true);
        return $input;
    }

    /**
     * @param string $name
     * @param array $validColumnNames
     * @return Input
     */
    protected function newSortColumnInput(string $name, array $validColumnNames): Input
    {
        $input = $this->newInput($name);
        $input->setRequired(false);

        $sortValidatorChain = $input->getValidatorChain();

        $inArrayValidator = new InArray();
        $inArrayValidator->setHaystack($validColumnNames);
        $inArrayValidator->setMessages([InArray::NOT_IN_ARRAY => 'table.validation.error.sort.in-array']);
        $inArrayValidator->setTranslator($this->translator->getTranslator());
        $sortValidatorChain->attach($inArrayValidator);

        return $input;
    }

    /**
     * @param string $name
     * @return Input
     */
    protected function newOrderInput(string $name): Input
    {
        $input = $this->newInput($name);
        $input->setRequired(false);

        // Build validator chain
        $sortValidatorChain = $input->getValidatorChain();

        $inArrayValidator = new InArray();
        $inArrayValidator->setHaystack(['ASC', 'DESC']);
        $inArrayValidator->setMessages([InArray::NOT_IN_ARRAY => 'table.validation.error.order.in-array']);
        $inArrayValidator->setTranslator($this->translator->getTranslator());
        $sortValidatorChain->attach($inArrayValidator);

        // Build filter chain
        $sortFilterChain = $input->getFilterChain();
        $sortFilterChain->attach(new StringToUpper());

        return $input;
    }

    /**
     * Checks the request for presence of vehicle search data to decide if the page
     * to show should be search results
     *
     * @param array $input
     * @return bool
     */
    protected function isSearchResultsPage(array $input): bool
    {
        return array_key_exists(ListVehicleSearch::FIELD_VEHICLE_SEARCH, $input);
    }

    /**
     * @param int $licenceId
     * @return string
     */
    protected function buildCurrentAndRemovedCsvUrl(int $licenceId): string
    {
        return $this->urlHelper->fromRoute('licence/vehicle/list/GET', ['licence' => $licenceId], ['query' => [
            'format' => static::FORMAT_CSV,
            static::QUERY_KEY_INCLUDE_REMOVED => '',
        ]]);
    }

    /**
     * @param Request $request
     * @return string
     */
    protected function buildSearchClearUrl(Request $request): string
    {
        $urlQueryParams = $request->getQuery()->toArray();
        unset($urlQueryParams[ListVehicleSearch::FIELD_VEHICLE_SEARCH]);
        $url = $request->getUri();
        $url->setQuery($urlQueryParams);
        return $url->toString();
    }

    /**
     * @param int $licenceId
     * @param array $queryParams
     * @return string
     */
    protected function buildToggleRemovedVehiclesUrl(int $licenceId, array $queryParams = []): string
    {
        if (array_key_exists(static::QUERY_KEY_INCLUDE_REMOVED, $queryParams)) {
            unset($queryParams[static::QUERY_KEY_INCLUDE_REMOVED]);
            unset($queryParams[static::QUERY_KEY_SORT_REMOVED_VEHICLES]);
            unset($queryParams[static::QUERY_KEY_ORDER_REMOVED_VEHICLES]);
        } else {
            $queryParams[static::QUERY_KEY_INCLUDE_REMOVED] = '';
        }
        return $this->urlHelper->fromRoute('licence/vehicle/list/GET', ['licence' => $licenceId], ['query' => $queryParams]);
    }

    /**
     * Creates a response from a set of data; formatted as csv.
     *
     * @param array $data
     * @return Response
     */
    protected function renderCsvResponse(array $data): Response
    {
        $licenceVehicles = [
            'count' => $data['currentLicenceVehicleList']['count'],
            'results' => $data['currentLicenceVehicleList']['results'],
        ];

        if (isset($data['removedLicenceVehicleList'])) {
            $licenceVehicles['count'] = $data['removedLicenceVehicleList']['count'] ?? 0;
            $licenceVehicles['results'] = $data['removedLicenceVehicleList']['results'] ?? [];
        }

        $table = $this->tableFactory->getTableBuilder()->prepareTable(TableEnum::LICENCE_VEHICLE_LIST_EXPORT_CURRENT_AND_REMOVED, $licenceVehicles);
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
        $form->get(ListVehicleSearch::FIELD_VEHICLE_SEARCH)->setOption('legend', 'licence.vehicle.table.search.list.legend');
        $form->setAttribute('action', $action);

        $searchFormData = array_intersect_key($data, array_flip([ListVehicleSearch::FIELD_VEHICLE_SEARCH]));
        if (! empty($searchFormData)) {
            $form->setData($searchFormData);
            $form->isValid();
        }

        // Add hidden fields for any other data that is not search related
        $extraFormData = array_diff_key($data, array_flip([ListVehicleSearch::FIELD_VEHICLE_SEARCH]));
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
     * @param array $input
     * @param array $currentLicenceVehicleList
     * @return TableBuilder
     */
    protected function buildHtmlCurrentLicenceVehiclesTable(array $input, array $currentLicenceVehicleList): TableBuilder
    {
        $params = [
            'page' => (int) (array_key_exists('page', $input) && !empty($input['page']) ? $input['page'] : 1),
            'sort' => $input[static::QUERY_KEY_SORT_CURRENT_VEHICLES] ?? null,
            'order' => $input[static::QUERY_KEY_ORDER_CURRENT_VEHICLES] ?? null,
            'query' => $input,
            'limit' => (int) ($input['limit'] ?? static::DEFAULT_LIMIT_CURRENT_VEHICLES),
        ];

        $table = $this->tableFactory->prepareTable(TableEnum::LICENCE_VEHICLE_LIST_CURRENT, $currentLicenceVehicleList, $params);
        $table->setUrlParameterNameMap([
            'sort' => static::QUERY_KEY_SORT_CURRENT_VEHICLES,
            'order' => static::QUERY_KEY_ORDER_CURRENT_VEHICLES,
        ]);

        $totalVehicles = $currentLicenceVehicleList['count'];
        if ($this->isSearchResultsPage($input)) {
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
     * @param int $licenceId
     * @param array $input
     * @return array
     */
    protected function getLatestRemovedVehiclesData(int $licenceId, array $input): array
    {
        $data = [];

        $removedVehicleList = $this->listLicenceVehicles(Vehicles::create([
            'id' => $licenceId,
            'page' => 1,
            'limit' => static::DEFAULT_LIMIT_REMOVED_VEHICLES,
            'includeRemoved' => true,
            'includeActive' => false,
            'sort' => static::DEFAULT_SORT_REMOVED_VEHICLES,
            'order' => static::DEFAULT_ORDER_REMOVED_TABLE,
        ]));

        $removedVehicleCount = (int) $removedVehicleList['count'];
        $data['showRemovedVehicles'] = $removedVehicleCount > 0;
        if ($data['showRemovedVehicles']) {
            $data['removedVehiclesTable'] = $this->buildHtmlRemovedLicenceVehiclesTable($input, $removedVehicleList);
            $data['toggleRemovedAction'] = $this->buildToggleRemovedVehiclesUrl($licenceId, $input);
            $data['toggleRemovedVehiclesActionTitle'] = 'licence.vehicle.list.section.removed.action.show-removed-vehicles.title';
            $data['toggleRemovedVehiclesActionLabel'] = 'licence.vehicle.list.section.removed.action.show-removed-vehicles.label';

            if (array_key_exists(static::QUERY_KEY_INCLUDE_REMOVED, $input)) {
                $tableRowCount = count($removedVehicleList['results']);
                $data['removedVehicleTableTitle'] = $this->translator->translateReplace(
                    sprintf('licence.vehicle.list.section.removed.header.title.%s', $tableRowCount === 1 ? 'singular' : 'plural'),
                    [$tableRowCount]
                );
                $data['showRemovedVehiclesExpanded'] = true;
                $data['toggleRemovedVehiclesActionTitle'] = 'licence.vehicle.list.section.removed.action.hide-removed-vehicles.title';
                $data['toggleRemovedVehiclesActionLabel'] = 'licence.vehicle.list.section.removed.action.hide-removed-vehicles.label';
            }
        }
        return $data;
    }

    /**
     * Creates a new vehicle table.
     *
     * @param array $input
     * @param array $currentLicenceVehicleList
     * @return TableBuilder
     */
    protected function buildHtmlRemovedLicenceVehiclesTable(array $input, array $currentLicenceVehicleList): TableBuilder
    {
        $table = $this->tableFactory->prepareTable(TableEnum::LICENCE_VEHICLE_LIST_REMOVED, $currentLicenceVehicleList, [
            'page' => 1,
            'sort' => $input[static::QUERY_KEY_SORT_REMOVED_VEHICLES] ?? null,
            'order' => $input[static::QUERY_KEY_ORDER_REMOVED_VEHICLES] ?? null,
            'query' => $input,
            'limit' => static::DEFAULT_LIMIT_REMOVED_VEHICLES,
        ]);
        $table->setUrlParameterNameMap([
            'sort' => static::QUERY_KEY_SORT_REMOVED_VEHICLES,
            'order' => static::QUERY_KEY_ORDER_REMOVED_VEHICLES
        ]);

        // Always prefix the table title with the table total
        $table->setSetting('showTotal', true);

        // No action is needed in the view
        $table->removeColumn('action');

        // Disable pagination when the query has fewer total results then the table item limit
        $table->setSettings(array_diff_key($table->getSettings(), array_flip(['paginate'])));

        return $table;
    }
}
