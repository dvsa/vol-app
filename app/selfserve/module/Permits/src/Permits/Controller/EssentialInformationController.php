<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\View\Helper\BackToOverview;
use Permits\View\Helper\IrhpApplicationSection;

class EssentialInformationController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_PERMITS_ENABLED,
    ];

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::IRHP_APP_ESSENTIAL_INFORMATION,
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::IRHP_APP_CAN_VIEW_ESSENTIAL_INFORMATION,
    ];

    protected $templateConfig = [
        'default' => 'permits/essential-information',
    ];

    protected $templateVarsConfig = [
        'default' => [
            'browserTitle' => 'permits.page.bilateral.countries.essential.heading',
            'continueUri' => IrhpApplicationSection::ROUTE_PERIOD,
            'continueUriLabel' => 'permits.button.continue',
            'backUri' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
            'backUriLabel' => BackToOverview::BACK_LINK_LABEL,
        ],
    ];
}
