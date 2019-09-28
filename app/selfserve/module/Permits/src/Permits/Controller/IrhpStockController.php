<?php

namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\Controller\Config\Form\FormConfig;
use Permits\View\Helper\IrhpApplicationSection;

class IrhpStockController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_PERMITS_ENABLED,
    ];

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
     * @param array $config
     * @param array $params
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handlePostCommand(array &$config, array $params)
    {
        $this->redirectParams = [
            'stock' => $params['stock'],
        ];
    }
}
