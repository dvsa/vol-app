<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Olcs\Controller\AbstractSelfserveController;
use Olcs\Controller\Config\DataSource\DataSourceConfig;
use Olcs\Controller\Config\DataSource\DataSourceInterface;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;

class StartController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_SURRENDER_ENABLED
    ];

    protected $templateConfig = [
        'index' => 'licence/surrender-index'
    ];

    protected $dataSourceConfig = [
        'index' => DataSourceConfig::LICENCE
    ];
    /**
     * @var DataSourceInterface
     */
    private $dataSource;

    public function __construct(DataSourceInterface $dataSource)
    {
        $this->dataSource = $dataSource;
    }

    public function retrieveData()
    {
        $dataSourceConfig = $this->configsForAction('dataSourceConfig');

        //retrieve DTO data
        foreach ($dataSourceConfig as $dataSource => $config) {
            /**
             * @var DataSourceInterface $source
             * @var QueryInterface $query
             */
            $source = $this->dataSource;
            $query = $source->queryFromParams(array_merge($this->routeParams, $this->queryParams));

            $response = $this->handleQuery($query);
            $data = $this->handleResponse($response);

            if (isset($config['mapper'])) {
                $mapper = isset($config['mapper']) ? $config['mapper'] : DefaultMapper::class;
                $data = $mapper::mapForDisplay($data);
            }
            $this->data[$source::DATA_KEY] = $data;
            if (isset($config['append'])) {
                foreach ($config['append'] as $appendTo => $mapper) {
                    $combinedData = [
                        $appendTo => $this->data[$appendTo],
                        $source::DATA_KEY => $data
                    ];
                    $this->data[$appendTo] = $mapper::mapForDisplay($combinedData);
                }
            }
        }
    }
    /**
     * IndexAction
     *
     * @return array|\Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $licence = $this->data['licence'];
        $translateService = $this->getServiceLocator()->get('Helper\Translation');

        $view = $this->genericView();

        switch ($licence['goodsOrPsv']['id']) {
            case 'lcat_gv':
                $view->setVariables($this->getGvData());
                break;
            case 'lcat_psv':
                $view->setVariables($this->getPsvData($translateService));
                break;
            default:
                break;
        }

        $view->setVariable('licNo', $licence['licNo']);
        $view->setVariable('body', 'markup-licence-surrender-start');
        $view->setVariable('backUrl', $this->url()->fromRoute('lva-licence', ['licence' => $licence['id']]));

        return $view;
    }

    public function startAction()
    {

    }

    private function getGvData()
    {
        return [
            'pageTitle' => 'licence.surrender.start.title.gv',
            'cancelBus' => ['']
        ];
    }

    private function getPsvData($translateService)
    {
        return [
            'pageTitle' => 'licence.surrender.start.title.psv',
            'cancelBus' => [$translateService->translate('licence.surrender.start.cancel.bus')]
        ];
    }
}
