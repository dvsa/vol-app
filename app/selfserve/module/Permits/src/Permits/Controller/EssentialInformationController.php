<?php
namespace Permits\Controller;

use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\View\Helper\BackToOverview;
use Permits\View\Helper\IrhpApplicationSection;

class EssentialInformationController extends AbstractSelfserveController
{
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
            'backUriLabel' => BackToOverview::STANDARD_BACK_LINK_LABEL,
        ],
    ];
}
