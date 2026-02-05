<?php

namespace Permits\Controller;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\DataSource\AvailableStocks;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Data\Mapper\MapperManager;
use Permits\View\Helper\IrhpApplicationSection;

class IrhpStockController extends AbstractSelfserveController
{
    protected $dataSourceConfig = [
        'default' => DataSourceConfig::PERMIT_APP_STOCK,
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::PERMIT_APP_CAN_SELECT_STOCK,
    ];

    protected $formConfig = [
        'default' => FormConfig::FORM_STOCK,
    ];

    protected $templateConfig = [
        'default' => 'permits/single-question'
    ];

    protected $templateVarsConfig = [
        'question' => [
            'browserTitle' => 'permits.page.stock.title',
            'question' => 'permits.page.stock.question',
            'hint' => 'permits.page.stock.hint',
            'backUri' => IrhpApplicationSection::ROUTE_YEAR,
            'cancelUri' => IrhpApplicationSection::ROUTE_PERMITS,
        ]
    ];

    protected $postConfig = [
        'default' => [
            'retrieveData' => true,
            'checkConditionalDisplay' => true,
            'step' => IrhpApplicationSection::ROUTE_ADD_LICENCE,
        ],
    ];

    /**
     * @param TranslationHelperService $translationHelper
     * @param FormHelperService $formHelper
     * @param TableFactory $tableBuilder
     * @param MapperManager $mapperManager
     */
    public function __construct(
        TranslationHelperService $translationHelper,
        FormHelperService $formHelper,
        TableFactory $tableBuilder,
        MapperManager $mapperManager
    ) {
        parent::__construct($translationHelper, $formHelper, $tableBuilder, $mapperManager);
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    #[\Override]
    public function retrieveData()
    {
        parent::retrieveData();

        $selectedStockId = '';
        if (isset($this->queryParams['selected'])) {
            $selectedStockId = $this->queryParams['selected'];
        }

        $this->data[AvailableStocks::DATA_KEY]['selectedStock'] = $selectedStockId;
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    #[\Override]
    public function mergeTemplateVars()
    {
        $this->templateVarsConfig['question']['backUriOptions'] = [
            'query' => [
                'selected' => $this->routeParams['year']
            ]
        ];

        parent::mergeTemplateVars();
    }

    /**
     * @param array $config
     * @param array $params
     *
     * @SuppressWarnings (PHPMD.UnusedFormalParameter)
     *
     * @return void
     */
    #[\Override]
    public function handlePostCommand(array &$config, array $params)
    {
        $this->redirectParams = [
            'stock' => $params['stock'],
        ];
    }
}
