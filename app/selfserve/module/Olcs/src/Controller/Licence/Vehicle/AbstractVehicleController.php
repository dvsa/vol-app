<?php

namespace Olcs\Controller\Licence\Vehicle;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;
use Common\RefData;
use Common\Service\Cqrs\Exception\NotFoundException;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableBuilder;
use Common\Util;
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
    use Util\FlashMessengerTrait;

    public const DEFAULT_TABLE_SORT_ORDER = 'DESC';
    public const DEFAULT_TABLE_SORT_COLUMN = 'createdOn';
    public const DEFAULT_TABLE_ROW_LIMIT = 10;
    public const TABLE_TITLE_SINGULAR = 'licence.vehicle.table.title.singular';
    public const TABLE_TITLE_PLURAL = 'licence.vehicle.table.title.plural';

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
     * @return array
     */
    abstract protected function getViewVariables(): array;


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
    protected function fetchVehicleData(string $vrm): array
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
     * Format filters (query/route parameters)
     *
     * @param array $query parameters
     *
     * @return array
     */
    protected function formatTableFilters($query)
    {
        $filters = [
            'page' => (isset($query['page']) ? $query['page'] : 1),
            'limit' => (isset($query['limit']) ? $query['limit'] : static::DEFAULT_TABLE_ROW_LIMIT),
            'sort' => isset($query['sort']) ? $query['sort'] : static::DEFAULT_TABLE_SORT_COLUMN,
            'order' => isset($query['order']) ? $query['order'] : static::DEFAULT_TABLE_SORT_ORDER,
        ];

        return $filters;
    }

    /**
     * Define filters (query/route parameters)
     *
     * @return array
     */
    protected function getTableFilters()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $query = $request->getPost('query');
        } else {
            $query = $request->getQuery();
        }

        return $this->formatTableFilters((array)$query);
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

        /** @var TableBuilder $table */
        $table = $this->getServiceLocator()->get('Table');
        $table = $table->prepareTable('licence-vehicles', $vehicleData, $dtoData);

        $totalVehicles = $vehicleData['count'];
        $titleKey = $totalVehicles > 1 ? static::TABLE_TITLE_PLURAL : static::TABLE_TITLE_SINGULAR;
        $title = $this->translator->translateReplace($titleKey, [$totalVehicles]);
        $table->setVariable('title', $title);

        return $table;
    }
}
