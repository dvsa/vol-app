<?php

namespace Olcs\Controller\Licence\Vehicle;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;
use Common\Form\Elements\Types\AbstractInputSearch;
use Common\RefData;
use Common\Service\Cqrs\Exception\NotFoundException;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableBuilder;
use Common\Util\FlashMessengerTrait;
use Dvsa\Olcs\Transfer\Query\DvlaSearch\Vehicle;
use Dvsa\Olcs\Transfer\Query\Licence\Vehicles;
use Exception;
use Olcs\Controller\AbstractSelfserveController;
use Olcs\Controller\Config\DataSource\DataSourceConfig;
use Olcs\Session\LicenceVehicleManagement;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

abstract class AbstractVehicleController extends AbstractSelfserveController implements ToggleAwareInterface
{
    use FlashMessengerTrait;

    public const DEFAULT_TABLE_SORT_ORDER = 'DESC';
    public const DEFAULT_TABLE_SORT_COLUMN = 'createdOn';
    public const DEFAULT_TABLE_ROW_LIMIT = 10;
    public const TABLE_TITLE_SINGULAR = 'licence.vehicle.table.title.singular';
    public const TABLE_TITLE_PLURAL = 'licence.vehicle.table.title.plural';
    protected const TABLE_SEARCH_TITLE_SINGULAR = 'licence.vehicle.table.search.title.singular';
    protected const TABLE_SEARCH_TITLE_PLURAL = 'licence.vehicle.table.search.title.plural';
    protected const TABLE_SEARCH_TITLE_EMPTY = 'licence.vehicle.table.search.title.empty';

    protected $toggleConfig = [
        'default' => [FeatureToggle::DVLA_INTEGRATION]
    ];

    protected $templateConfig = [
        'default' => 'pages/licence-vehicle'
    ];

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::LICENCE
    ];

    protected $pageTemplate = 'pages/licence-vehicle';

    /** @var  FormHelperService */
    protected $hlpForm;

    /** @var  FlashMessengerHelperService */
    protected $hlpFlashMsgr;

    /** @var LicenceVehicleManagement */
    protected $session;

    /**
     * @var TranslationHelperService
     */
    protected $translator;

    /**
     * @var int $licenceId
     */
    protected $licenceId;

    /**
     * @param MvcEvent $e
     * @return array|mixed|\Zend\Http\PhpEnvironment\Response|\Zend\Http\Response
     */
    public function onDispatch(MvcEvent $e)
    {
        $this->licenceId = (int)$this->params('licence');
        $this->hlpForm = $this->getServiceLocator()->get('Helper\Form');
        $this->hlpFlashMsgr = $this->getServiceLocator()->get('Helper\FlashMessenger');
        $this->translator = $this->getServiceLocator()->get('Helper\Translation');
        $this->session = new LicenceVehicleManagement();
        return parent::onDispatch($e);
    }

    /**
     * @param array $params
     * @return ViewModel
     */
    protected function renderView(array $params): ViewModel
    {
        $content = new ViewModel($params);
        $content->setTemplate($this->pageTemplate);

        $view = new ViewModel();
        $view->setTemplate('layout/layout')
            ->setTerminal(true)
            ->addChild($content, 'content');

        return $view;
    }

    /**
     * Get a url based on a named route
     *
     * @param string $route
     * @return string
     */
    protected function getLink(string $route): string
    {
        return $this->url()->fromRoute($route, [], [], true);
    }

    /**
     * @return bool
     */
    protected function isGoods(): bool
    {
        return $this->data['licence']['goodsOrPsv']['id'] === RefData::LICENCE_CATEGORY_GOODS_VEHICLE;
    }

    /**
     * @param string $vrm
     * @return array
     * @throws Exception
     * @throws NotFoundException
     */
    protected function fetchDvlaVehicleData(string $vrm): array
    {
        $response = $this->handleQuery(Vehicle::create([
            'vrm' => $vrm
        ]));

        if (!$response->isOk()) {
            throw new Exception("Bad response: " . $response->getStatusCode());
        }

        if ($response->getResult()['count'] === 0) {
            throw new NotFoundException("Vehicle not found with vrm: $vrm");
        }

        return $response->getResult()['results'][0];
    }

    /**
     * Get filters for the vehicle table
     *
     * @return array
     */
    protected function getTableFilters()
    {
        $query = $this->getRequest()->getQuery()->toArray();

        return [
            'page' => $query['page'] ?? 1,
            'limit' => $query['limit'] ?? static::DEFAULT_TABLE_ROW_LIMIT,
            'sort' => $query['sort'] ?? static::DEFAULT_TABLE_SORT_COLUMN,
            'order' => $query['order'] ?? static::DEFAULT_TABLE_SORT_ORDER,
            'vrm' => $query['vehicleSearch'][AbstractInputSearch::ELEMENT_INPUT_NAME] ?? null
        ];
    }

    /**
     * Get an instance of the licence-vehicles table
     *
     * @return TableBuilder
     */
    protected function createVehicleTable(): TableBuilder
    {
        $dtoData = $this->getTableFilters();
        $dtoData['id'] = $this->licenceId;
        $vehicleData = $this->handleQuery(Vehicles::create($dtoData))->getResult();
        $totalVehicles = $vehicleData['count'];

        $query = $this->filterSearchQuery($this->getRequest()->getQuery()->toArray());
        $params = array_merge($dtoData, ['query' => $query]);

        /** @var TableBuilder $table */
        $table = $this->getServiceLocator()->get('Table');
        $table = $table->prepareTable('licence-vehicles', $vehicleData, $params);

        if ($this->isSearchResultsPage()) {
            return $this->alterTableForSearchView($table, $totalVehicles);
        }

        return $this->alterTableForDefaultView($table, $totalVehicles);
    }

    /**
     * @return string
     */
    protected function getChooseDifferentActionMarkup(): string
    {
        return $this->translator->translateReplace(
            'licence.vehicle.generic.choose-different-action',
            [
                $this->getLink('licence/vehicle/GET')
            ]
        );
    }

    /**
     * Checks the request for presence of vehicle search data to decide if the page
     * to show should be search results
     *
     * @return bool
     */
    protected function isSearchResultsPage(): bool
    {
        $request = $this->filterSearchQuery($this->getRequest()->getQuery()->toArray());

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
     * Alter the vehicle table for search results view
     *
     * @param TableBuilder $table
     * @param $totalVehicles
     */
    protected function alterTableForSearchView(TableBuilder $table, $totalVehicles): TableBuilder
    {
        switch ($totalVehicles) {
            case 0:
                $title = static::TABLE_SEARCH_TITLE_EMPTY;
                break;
            case 1:
                $title = static::TABLE_SEARCH_TITLE_SINGULAR;
                break;
            default:
                $title = static::TABLE_SEARCH_TITLE_PLURAL;
        }
        $table->setVariable('title', $this->translator->translate($title));
        $table->setSetting('overrideTotal', false);
        return $table;
    }

    /**
     * Alter vehicle table to default view
     *
     * @param TableBuilder $table
     * @param $totalVehicles
     */
    protected function alterTableForDefaultView(TableBuilder $table, $totalVehicles): TableBuilder
    {
        $title = $totalVehicles == 1 ? static::TABLE_TITLE_SINGULAR : static::TABLE_TITLE_PLURAL;

        $table->setVariable(
            'title',
            $this->translator->translateReplace($title, [$totalVehicles])
        );

        return $table;
    }
}
