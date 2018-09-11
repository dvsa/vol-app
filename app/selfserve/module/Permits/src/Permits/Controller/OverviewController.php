<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\Table\TableConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;

class OverviewController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_ECMT_ENABLED,
    ];

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::PERMIT_APP_WITH_FEE_LIST,
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::PERMIT_APP_NOT_SUBMITTED,
    ];

    protected $tableConfig = [
        'default' => [],
        'validpermitsoverview' => TableConfig::VALID_APP_OVERVIEW
    ];

    protected $templateConfig = [
        'generic' => 'permits/application-overview',
        'validpermitsoverview' => 'permits/valid-permits-overview'
    ];

    protected function validPermitsOverviewAction()
    {
        return parent::genericAction();
    }
}
