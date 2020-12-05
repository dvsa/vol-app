<?php

namespace Permits\Controller;

use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;

class MaxPermittedReachedForStockController extends AbstractSelfserveController
{
    protected $dataSourceConfig = [
        'generic' => DataSourceConfig::PERMIT_APP_MAX_PERMITTED_REACHED_FOR_STOCK,
    ];

    protected $conditionalDisplayConfig = [
        'generic' => ConditionalDisplayConfig::PERMIT_APP_CAN_SHOW_MAX_PERMITTED_REACHED_FOR_STOCK,
    ];

    protected $templateConfig = [
        'generic' => 'permits/max-permitted-reached-for-stock'
    ];

    protected $templateVarsConfig = [
        'generic' => [
            'browserTitle' => 'permits.page.max-permitted-reached-for-stock.browser.title',
        ]
    ];
}
