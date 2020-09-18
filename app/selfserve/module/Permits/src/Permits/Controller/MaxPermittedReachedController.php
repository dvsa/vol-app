<?php

namespace Permits\Controller;

use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;

class MaxPermittedReachedController extends AbstractSelfserveController
{
    protected $dataSourceConfig = [
        'generic' => DataSourceConfig::PERMIT_APP_MAX_PERMITTED_REACHED,
    ];

    protected $conditionalDisplayConfig = [
        'generic' => ConditionalDisplayConfig::PERMIT_APP_CAN_SHOW_MAX_PERMITTED_REACHED,
    ];

    protected $templateConfig = [
        'generic' => 'permits/max-permitted-reached'
    ];

    protected $templateVarsConfig = [
        'generic' => [
            'browserTitle' => 'permits.page.max-permitted-reached.browser.title',
        ]
    ];
}
