<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;

class IrhpApplicationController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_PERMITS_ENABLED,
    ];

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::IRHP_APP_OVERVIEW,
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::IRHP_APP_NOT_SUBMITTED,
    ];

    protected $templateConfig = [
        'default' => 'permits/irhp-application-overview'
    ];
}
