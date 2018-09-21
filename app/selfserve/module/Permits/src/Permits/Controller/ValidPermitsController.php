<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\Table\TableConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;

class ValidPermitsController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_ECMT_ENABLED,
    ];

    protected $dataSourceConfig = [
        'generic' => DataSourceConfig::PERMIT_ECMT_VALID,
    ];

    protected $tableConfig = [
        'generic' => TableConfig::VALID_APP_OVERVIEW
    ];

    protected $templateConfig = [
        'generic' => 'permits/valid-permits-overview',
    ];
}
