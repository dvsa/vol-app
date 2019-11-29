<?php

namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\DataSource\IrhpApplication as IrhpAppDataSource;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\View\Helper\IrhpApplicationSection;

class IrhpUnderConsiderationController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_PERMITS_ENABLED,
    ];

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::IRHP_APP_UNDER_CONSIDERATION,
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::IRHP_APP_UNDER_CONSIDERATION,
    ];

    protected $templateConfig = [
        'generic' => 'permits/irhp-under-consideration'
    ];

    protected $templateVarsConfig = [
        'generic' => [
            'backUri' => IrhpApplicationSection::ROUTE_PERMITS,
            'browserTitle' => 'permits.irhp.under-consideration.browser.title',
            'prependTitleDataKey' => IrhpAppDataSource::DATA_KEY,
        ]
    ];
}
