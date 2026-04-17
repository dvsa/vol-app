<?php

namespace Permits\Controller;

use Common\RefData;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Query\Permits\AvailableStocks;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\DataSource\AvailableYears;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Data\Mapper\MapperManager;
use Permits\View\Helper\IrhpApplicationSection;
use RuntimeException;

class YearController extends AbstractSelfserveController
{
    protected $dataSourceConfig = [
        'default' => DataSourceConfig::PERMIT_APP_YEAR,
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::PERMIT_APP_CAN_SELECT_YEAR,
    ];

    protected $formConfig = [
        'default' => FormConfig::FORM_YEAR,
    ];

    protected $templateConfig = [
        'default' => 'permits/single-question'
    ];

    protected $templateVarsConfig = [
        'question' => [
            'backUri' => IrhpApplicationSection::ROUTE_TYPE,
            'cancelUri' => IrhpApplicationSection::ROUTE_PERMITS,
        ]
    ];

    protected $postConfig = [
        'default' => [
            'retrieveData' => true,
            'checkConditionalDisplay' => true,
            'params' => [
                'route' => [
                    'type',
                ]
            ],
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

        $selectedYear = '';
        if (isset($this->queryParams['selected'])) {
            $selectedYear = $this->queryParams['selected'];
        }

        $this->data[AvailableYears::DATA_KEY]['selectedYear'] = $selectedYear;
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
                'selected' => $this->routeParams['type']
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
        if ($params['type'] == RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID && $params['year'] >= 2020) {
            // redirect to the stock selection page
            $config['step'] = IrhpApplicationSection::ROUTE_STOCK;

            $this->redirectParams = [
                'year' => $params['year'],
                'irhpPermitType' => $params['type']
            ];
        } else {
            // find a stock id
            $response = $this->handleQuery(
                AvailableStocks::create(
                    [
                        'irhpPermitType' => $params['type'],
                        'year' => $params['year'],
                    ]
                )
            );
            $data = $this->handleResponse($response);

            if (empty($data['stocks'])) {
                throw new RuntimeException('Stock not found.');
            }

            $stock = array_shift($data['stocks']);

            $this->redirectParams = [
                'type' => $params['type'],
                'stock' => $stock['id'],
            ];
        }
    }
}
